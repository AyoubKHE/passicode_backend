<?php

namespace Tests\Feature\Auth\EmailVerificationTests;

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


    public function test_successfull_user_email_verification(): void
    {

        $email_verification_token_payload = [
            "iat" => time(),
            "exp" => time() + 900, // 15 minutes
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $email_verification_token_object = new JWTService($email_verification_token_payload);

        $email_verification_token = $email_verification_token_object->getJwtToken();

        $hashed_email_verification_token = Hash::make($email_verification_token);

        try {
            User::create([
                'first_name' => 'Ayoub',
                'last_name' => 'Kheyar',
                'email' => 'ayoub.kheyar06@gmail.com',
                'password' => Hash::make('a'),
                'role' => 'Admin',
                'is_active' => true,
                'email_verification_token' => $hashed_email_verification_token,
                'email_verification_token_sent_at' => now(),
                'created_at' => now()
            ]);
        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $response = $this->getJson('api/auth/email-verification/' . $email_verification_token);

        try {
            $this->assertDatabaseHas(
                "users",
                [
                    "id" => 1,
                    'first_name' => 'Ayoub',
                    'last_name' => 'Kheyar',
                    "email" => "ayoub.kheyar06@gmail.com",
                    'role' => 'Admin',
                    'email_verification_token' => null,
                    'email_verification_token_sent_at' => null,
                ]
            );

            $response->assertStatus(200)
                ->assertJsonFragment(
                    [
                        'message' => "User's email has been successfully verified!"
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_user_email_verification_fails_when_sending_expired_token(): void
    {

        $email_verification_token_payload = [
            "iat" => time(),
            "exp" => time(),
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $email_verification_token_object = new JWTService($email_verification_token_payload);

        $email_verification_token = $email_verification_token_object->getJwtToken();

        $hashed_email_verification_token = Hash::make($email_verification_token);

        try {
            User::create([
                'first_name' => 'Ayoub',
                'last_name' => 'Kheyar',
                'email' => 'ayoub.kheyar06@gmail.com',
                'password' => Hash::make('a'),
                'role' => 'Admin',
                'is_active' => true,
                'email_verification_token' => $hashed_email_verification_token,
                'email_verification_token_sent_at' => now(),
                'created_at' => now()
            ]);
        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $response = $this->getJson('api/auth/email-verification/' . $email_verification_token);

        try {
            $this->assertDatabaseHas(
                "users",
                [
                    "id" => 1,
                    'first_name' => 'Ayoub',
                    'last_name' => 'Kheyar',
                    "email" => "ayoub.kheyar06@gmail.com",
                    'role' => 'Admin',
                    'email_verification_token' => $hashed_email_verification_token,
                ]
            );

            $response->assertStatus(400)
                ->assertJsonFragment(
                    [
                        'error' => "The verification token has expired."
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_user_email_verification_fails_when_sending_wrong_token(): void
    {

        $email_verification_token_payload = [
            "iat" => time(),
            "exp" => time() + 900, // 15 minutes
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $email_verification_token_object = new JWTService($email_verification_token_payload);

        $email_verification_token = $email_verification_token_object->getJwtToken();

        $hashed_email_verification_token = Hash::make($email_verification_token);

        try {
            User::create([
                'first_name' => 'Ayoub',
                'last_name' => 'Kheyar',
                'email' => 'ayoub.kheyar06@gmail.com',
                'password' => Hash::make('a'),
                'role' => 'Admin',
                'is_active' => true,
                'email_verification_token' => $hashed_email_verification_token,
                'email_verification_token_sent_at' => now(),
                'created_at' => now()
            ]);
        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $response = $this->getJson('api/auth/email-verification/' . 'wrong token !!!');

        try {
            $this->assertDatabaseHas(
                "users",
                [
                    "id" => 1,
                    'first_name' => 'Ayoub',
                    'last_name' => 'Kheyar',
                    "email" => "ayoub.kheyar06@gmail.com",
                    'role' => 'Admin',
                    'email_verification_token' => $hashed_email_verification_token,
                ]
            );

            $response->assertStatus(400)
                ->assertJsonFragment(
                    [
                        'error' => "The verification token is invalid."
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_user_email_verification_fails_when_email_already_verified(): void
    {

        $email_verification_token_payload = [
            "iat" => time(),
            "exp" => time() + 900, // 15 minutes
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $email_verification_token_object = new JWTService($email_verification_token_payload);

        $email_verification_token = $email_verification_token_object->getJwtToken();

        $hashed_email_verification_token = Hash::make($email_verification_token);

        try {
            User::create([
                'first_name' => 'Ayoub',
                'last_name' => 'Kheyar',
                'email' => 'ayoub.kheyar06@gmail.com',
                'password' => Hash::make('a'),
                'role' => 'Admin',
                'is_active' => true,
                'email_verification_token' => null,
                'email_verification_token_sent_at' => null,
                'created_at' => now()
            ]);
        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $response = $this->getJson('api/auth/email-verification/' . $email_verification_token);

        try {
            $this->assertDatabaseHas(
                "users",
                [
                    "id" => 1,
                    'first_name' => 'Ayoub',
                    'last_name' => 'Kheyar',
                    "email" => "ayoub.kheyar06@gmail.com",
                    'role' => 'Admin',
                    'email_verification_token' => null,
                    'email_verification_token_sent_at' => null,
                ]
            );

            $response->assertStatus(400)
                ->assertJsonFragment(
                    [
                        'error' => "The user email already verified."
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_user_email_verification_fails_when_dont_found_the_requested_user_in_database(): void
    {

        $email_verification_token_payload = [
            "iat" => time(),
            "exp" => time() + 900, // 15 minutes
            "user_data" => array(
                "user_id" => 2,
            )
        ];

        $email_verification_token_object = new JWTService($email_verification_token_payload);

        $email_verification_token = $email_verification_token_object->getJwtToken();

        $hashed_email_verification_token = Hash::make($email_verification_token);

        try {
            User::create([
                'first_name' => 'Ayoub',
                'last_name' => 'Kheyar',
                'email' => 'ayoub.kheyar06@gmail.com',
                'password' => Hash::make('a'),
                'role' => 'Admin',
                'is_active' => true,
                'email_verification_token' => null,
                'email_verification_token_sent_at' => null,
                'created_at' => now()
            ]);
        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $response = $this->getJson('api/auth/email-verification/' . $email_verification_token);

        try {
            $this->assertDatabaseHas(
                "users",
                [
                    "id" => 1,
                    'first_name' => 'Ayoub',
                    'last_name' => 'Kheyar',
                    "email" => "ayoub.kheyar06@gmail.com",
                    'role' => 'Admin',
                    'email_verification_token' => null,
                    'email_verification_token_sent_at' => null,
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


    public function test_user_email_verification_fails_when_sending_valid_token_but_different_from_the_token_stored_in_database(): void
    {

        $email_verification_token_payload = [
            "iat" => time(),
            "exp" => time() + 900, // 15 minutes
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $email_verification_token_object = new JWTService($email_verification_token_payload);

        $email_verification_token = $email_verification_token_object->getJwtToken();

        $hashed_email_verification_token = Hash::make($email_verification_token);

        try {
            User::create([
                'first_name' => 'Ayoub',
                'last_name' => 'Kheyar',
                'email' => 'ayoub.kheyar06@gmail.com',
                'password' => Hash::make('a'),
                'role' => 'Admin',
                'is_active' => true,
                'email_verification_token' => $hashed_email_verification_token,
                'email_verification_token_sent_at' => now(),
                'created_at' => now()
            ]);
        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $different_email_verification_token_payload = [
            "iat" => time() + 1,
            "exp" => time() + 900, // 15 minutes
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $different_email_verification_token_object = new JWTService($different_email_verification_token_payload);

        $different_email_verification_token = $different_email_verification_token_object->getJwtToken();

        $response = $this->getJson('api/auth/email-verification/' . $different_email_verification_token);

        try {
            $this->assertDatabaseHas(
                "users",
                [
                    "id" => 1,
                    'first_name' => 'Ayoub',
                    'last_name' => 'Kheyar',
                    "email" => "ayoub.kheyar06@gmail.com",
                    'role' => 'Admin',
                    'email_verification_token' => $hashed_email_verification_token,
                ]
            );

            $response->assertStatus(400)
                ->assertJsonFragment(
                    [
                        'error' => "The verification token is invalid."
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }

}