<?php

namespace Tests\Feature\Categories\UpdateParentCategoryTests;

use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Services\JWTService;
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


    public function test_successfull_update_parent_category_case_1(): void
    {
        $this->adminLogin();

        try {

            DB::transaction(function () {
                Category::insert(array(
                    [
                        'name' => 'Cat1',
                        'description' => 'Cat1 Description',
                        'is_active' => true,
                        'image_path' => 'categories/id_1/image_1_name.png',


                        'is_leaf_category' => false,
                        'parent_id' => null,
                        'created_at' => now(),

                    ],
                    [
                        'name' => 'Cat2',
                        'description' => 'Cat2 Description',
                        'is_active' => true,
                        'image_path' => 'categories/id_2/image_2_name.png',


                        'is_leaf_category' => true,
                        'parent_id' => null,
                        'created_at' => now(),

                    ],
                    [
                        'name' => 'Cat3',
                        'description' => 'Cat3 Description',
                        'is_active' => true,
                        'image_path' => 'categories/id_3/image_3_name.png',


                        'is_leaf_category' => true,
                        'parent_id' => 1,
                        'created_at' => now(),
                    ],
                ));

            });


        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating fake categories and products manually");
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->putJson('api/categories/update-parent-category/3', [
                    'new_parent_id' => 2,
                ]);

        try {

            $this->assertDatabaseHas(
                "categories",
                [
                    "id" => 1,
                    'name' => 'Cat1',
                    'description' => 'Cat1 Description',
                    'is_active' => 1,
                    'is_leaf_category' => 1,
                    'parent_id' => null,
                ]
            );

            $this->assertDatabaseHas(
                "categories",
                [
                    "id" => 2,
                    'name' => 'Cat2',
                    'description' => 'Cat2 Description',
                    'is_active' => 1,
                    'is_leaf_category' => 0,
                    'parent_id' => null,
                ]
            );

            $this->assertDatabaseHas(
                "categories",
                [
                    "id" => 3,
                    'name' => 'Cat3',
                    'description' => 'Cat3 Description',
                    'is_active' => 1,
                    'is_leaf_category' => 1,
                    'parent_id' => 2,
                ]
            );


            $response->assertStatus(200)
                ->assertJsonFragment([
                    'message' => "The parent category has been successfully updated."
                ]);

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}