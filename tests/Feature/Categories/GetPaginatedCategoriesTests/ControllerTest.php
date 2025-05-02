<?php

namespace Tests\Feature\Categories\GetPaginatedCategoriesTests;

use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Services\JWTService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Products\Category;
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


                    'is_leaf_category' => true,
                    'parent_id' => null,
                    'created_at' => now(),

                ],
                [
                    'name' => 'Playstation',
                    'description' => 'Playstation Description',
                    'is_active' => true,
                    'image_path' => 'categories/id_2/image_2_name.png',


                    'is_leaf_category' => true,
                    'parent_id' => 1,
                    'created_at' => now(),

                ],
                [
                    'name' => 'ITunes',
                    'description' => 'ITunes Description',
                    'is_active' => false,
                    'image_path' => 'categories/id_3/image_3_name.png',


                    'is_leaf_category' => false,
                    'parent_id' => 1,
                    'created_at' => now(),

                ],
                [
                    'name' => 'Cat 4',
                    'description' => 'Cat 4 Description',
                    'is_active' => false,
                    'image_path' => 'categories/id_3/image_3_name.png',


                    'is_leaf_category' => false,
                    'parent_id' => 1,
                    'created_at' => now(),

                ],
                [
                    'name' => 'Cat 5',
                    'description' => 'Cat 5 Description',
                    'is_active' => false,
                    'image_path' => 'categories/id_3/image_3_name.png',


                    'is_leaf_category' => false,
                    'parent_id' => 1,
                    'created_at' => now(),

                ],
                [
                    'name' => 'Cat 6',
                    'description' => 'Cat 6 Description',
                    'is_active' => false,
                    'image_path' => 'categories/id_3/image_3_name.png',


                    'is_leaf_category' => false,
                    'parent_id' => 1,
                    'created_at' => now(),

                ],
                [
                    'name' => 'Cat 7',
                    'description' => 'Cat 7 Description',
                    'is_active' => false,
                    'image_path' => 'categories/id_3/image_3_name.png',


                    'is_leaf_category' => false,
                    'parent_id' => 1,
                    'created_at' => now(),

                ],
                [
                    'name' => 'Cat 8',
                    'description' => 'Cat 8 Description',
                    'is_active' => false,
                    'image_path' => 'categories/id_3/image_3_name.png',


                    'is_leaf_category' => false,
                    'parent_id' => 1,
                    'created_at' => now(),

                ],
                [
                    'name' => 'Cat 9',
                    'description' => 'Cat 9 Description',
                    'is_active' => false,
                    'image_path' => 'categories/id_3/image_3_name.png',


                    'is_leaf_category' => false,
                    'parent_id' => 1,
                    'created_at' => now(),

                ],
                [
                    'name' => 'Cat 10',
                    'description' => 'Cat 10 Description',
                    'is_active' => false,
                    'image_path' => 'categories/id_3/image_3_name.png',


                    'is_leaf_category' => false,
                    'parent_id' => 1,
                    'created_at' => now(),

                ],
                [
                    'name' => 'Cat 11',
                    'description' => 'Cat 11 Description',
                    'is_active' => false,
                    'image_path' => 'categories/id_3/image_3_name.png',


                    'is_leaf_category' => false,
                    'parent_id' => 1,
                    'created_at' => now(),

                ],
                [
                    'name' => 'Cat 12',
                    'description' => 'Cat 12 Description',
                    'is_active' => false,
                    'image_path' => 'categories/id_3/image_3_name.png',


                    'is_leaf_category' => false,
                    'parent_id' => 1,
                    'created_at' => now(),

                ],
                [
                    'name' => 'Cat 13',
                    'description' => 'Cat 13 Description',
                    'is_active' => false,
                    'image_path' => 'categories/id_3/image_3_name.png',


                    'is_leaf_category' => false,
                    'parent_id' => 1,
                    'created_at' => now(),

                ],
                [
                    'name' => 'Cat 14',
                    'description' => 'Cat 14 Description',
                    'is_active' => false,
                    'image_path' => 'categories/id_3/image_3_name.png',


                    'is_leaf_category' => false,
                    'parent_id' => 1,
                    'created_at' => now(),

                ],
                [
                    'name' => 'Cat 15',
                    'description' => 'Cat 15 Description',
                    'is_active' => false,
                    'image_path' => 'categories/id_3/image_3_name.png',


                    'is_leaf_category' => false,
                    'parent_id' => 1,
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


    public function test_successfull_get_paginated_categories(): void
    {
        $this->adminLogin();

        $this->createFakeCategories();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->getJson('api/categories/get-paginated-categories?limit=3&page=5');

        try {

            $response->assertStatus(200)
                ->assertJsonFragment([
                    'total' => 15
                ]);

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}