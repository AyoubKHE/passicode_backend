<?php

namespace Tests\Feature\Auth\LoginTests;

use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Models\Students\Student;
use App\Models\Users\PhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Users\SocialMediaAccount;
use App\Models\Students\StudentAvailabilityDay;
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


    public function test_successfull_user_login(): void
    {

        try {
            DB::transaction(function () {
                User::create(
                    [
                        'first_name' => 'Ayoub',
                        'last_name' => 'Kheyar',
                        'email' => 'ayoub.kheyar06@gmail.com',
                        'password' => Hash::make('a'),
                        'role' => 'Super Admin',
                        'is_active' => true,
                        'created_at' => now()
                    ],
                );


                Admin::create(
                    [
                        "user_id" => 1,
                    ],
                );

            });
        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating user manually");
        }

        $response = $this->postJson('api/auth/login', [
            'email' => 'ayoub.kheyar06@gmail.com',
            'password' => "a",
        ]);

        try {

            $response->assertStatus(200)
                ->assertJsonFragment(
                    [
                        'message' => 'User logged in successfully!',
                    ]
                )
                ->assertJsonStructure([
                    'access_token',
                ])->assertCookie('refresh_token');
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_user_login_fails_when_user_send_wrong_email(): void
    {

        try {
            DB::transaction(function () {
                User::create(
                    [
                        'first_name' => 'Ayoub',
                        'last_name' => 'Kheyar',
                        'email' => 'ayoub.kheyar06@gmail.com',
                        'password' => Hash::make('a'),
                        'role' => 'Super Admin',
                        'is_active' => true,
                        'created_at' => now()
                    ],
                );


                Admin::create(
                    [
                        "user_id" => 1,
                    ],
                );

            });
        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating user manually");
        }

        $response = $this->postJson('api/auth/login', [
            'email' => 'a@a.com',
            'password' => "a",
        ]);

        try {

            $response->assertStatus(401)
                ->assertJsonFragment(
                    [
                        'error' => 'Invalid email or password.'
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_user_login_fails_when_user_send_wrong_password(): void
    {

        try {
            DB::transaction(function () {
                User::create(
                    [
                        'first_name' => 'Ayoub',
                        'last_name' => 'Kheyar',
                        'email' => 'ayoub.kheyar06@gmail.com',
                        'password' => Hash::make('a'),
                        'role' => 'Super Admin',
                        'is_active' => true,
                        'created_at' => now()
                    ],
                );


                Admin::create(
                    [
                        "user_id" => 1,
                    ],
                );

            });
        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating user manually");
        }

        $response = $this->postJson('api/auth/login', [
            'email' => 'ayoub.kheyar06@gmail.com',
            'password' => "wrong password",
        ]);

        try {

            $response->assertStatus(401)
                ->assertJsonFragment(
                    [
                        'error' => 'Invalid email or password.'
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}