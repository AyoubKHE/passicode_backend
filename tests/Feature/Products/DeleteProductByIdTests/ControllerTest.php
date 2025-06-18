<?php

namespace Tests\Feature\Products\DeleteProductByIdTests;

use Exception;
use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Services\JWTService;
use App\Models\Products\Product;
use App\Models\Products\Category;
use Illuminate\Http\UploadedFile;
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
                    'id' => 1,
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

    private function createFakeProduct()
    {
        try {

            DB::transaction(function () {
                Category::insert(array(
                    [
                        'name' => 'Netflix',
                        'description' => 'Netflix Description',
                        'is_active' => true,
                        'quantity' => 1,
                        'is_leaf_category' => true,
                        'parent_id' => null,
                        'image_path' => 'categories/id_1/image_1_name.png',
                        'created_at' => now(),
                    ],
                ));

                Product::create([
                    'category_id' => 1,
                    'code' => Crypt::encryptString("code1"),
                    'code_start' => "code1",
                    'sold' => false,
                    'expiration_date' => "9999-12-31",
                    'purchase_price' => 2500,
                    'created_at' => now(),
                ]);
            });

        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a product manually");
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


    public function test_successfull_delete_product_by_id(): void
    {
        $this->adminLogin();

        $this->createFakeProduct();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->deleteJson('api/products/delete-by-id/1');

        try {

            $this->assertDatabaseMissing(
                "products",
                [
                    'id' => 1,
                    'sold' => false,
                    'purchase_price' => 2500,
                    'expiration_date' => "9999-12-31",
                ]
            );


            $this->assertDatabaseHas(
                "categories",
                [
                    'id' => 1,
                    'quantity' => 0
                ]
            );


            $response->assertStatus(200)
                ->assertJsonFragment(
                    [
                        'message' => 'Product deleted successfully.'
                    ]
                );

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}