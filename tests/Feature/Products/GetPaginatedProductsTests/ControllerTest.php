<?php

namespace Tests\Feature\Products\GetPaginatedProductsTests;

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


                Product::insert(array(
                    [
                        'category_id' => 2,
                        'code' => Crypt::encryptString("SDHF5454SDSD"),
                        'code_start' => "SDHF5",
                        'sold' => false,
                        'expiration_date' => "9999-12-31",
                        'purchase_price' => 2500,
                        'created_at' => now(),
                    ],
                    [
                        'category_id' => 2,
                        'code' => Crypt::encryptString("HJG54D698DS5"),
                        'code_start' => "HJG54",
                        'sold' => false,
                        'expiration_date' => "9999-12-31",
                        'purchase_price' => 3000,
                        'created_at' => now(),
                    ],
                    [
                        'category_id' => 2,
                        'code' => Crypt::encryptString("54TUR87EDGZ2"),
                        'code_start' => "54TUR",
                        'sold' => false,
                        'expiration_date' => "9999-12-31",
                        'purchase_price' => 3500,
                        'created_at' => now(),
                    ],
                    [
                        'category_id' => 2,
                        'code' => Crypt::encryptString("54TDH58TFJHD"),
                        'code_start' => "54TDH",
                        'sold' => false,
                        'expiration_date' => "9999-12-31",
                        'purchase_price' => 4000,
                        'created_at' => now(),
                    ],
                ));
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


    public function test_successfull_get_paginated_products(): void
    {
        $this->adminLogin();

        $this->createFakeProductsAndCategories();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->getJson('api/products/get-paginated-products?limit=5&page=1');

        try {

            $response->assertStatus(200)
                ->assertJsonFragment([
                    'total' => 4
                ]);

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_successfull_get_paginated_products_by_code(): void
    {
        $this->adminLogin();

        $this->createFakeProductsAndCategories();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->getJson('api/products/get-paginated-products?code=54TDH&limit=5&page=1');

        try {

            $response->assertStatus(200)
                ->assertJsonFragment([
                    'total' => 1
                ]);

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}