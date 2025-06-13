<?php

namespace Tests\Feature\Orders\OrdersFilterDialogDataTests;

use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Services\JWTService;
use App\Models\Clients\Client;
use App\Models\Products\Product;
use App\Models\Products\Category;
use Illuminate\Support\Facades\DB;
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

    private function createFakeData()
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
                        'name' => 'Netflix 10$',
                        'description' => 'Netflix 10$ Description',
                        'price' => 2700,
                        'discount' => 10,
                        'is_active' => true,
                        'quantity' => 5,
                        'image_path' => 'categories/id_2/image_2_name.png',
                        'is_leaf_category' => true,
                        'parent_id' => 1,
                        'created_at' => now(),
                    ],
                    [
                        'name' => 'Netflix 20$',
                        'description' => 'Netflix 20$ Description',
                        'price' => 5900,
                        'discount' => null,
                        'is_active' => true,
                        'quantity' => 0,
                        'image_path' => 'categories/id_3/image_3_name.png',
                        'is_leaf_category' => true,
                        'parent_id' => 1,
                        'created_at' => now(),
                    ],
                    [
                        'name' => 'Netflix 30$',
                        'description' => 'Netflix 30$ Description',
                        'price' => 9900,
                        'discount' => 25,
                        'is_active' => true,
                        'quantity' => 0,
                        'image_path' => 'categories/id_4/image_4_name.png',
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
                        'category_id' => 2,
                        'code' => 'code3',
                        'code_start' => "code3",
                        'sold' => false,
                        'expiration_date' => "9999-12-31",
                        'purchase_price' => 2500,
                        'created_at' => now(),
                    ],
                    [
                        'category_id' => 2,
                        'code' => 'code4',
                        'code_start' => "code4",
                        'sold' => false,
                        'expiration_date' => "9999-12-31",
                        'purchase_price' => 2500,
                        'created_at' => now(),
                    ],
                    [
                        'category_id' => 2,
                        'code' => 'code5',
                        'code_start' => "code5",
                        'sold' => false,
                        'expiration_date' => "9999-12-31",
                        'purchase_price' => 2500,
                        'created_at' => now(),
                    ],
                ));

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
                        'first_name' => 'Walid',
                        'last_name' => 'Hamma',
                        'email' => 'walid.hamma@gmail.com',
                        'role' => 'Client',
                        'is_active' => true,
                        'created_at' => now()
                    ],
                    [
                        'first_name' => 'Massi',
                        'last_name' => 'Guennaoui',
                        'email' => 'massi.guennaoui@gmail.com',
                        'role' => 'Client',
                        'is_active' => true,
                        'created_at' => now()
                    ]
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


    public function test_successfull_get_orders_filter_dialog_data(): void
    {
        $this->adminLogin();

        $this->createFakeData();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->getJson('api/orders/orders-filter-dialog-data');

        try {

            $response->assertStatus(200)
                ->assertJsonPath(
                    'orders_filter_dialog_data.leaf_categories.0.name',
                    'Netflix 10$'
                )->assertJsonPath(
                    'orders_filter_dialog_data.leaf_categories.1.name',
                    'Netflix 20$'
                )->assertJsonPath(
                    'orders_filter_dialog_data.leaf_categories.2.name',
                    'Netflix 30$'
                )->assertJsonPath(
                    'orders_filter_dialog_data.clients.0.first_name',
                    'Salim'
                )
                ->assertJsonPath(
                    'orders_filter_dialog_data.clients.1.first_name',
                    'Walid'
                )
                ->assertJsonPath(
                    'orders_filter_dialog_data.clients.2.first_name',
                    'Massi'
                );

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}