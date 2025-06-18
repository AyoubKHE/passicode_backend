<?php

namespace Tests\Feature\Products\GetPaginatedProductsByFilterTests;

use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Services\JWTService;
use App\Models\Products\Product;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class IdFilterTest extends TestCase
{
    use DatabaseMigrations;

    private string $access_token;

    private function superAdminLogin()
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
            $this->markTestSkipped("test skipped because a problem occured while creating a super admin manually");
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

    private function createFakeProductsAndCategories()
    {
        try {

            DB::transaction(function () {
                Category::insert(array(
                    [
                        'name' => 'Netflix',
                        'description' => 'Netflix Description',
                        'is_active' => true,
                        'image_path' => 'categories/id_1/image_1_name.png',
                        'quantity' => 0,
                        'is_leaf_category' => false,
                        'parent_id' => null,
                        'created_at' => now(),
                    ],
                    [
                        'name' => 'Netflix Turc 10$',
                        'description' => 'Netflix Turc 10$ Description',
                        'is_active' => true,
                        'image_path' => 'categories/id_1/image_1_name.png',
                        'quantity' => 0,
                        'is_leaf_category' => true,
                        'parent_id' => 1,
                        'created_at' => now(),
                    ],
                ));


                Product::create([
                    'category_id' => 2,
                    'id' => 12345,
                    'code' => Crypt::encryptString("code1"),
                    'code_start' => "code1",
                    'sold' => false,
                    'expiration_date' => "9999-12-31",
                    'purchase_price' => 2500,
                    'created_at' => now(),
                ]);
            });


        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating fake categories and products manually");
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


    public function test_successfull_get_paginated_products_by_filter_complete_id(): void
    {
        $this->superAdminLogin();

        $this->createFakeProductsAndCategories();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->postJson('api/products/get-paginated-products-by-filter', [
                    'id' => 12345
                ]);

        try {

            $response->assertStatus(200)
                ->assertJsonFragment([
                    'id' => 12345,
                    'code' => 'code1',
                ])
                ->assertJsonFragment([
                    'total' => 1
                ]);


        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_successfull_get_paginated_products_by_filter_id_with_like(): void
    {
        $this->superAdminLogin();

        $this->createFakeProductsAndCategories();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->postJson('api/products/get-paginated-products-by-filter', [
                    'id' => 234
                ]);

        try {

            $response->assertStatus(200)
                ->assertJsonFragment([
                    'id' => 12345,
                    'code' => 'code1',
                ])
                ->assertJsonFragment([
                    'total' => 1
                ])
            ;

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_get_paginated_products_by_filter_id_return_empty_array_when_sending_unexisting_id(): void
    {
        $this->superAdminLogin();

        $this->createFakeProductsAndCategories();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->postJson('api/products/get-paginated-products-by-filter', [
                    'id' => 789854
                ]);

        try {

            $response->assertStatus(200)
                ->assertJsonFragment([
                    'products_data' => [
                        'products' => [],
                        'meta' => null
                    ],
                ]);

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }

}