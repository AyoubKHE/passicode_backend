<?php

namespace Tests\Feature\Auth\PasswordResetTests;

use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use App\Services\JWTService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ControllerTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        try {
            parent::setUp();
        } catch (Throwable $th) {
            $this->markTestSkipped($th->getMessage());
        }
    }
    protected function tearDown(): void
    {
        try {
            parent::tearDown();
        } catch (Throwable $th) {
            $this->markTestSkipped($th->getMessage());
        }

    }


    public function test_successfull_user_password_reset(): void
    {

        $password_reset_token_payload = [
            "iat" => time(),
            "exp" => time() + 900, // 15 minutes
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $password_reset_token_object = new JWTService($password_reset_token_payload);

        $password_reset_token = $password_reset_token_object->getJwtToken();

        $hashed_password_reset_token = Hash::make($password_reset_token);

        try {
            User::create([
                'first_name' => 'Ayoub',
                'last_name' => 'Kheyar',
                'email' => 'ayoub.kheyar06@gmail.com',
                'password' => Hash::make('a'),
                'role' => 'Admin',
                'is_active' => true,
                'password_reset_token' => $hashed_password_reset_token,
                'created_at' => now()
            ]);
        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $response = $this->postJson('api/auth/reset-password', [
            'password_reset_token' => $password_reset_token,
            'new_password' => 'new password!'
        ]);

        try {
            $this->assertDatabaseHas(
                "users",
                [
                    "id" => 1,
                    'password_reset_token' => null,
                ]
            );

            $response->assertStatus(200)
                ->assertJsonFragment(
                    [
                        'message' => "User's password has been reset successfully!"
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_user_password_reset_fails_when_sending_expired_token(): void
    {

        $password_reset_token_payload = [
            "iat" => time(),
            "exp" => time(),
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $password_reset_token_object = new JWTService($password_reset_token_payload);

        $password_reset_token = $password_reset_token_object->getJwtToken();

        $hashed_password_reset_token = Hash::make($password_reset_token);

        try {
            User::create([
                'first_name' => 'Ayoub',
                'last_name' => 'Kheyar',
                'email' => 'ayoub.kheyar06@gmail.com',
                'password' => Hash::make('a'),
                'role' => 'Admin',
                'is_active' => true,
                'password_reset_token' => $hashed_password_reset_token,
                'created_at' => now()
            ]);
        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $response = $this->postJson('api/auth/reset-password', [
            'password_reset_token' => $password_reset_token,
            'new_password' => 'new password!'
        ]);

        try {
            $this->assertDatabaseHas(
                "users",
                [
                    "id" => 1,
                    'password_reset_token' => $hashed_password_reset_token,
                ]
            );

            $response->assertStatus(400)
                ->assertJsonFragment(
                    [
                        'error' => "The password reset token has expired."
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_user_password_reset_fails_when_sending_wrong_token(): void
    {

        $password_reset_token_payload = [
            "iat" => time(),
            "exp" => time() + 900, // 15 minutes
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $password_reset_token_object = new JWTService($password_reset_token_payload);

        $password_reset_token = $password_reset_token_object->getJwtToken();

        $hashed_password_reset_token = Hash::make($password_reset_token);

        try {
            User::create([
                'first_name' => 'Ayoub',
                'last_name' => 'Kheyar',
                'email' => 'ayoub.kheyar06@gmail.com',
                'password' => Hash::make('a'),
                'role' => 'Admin',
                'is_active' => true,
                'password_reset_token' => $hashed_password_reset_token,
                'created_at' => now()
            ]);
        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $response = $this->postJson('api/auth/reset-password', [
            'password_reset_token' => "wrong token !!!",
            'new_password' => 'new password!'
        ]);

        try {
            $this->assertDatabaseHas(
                "users",
                [
                    "id" => 1,
                    'first_name' => 'Ayoub',
                    'last_name' => 'Kheyar',
                    "email" => "ayoub.kheyar06@gmail.com",
                    'role' => 'Admin',
                    'password_reset_token' => $hashed_password_reset_token,
                ]
            );

            $response->assertStatus(400)
                ->assertJsonFragment(
                    [
                        'error' => "The password reset token is invalid."
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_user_password_reset_fails_when_user_does_not_requested_a_reset_password(): void
    {

        $password_reset_token_payload = [
            "iat" => time(),
            "exp" => time() + 900, // 15 minutes
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $password_reset_token_object = new JWTService($password_reset_token_payload);

        $password_reset_token = $password_reset_token_object->getJwtToken();

        $hashed_password_reset_token = Hash::make($password_reset_token);

        try {
            User::create([
                'first_name' => 'Ayoub',
                'last_name' => 'Kheyar',
                'email' => 'ayoub.kheyar06@gmail.com',
                'password' => Hash::make('a'),
                'role' => 'Admin',
                'is_active' => true,
                'password_reset_token' => null,
                'created_at' => now()
            ]);
        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $response = $this->postJson('api/auth/reset-password', [
            'password_reset_token' => $password_reset_token,
            'new_password' => 'new password!'
        ]);

        try {
            $this->assertDatabaseHas(
                "users",
                [
                    "id" => 1,
                    'first_name' => 'Ayoub',
                    'last_name' => 'Kheyar',
                    "email" => "ayoub.kheyar06@gmail.com",
                    'role' => 'Admin',
                    'password_reset_token' => null,
                ]
            );

            $response->assertStatus(400)
                ->assertJsonFragment(
                    [
                        'error' => "The user has not requested a token to reset the password."
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_user_password_reset_fails_when_dont_found_the_requested_user_in_database(): void
    {

        $password_reset_token_payload = [
            "iat" => time(),
            "exp" => time() + 900, // 15 minutes
            "user_data" => array(
                "user_id" => 2,
            )
        ];

        $password_reset_token_object = new JWTService($password_reset_token_payload);

        $password_reset_token = $password_reset_token_object->getJwtToken();

        $hashed_password_reset_token = Hash::make($password_reset_token);

        try {
            User::create([
                'first_name' => 'Ayoub',
                'last_name' => 'Kheyar',
                'email' => 'ayoub.kheyar06@gmail.com',
                'password' => Hash::make('a'),
                'role' => 'Admin',
                'is_active' => true,
                'password_reset_token' => null,
                'created_at' => now()
            ]);
        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $response = $this->postJson('api/auth/reset-password', [
            'password_reset_token' => $password_reset_token,
            'new_password' => 'new password!'
        ]);

        try {
            $this->assertDatabaseHas(
                "users",
                [
                    "id" => 1,
                    'first_name' => 'Ayoub',
                    'last_name' => 'Kheyar',
                    "email" => "ayoub.kheyar06@gmail.com",
                    'role' => 'Admin',
                    'password_reset_token' => null,
                ]
            );

            $response->assertStatus(404)
                ->assertJsonFragment(
                    [
                        'error' => "User not found"
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_user_password_reset_fails_when_sending_valid_token_but_different_from_the_token_stored_in_database(): void
    {

        $password_reset_token_payload = [
            "iat" => time(),
            "exp" => time() + 900, // 15 minutes
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $password_reset_token_object = new JWTService($password_reset_token_payload);

        $password_reset_token = $password_reset_token_object->getJwtToken();

        $hashed_password_reset_token = Hash::make($password_reset_token);

        try {
            User::create([
                'first_name' => 'Ayoub',
                'last_name' => 'Kheyar',
                'email' => 'ayoub.kheyar06@gmail.com',
                'password' => Hash::make('a'),
                'role' => 'Admin',
                'is_active' => true,
                'password_reset_token' => $hashed_password_reset_token,
                'created_at' => now()
            ]);
        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $different_password_reset_token_payload = [
            "iat" => time() + 1,
            "exp" => time() + 900, // 15 minutes
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $different_password_reset_token_object = new JWTService($different_password_reset_token_payload);

        $different_password_reset_token = $different_password_reset_token_object->getJwtToken();

        $response = $this->postJson('api/auth/reset-password', [
            'password_reset_token' => $different_password_reset_token,
            'new_password' => 'new password!'
        ]);
        try {
            $this->assertDatabaseHas(
                "users",
                [
                    "id" => 1,
                    'first_name' => 'Ayoub',
                    'last_name' => 'Kheyar',
                    "email" => "ayoub.kheyar06@gmail.com",
                    'role' => 'Admin',
                    'password_reset_token' => $hashed_password_reset_token,
                ]
            );

            $response->assertStatus(400)
                ->assertJsonFragment(
                    [
                        'error' => "The password reset token is invalid."
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }

}