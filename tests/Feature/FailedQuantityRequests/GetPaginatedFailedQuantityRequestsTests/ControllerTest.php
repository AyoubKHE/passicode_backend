<?php

namespace Tests\Feature\FailedQuantityRequests\GetPaginatedFailedQuantityRequestsTests;

use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Services\JWTService;
use App\Models\Clients\Client;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Products\FailedQuantityRequest;
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

    private function createFakeFailedQuantityRequests()
    {

        DB::transaction(function () {

            User::create([
                'first_name' => 'Salim',
                'last_name' => 'Madi',
                'email' => 'salim.madi@gmail.com',
                'role' => 'Client',
                'is_active' => true,
                'created_at' => now()
            ]);

            Client::create(
                [
                    'user_id' => 2,
                ],
            );

            Category::create(
                [
                    'name' => 'Netflix 10$',
                    'description' => 'Netflix 10$ Description',
                    'price' => 2700,
                    'discount' => 15,
                    'quantity' => 1,
                    'is_active' => true,
                    'is_leaf_category' => true,
                    'parent_id' => null,
                    'image_path' => 'categories/id_1/image_1_name.png',
                    'created_at' => now(),
                ],
            );


            FailedQuantityRequest::insert(array(
                [
                    'category_id' => 1,
                    'user_id' => 2,
                    'available_quantity' => 2,
                    'requested_quantity' => 5,
                    'status' => "not_settled",
                    'is_category_active' => true,
                    'created_at' => now(),
                    'settled_at' => null,
                ],
                [
                    'category_id' => 1,
                    'user_id' => 2,
                    'available_quantity' => 2,
                    'requested_quantity' => 5,
                    'status' => "not_settled",
                    'is_category_active' => true,
                    'created_at' => now(),
                    'settled_at' => null,
                ],
                [
                    'category_id' => 1,
                    'user_id' => 2,
                    'available_quantity' => 2,
                    'requested_quantity' => 5,
                    'status' => "settled",
                    'is_category_active' => true,
                    'created_at' => now(),
                    'settled_at' => "2025-07-05 12:00:00",
                ],
                [
                    'category_id' => 1,
                    'user_id' => 2,
                    'available_quantity' => 2,
                    'requested_quantity' => 5,
                    'status' => "settled",
                    'is_category_active' => true,
                    'created_at' => now(),
                    'settled_at' => "2025-07-05 12:00:00",
                ],
            ));
        });

    }

    protected function setUp(): void
    {
        try {
            parent::setUp();
        } catch (Throwable $th) {
            $this->markTestSkipped($th->getMessage());
        }

        Storage::deleteDirectory("categories");
    }
    protected function tearDown(): void
    {
        try {
            parent::tearDown();
        } catch (Throwable $th) {
            $this->markTestSkipped($th->getMessage());
        }

    }


    public function test_successfull_get_paginated_failed_quantity_requests(): void
    {
        $this->adminLogin();

        $this->createFakeFailedQuantityRequests();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->getJson('api/failed-quantity-requests/get-paginated-failed-quantity-requests');

        try {

            $response->assertStatus(200)
                ->assertJsonFragment([
                    'total' => 4
                ]);

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}