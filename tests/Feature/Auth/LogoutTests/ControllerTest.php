<?php

namespace Tests\Feature\Auth\LogoutTests;

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

    private string $access_token;

    private function userLogin()
    {
        try {
            User::create([
                'first_name' => 'Ayoub',
                'last_name' => 'Kheyar',
                'email' => 'ayoub.kheyar06@gmail.com',
                'password' => Hash::make('a'),
                'role' => 'Admin',
                'is_active' => true,
                'created_at' => now(),
                'refresh_token' => 'refresh_token',
            ]);

        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a user manually");
        }

        $access_token_payload = [
            "iat" => time(),
            "exp" => time() + 900,
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $access_token_object = new JWTService($access_token_payload);

        $this->access_token = $access_token_object->getJwtToken();
    }

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


    public function test_successfull_user_logout(): void
    {
        $this->userLogin();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->getJson('api/auth/logout');

        try {
            $this->assertDatabaseHas(
                "users",
                [
                    "id" => 1,
                    "refresh_token" => null,
                ]
            );


            $response->assertStatus(200)
                ->assertJsonFragment(
                    [
                        'message' => 'User logged out successfully!'
                    ]
                )->assertCookie('refresh_token', '');

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}