<?php

namespace Tests\Feature\Products\UpdateProductBaseDataTests;

use App\Models\Products\Product;
use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Services\JWTService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
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
                    'password' => Hash::make('a'),
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

    private function createFakeProducts()
    {

        try {
            Product::insert(array(
                [
                    'code' => Crypt::encryptString("code1"),
                    'sold' => false,
                    'expiration_date' => "2026-12-31",
                    'purchase_price' => 2500,
                    'created_at' => now(),
                ],
                [
                    'code' => Crypt::encryptString("code2"),
                    'sold' => false,
                    'expiration_date' => "2026-12-31",
                    'purchase_price' => 2500,
                    'created_at' => now(),
                ],
            ));

        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating fake products manually");
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


    public function test_successfull_update_product_base_data(): void
    {
        $this->adminLogin();

        $this->createFakeProducts();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->putJson('api/products/update-base-data/1', [
                    'code' => 'code11',
                    'expiration_date' => "2027-12-31",
                    'purchase_price' => 3000,
                ]);

        try {

            $this->assertDatabaseHas(
                "products",
                [
                    "id" => 1,
                    'expiration_date' => "2027-12-31",
                    'purchase_price' => 3000
                ]
            );


            $response->assertStatus(200)
                ->assertJsonFragment([
                    'message' => "Product's base data updated successfully."
                ]);

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}