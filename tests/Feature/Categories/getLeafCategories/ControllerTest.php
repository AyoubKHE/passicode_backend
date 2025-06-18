<?php

namespace Tests\Feature\Categories\getLeafCategories;

use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Services\JWTService;
use App\Models\Products\Product;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
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

    private function createFakeCategoriesAndProducts()
    {

        try {

            DB::transaction(function () {
                Category::insert(array(
                    [
                        'name' => 'Netflix',
                        'description' => 'Netflix Description',
                        'is_active' => true,
                        'image_path' => 'categories/id_1/image_1_name.png',
                        'is_leaf_category' => false,
                        'parent_id' => null,
                        'created_at' => now(),
                    ],
                    [
                        'name' => 'Netflix 10$',
                        'description' => 'Netflix 10$ Description',
                        'is_active' => true,
                        'image_path' => 'categories/id_1/image_1_name.png',
                        'is_leaf_category' => true,
                        'parent_id' => 1,
                        'created_at' => now(),
                    ],
                    [
                        'name' => 'Netflix 20$',
                        'description' => 'Netflix 20$ Description',
                        'is_active' => true,
                        'image_path' => 'categories/id_1/image_1_name.png',
                        'is_leaf_category' => true,
                        'parent_id' => 1,
                        'created_at' => now(),
                    ],
                    [
                        'name' => 'Netflix 30$',
                        'description' => 'Netflix 30$ Description',
                        'is_active' => true,
                        'image_path' => 'categories/id_1/image_1_name.png',
                        'is_leaf_category' => true,
                        'parent_id' => 1,
                        'created_at' => now(),
                    ],
                ));


                Product::insert(array(
                    [
                        'category_id' => 2,
                        'code' => 'code1',
                        'code_start' => "code1",
                        'sold' => false,
                        'expiration_date' => "9999-12-31",
                        'purchase_price' => 2500,
                        'created_at' => now(),
                    ],
                    [
                        'category_id' => 2,
                        'code' => 'code2',
                        'code_start' => "code2",
                        'sold' => false,
                        'expiration_date' => "9999-12-31",
                        'purchase_price' => 2500,
                        'created_at' => now(),
                    ],
                    [
                        'category_id' => 3,
                        'code' => 'code3',
                        'code_start' => "code3",
                        'sold' => false,
                        'expiration_date' => "9999-12-31",
                        'purchase_price' => 2500,
                        'created_at' => now(),
                    ],
                    [
                        'category_id' => 3,
                        'code' => 'code4',
                        'code_start' => "code4",
                        'sold' => false,
                        'expiration_date' => "9999-12-31",
                        'purchase_price' => 2500,
                        'created_at' => now(),
                    ],
                    [
                        'category_id' => 4,
                        'code' => 'code5',
                        'code_start' => "code5",
                        'sold' => false,
                        'expiration_date' => "9999-12-31",
                        'purchase_price' => 2500,
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


    public function test_successfull_get_leaf_categories(): void
    {
        $this->adminLogin();

        $this->createFakeCategoriesAndProducts();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->getJson('api/categories/get-leaf-categories');

        try {

            $response->assertStatus(200)
                ->assertJsonPath(
                    'leaf_categories.0.name',
                    'Netflix 10$'
                )->assertJsonPath(
                    'leaf_categories.1.name',
                    'Netflix 20$'
                )->assertJsonPath(
                    'leaf_categories.2.name',
                    'Netflix 30$'
                );

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}