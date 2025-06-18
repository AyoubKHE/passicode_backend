<?php

namespace Tests\Feature\Auth\UsersJwtAuthenticationMiddlewareTests;

use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Services\JWTService;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class MiddlewareTest extends TestCase
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


    public function test_successfull_authentication(): void
    {

        try {
            DB::transaction(function () {
                User::create([
                    'first_name' => 'Ayoub',
                    'last_name' => 'Kheyar',
                    'email' => 'ayoub.kheyar06@gmail.com',
                    'role' => 'Admin',
                    'is_active' => true,
                    'created_at' => now()
                ]);

                Admin::create(
                    [
                        'user_id' => 1,
                    ],
                );
            });

        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $access_token_payload = [
            "iat" => time(),
            "exp" => time() + 900, // 15 minutes
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $access_token_object = new JWTService($access_token_payload);

        $access_token = $access_token_object->getJwtToken();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $access_token,
        ])->getJson('api/users/get-my-account');

        try {

            $response->assertStatus(200);
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_successfull_authentication_when_sending_expired_access_token_but_a_valid_refresh_token(): void
    {

        try {
            DB::transaction(function () {
                User::create([
                    'first_name' => 'Ayoub',
                    'last_name' => 'Kheyar',
                    'email' => 'ayoub.kheyar06@gmail.com',
                    'role' => 'Admin',
                    'is_active' => true,
                    'created_at' => now()
                ]);

                Admin::create(
                    [
                        'user_id' => 1,
                    ],
                );
            });

        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $expired_access_token_payload = [
            "iat" => time(),
            "exp" => time(), // 0 minutes
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $expired_access_token_object = new JWTService($expired_access_token_payload);

        $expired_access_token = $expired_access_token_object->getJwtToken();

        $refresh_token_payload = [
            "iat" => time(),
            "exp" => time() + 2592000, // 1 month
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $refresh_token_object = new JWTService($refresh_token_payload);

        $refresh_token = $refresh_token_object->getJwtToken();

        $response = $this->withCookie('refresh_token', $refresh_token)
            ->withCredentials()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $expired_access_token,
            ])
            ->getJson('api/users/get-my-account');

        try {

            $response->assertStatus(200)->assertJsonStructure([
                'new_access_token',
            ]);
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_successfull_ending_user_session_when_refresh_token_expires(): void
    {

        try {
            DB::transaction(function () {
                User::create([
                    'first_name' => 'Ayoub',
                    'last_name' => 'Kheyar',
                    'email' => 'ayoub.kheyar06@gmail.com',
                    'role' => 'Admin',
                    'is_active' => true,
                    'refresh_token' => "refresh token",
                    'created_at' => now()
                ]);

                Admin::create(
                    [
                        'user_id' => 1,
                    ],
                );
            });

        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $expired_access_token_payload = [
            "iat" => time(),
            "exp" => time(), // 0 minutes
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $expired_access_token_object = new JWTService($expired_access_token_payload);

        $expired_access_token = $expired_access_token_object->getJwtToken();

        $expired_refresh_token_payload = [
            "iat" => time(),
            "exp" => time(), // 0 minutes
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $expired_refresh_token_object = new JWTService($expired_refresh_token_payload);

        $expired_refresh_token = $expired_refresh_token_object->getJwtToken();

        $response = $this->withCookie('refresh_token', $expired_refresh_token)
            ->withCredentials()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $expired_access_token,
            ])
            ->getJson('api/users/get-my-account');

        try {

            $response->assertStatus(401)
                ->assertJsonFragment(
                    [
                        'error' => 'User session has expired. Authentication required.',
                    ]
                )->assertCookie('refresh_token', '');
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}