<?php

namespace Tests\Feature\Clients\GetPaginatedClientsTests;

use App\Models\Clients\Client;
use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Services\JWTService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Products\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ControllerTest extends TestCase
{
    use DatabaseMigrations;

    private string $access_token;

    private function adminLogin()
    {
        try {
            DB::transaction(function () {
                User::create([
                    'first_name' => 'Ayoub',
                    'last_name' => 'Kheyar',
                    'email' => 'ayoub.kheyar06@gmail.com',
                    'role' => 'Super Admin',
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
            "exp" => time() + 900,
            "user_data" => array(
                "user_id" => 1,
            )
        ];

        $access_token_object = new JWTService($access_token_payload);

        $this->access_token = $access_token_object->getJwtToken();
    }

    private function createFakeClients()
    {

        try {

            DB::transaction(function () {
                User::insert(array(
                    [
                        'first_name' => 'Salim',
                        'last_name' => 'Madi',
                        'email' => 'salim.madi@gmail.com',
                        'role' => 'Client',
                        'is_active' => true,
                        'created_at' => now()

                    ],
                    [
                        'first_name' => 'Karim',
                        'last_name' => 'Asli',
                        'email' => 'karim.asli@gmail.com',
                        'role' => 'Client',
                        'is_active' => true,
                        'created_at' => now()

                    ],
                    [
                        'first_name' => 'Mounir',
                        'last_name' => 'Aksel',
                        'email' => 'mounir.aksel@gmail.com',
                        'role' => 'Client',
                        'is_active' => true,
                        'created_at' => now()

                    ],
                    [
                        'first_name' => 'Hamid',
                        'last_name' => 'Madani',
                        'email' => 'hamid.madani@gmail.com',
                        'role' => 'Client',
                        'is_active' => true,
                        'created_at' => now()

                    ],
                    [
                        'first_name' => 'Sofiane',
                        'last_name' => 'Aouni',
                        'email' => 'soufiane.aouni@gmail.com',
                        'role' => 'Client',
                        'is_active' => true,
                        'created_at' => now()

                    ],
                ));


                Client::insert(array(
                    [
                        'user_id' => 2,
                    ],
                    [
                        'user_id' => 3,
                    ],
                    [
                        'user_id' => 4,
                    ],
                    [
                        'user_id' => 5,
                    ],
                    [
                        'user_id' => 6,
                    ],
                ));
            });

        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating fake clients manually");
        }

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


    public function test_successfull_get_paginated_clients(): void
    {
        $this->adminLogin();

        $this->createFakeClients();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->getJson('api/clients/get-paginated-clients');

        try {

            $response->assertStatus(200)
                ->assertJsonFragment([
                    'total' => 5
                ]);

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}