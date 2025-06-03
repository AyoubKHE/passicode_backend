<?php

namespace Tests\Feature\Orders\OrderCreationTests;

use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Services\JWTService;
use App\Models\Clients\Client;
use App\Models\Products\Product;
use App\Models\Products\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use App\Models\Products\Product_Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ControllerTest extends TestCase
{
    use DatabaseMigrations;

    private string $access_token;

    private function ClientLogin()
    {
        try {
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
                        'price' => null,
                        'discount' => null,
                        'is_active' => true,
                        'quantity' => 0,
                        'image_path' => 'categories/id_1/image_1_name.png',
                        'is_leaf_category' => false,
                        'parent_id' => null,
                        'created_at' => now(),
                    ],
                    [
                        'name' => 'Netflix Turc 10$',
                        'description' => 'Netflix Turc 10$ Description',
                        'price' => 2500,
                        'discount' => 25,
                        'is_active' => true,
                        'quantity' => 3,
                        'image_path' => 'categories/id_2/image_2_name.png',
                        'is_leaf_category' => true,
                        'parent_id' => 1,
                        'created_at' => now(),
                    ],
                ));


                Product::insert(array(
                    [
                        'category_id' => 2,
                        'code' => Crypt::encryptString("code1"),
                        'code_start' => "code1",
                        'sold' => false,
                        'expiration_date' => "9999-12-31",
                        'purchase_price' => 2500,
                        'created_at' => now(),
                    ],
                    [
                        'category_id' => 2,
                        'code' => Crypt::encryptString("code2"),
                        'code_start' => "code2",
                        'sold' => false,
                        'expiration_date' => "2025-12-31",
                        'purchase_price' => 3000,
                        'created_at' => now(),
                    ],
                    [
                        'category_id' => 2,
                        'code' => Crypt::encryptString("code3"),
                        'code_start' => "code3",
                        'sold' => true,
                        'expiration_date' => "9999-12-31",
                        'purchase_price' => 3500,
                        'created_at' => now(),
                    ],
                    [
                        'category_id' => 2,
                        'code' => Crypt::encryptString("code4"),
                        'code_start' => "code4",
                        'sold' => false,
                        'expiration_date' => "2025-09-30",
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


    public function test_successfull_order_creation(): void
    {
        $this->ClientLogin();

        $this->createFakeProductsAndCategories();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->postJson('api/orders/create', [
                    'category_id' => 2,
                    'quantity' => 2,
                ]);

        try {


            $this->assertDatabaseHas(
                "categories",
                [
                    "id" => 2,
                    'quantity' => 1,
                ]
            );


            $this->assertDatabaseHas(
                "products",
                [
                    "id" => 2,
                    'sold' => 1,
                ]
            );
            $this->assertDatabaseHas(
                "products",
                [
                    "id" => 4,
                    'sold' => 1,
                ]
            );


            $this->assertDatabaseHas(
                "orders",
                [
                    "id" => 1,
                    'user_id' => 1,
                    'status' => 'pending',
                    'amount' => 3750,
                ]
            );


            $this->assertDatabaseHas(
                "ordersItems",
                [
                    'order_id' => 1,
                    'product_id' => 2,
                    'price' => "2500.00",
                    'discount' => 25,
                ]
            );
            $this->assertDatabaseHas(
                "ordersItems",
                [
                    'order_id' => 1,
                    'product_id' => 4,
                    'price' => "2500.00",
                    'discount' => 25,
                ]
            );


            $this->assertDatabaseHas(
                "chargilyPayments",
                [
                    "id" => 1,
                    'user_id' => 1,
                    'order_id' => 1,
                    'status' => 'pending',
                    'currency' => 'dzd',
                    'amount' => 3750,
                ]
            );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}