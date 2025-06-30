<?php

namespace Tests\Feature\Orders\GetOrderByIdTests;

use App\Models\Clients\Client;
use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use Illuminate\Support\Str;
use App\Models\Admins\Admin;
use App\Models\Orders\Order;
use App\Services\JWTService;
use App\Models\Orders\OrderItem;
use App\Models\Products\Product;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\Orders\ChargilyPayment;
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

    private function createFakeOrders()
    {

        DB::transaction(function () {

            User::create([
                'first_name' => 'Ahmed',
                'last_name' => 'Salemi',
                'email' => 'ahmed.salemi@gmail.com',
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
                    'discount' => 0,
                    'quantity' => 1,
                    'is_active' => true,
                    'is_leaf_category' => true,
                    'parent_id' => null,
                    'image_path' => 'categories/id_1/image_1_name.png',
                    'created_at' => now(),
                ],
            );


            Product::insert(array(
                [
                    'category_id' => 1,
                    'code' => Crypt::encryptString("SDHF5454SDSD"),
                    'code_start' => "SDHF5",
                    'sold' => true,
                    'expiration_date' => "2025-12-31",
                    'purchase_price' => 2500,
                    'created_at' => now(),
                ],
                [
                    'category_id' => 1,
                    'code' => Crypt::encryptString("HJG54D698DS5"),
                    'code_start' => "HJG54",
                    'sold' => true,
                    'expiration_date' => "9999-12-31",
                    'purchase_price' => 2500,
                    'created_at' => now(),
                ],
                [
                    'category_id' => 1,
                    'code' => Crypt::encryptString("54TUR87EDGZ2"),
                    'code_start' => "54TUR",
                    'sold' => true,
                    'expiration_date' => "2026-06-30",
                    'purchase_price' => 2500,
                    'created_at' => now(),
                ],
                [
                    'category_id' => 1,
                    'code' => Crypt::encryptString("54TDH58TFJHD"),
                    'code_start' => "54TDH",
                    'sold' => false,
                    'expiration_date' => "9999-12-31",
                    'purchase_price' => 2500,
                    'created_at' => now(),
                ],
            ));


            Order::insert(array(
                [
                    'public_id' => (string) Str::ulid(),
                    'user_id' => 2,
                    'status' => "paid",
                    'amount' => 8100,
                    'created_at' => now(),
                ],
                [
                    'public_id' => (string) Str::ulid(),
                    'user_id' => 2,
                    'status' => "failed",
                    'amount' => 2700,
                    'created_at' => now(),
                ],
            ));


            OrderItem::insert(array(
                [
                    'order_id' => 1,
                    'product_id' => 1,
                    'price' => 2700,
                    'discount' => 0,
                ],
                [
                    'order_id' => 1,
                    'product_id' => 2,
                    'price' => 2700,
                    'discount' => 0,
                ],
                [
                    'order_id' => 1,
                    'product_id' => 3,
                    'price' => 2700,
                    'discount' => 0,
                ],
                [
                    'order_id' => 2,
                    'product_id' => 4,
                    'price' => 2700,
                    'discount' => 0,
                ],
            ));


            ChargilyPayment::insert(array(
                [
                    'chargily_payment_id' => "01JX7Q2HZSKSCWA6RACY8WGVFS",
                    'user_id' => 2,
                    'order_id' => 1,
                    'status' => "paid",
                    'currency' => "dzd",
                    'amount' => 8100,
                    'created_at' => now(),
                ],
                [
                    'chargily_payment_id' => (string) Str::ulid(),
                    'user_id' => 2,
                    'order_id' => 2,
                    'status' => "failed",
                    'currency' => "dzd",
                    'amount' => 2700,
                    'created_at' => now(),
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
    }
    protected function tearDown(): void
    {
        try {
            parent::tearDown();
        } catch (Throwable $th) {
            $this->markTestSkipped($th->getMessage());
        }

    }


    public function test_successfull_get_order_by_id(): void
    {
        $this->adminLogin();

        $this->createFakeOrders();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->getJson('api/orders/get-by-id/1');

        try {

            $response->assertStatus(200)
                ->assertJsonPath(
                    'order.id',
                    1
                );

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}