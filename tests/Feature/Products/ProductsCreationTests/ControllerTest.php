<?php

namespace Tests\Feature\Products\ProductsCreationTests;

use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Services\JWTService;
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


    private function createFakeCategories()
    {

        try {
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
                    'name' => 'Netflix Turc',
                    'description' => 'Netflix Turc Description',
                    'is_active' => true,
                    'image_path' => 'categories/id_1/image_1_name.png',
                    'quantity' => 0,
                    'is_leaf_category' => false,
                    'parent_id' => 1,
                    'created_at' => now(),

                ],
                [
                    'name' => 'Netflix Turc 10$',
                    'description' => 'Netflix Turc 10$ Description',
                    'is_active' => true,
                    'image_path' => 'categories/id_1/image_1_name.png',
                    'quantity' => 0,
                    'is_leaf_category' => true,
                    'parent_id' => 2,
                    'created_at' => now(),

                ],
            ));

        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating fake categories manually");
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


    public function test_successfull_products_creation(): void
    {
        $this->adminLogin();

        $this->createFakeCategories();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->postJson('api/products/create', [
                    'products' => array(
                        [
                            'code' => 'code 1',
                            'expiration_date' => null,
                            'purchase_price' => 2000,
                        ],
                        [
                            'code' => 'code 2',
                            'expiration_date' => null,
                            'purchase_price' => 2500,
                        ],
                        [
                            'code' => 'code 3',
                            'expiration_date' => "2025-12-31",
                            'purchase_price' => 3000,
                        ],
                        [
                            'code' => 'code 4',
                            'expiration_date' => "2026-12-31",
                            'purchase_price' => 3500,
                        ]
                    ),
                    'related_category_id' => 3
                ]);

        try {

            $this->assertDatabaseHas(
                "products",
                [
                    "id" => 1,
                    'sold' => 0,
                    'expiration_date' => "9999-12-31",
                    'purchase_price' => 2000,
                ]
            );
            $this->assertDatabaseHas(
                "products",
                [
                    "id" => 2,
                    'sold' => 0,
                    'expiration_date' => "9999-12-31",
                    'purchase_price' => 2500,
                ]
            );
            $this->assertDatabaseHas(
                "products",
                [
                    "id" => 3,
                    'sold' => 0,
                    'expiration_date' => "2025-12-31",
                    'purchase_price' => 3000,
                ]
            );
            $this->assertDatabaseHas(
                "products",
                [
                    "id" => 4,
                    'sold' => 0,
                    'expiration_date' => "2026-12-31",
                    'purchase_price' => 3500,
                ]
            );


            $this->assertDatabaseHas(
                "categories",
                [
                    "id" => 3,
                    'quantity' => 4,
                ]
            );


            $response->assertStatus(201)
                ->assertJsonFragment(
                    [
                        'message' => 'Products created successfully.'
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}