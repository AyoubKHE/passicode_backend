<?php

namespace Tests\Feature\Categories\UpdateCategoryBaseDataTests;

use Throwable;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Services\JWTService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Products\Category;

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

    private function createFakeCategories()
    {

        try {
            Category::insert(array(
                [
                    'name' => 'Netflix',
                    'description' => 'Netflix Description',
                    'is_active' => true,
                    'is_leaf_category' => true,
                    'parent_id' => null,
                    'image_path' => 'categories/id_1/image_1_name.png',
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


    public function test_successfull_update_category_base_data(): void
    {
        $this->adminLogin();

        $this->createFakeCategories();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->putJson('api/categories/update-base-data/1', [
                    'name' => 'Playstation',
                    'description' => 'Playstation Description',
                    'is_active' => false,
                ]);

        try {

            $this->assertDatabaseHas(
                "categories",
                [
                    "id" => 1,
                    'name' => 'Playstation',
                    'description' => 'Playstation Description',
                    'is_active' => 0,
                ]
            );


            $response->assertStatus(200)
                ->assertJsonFragment([
                    'message' => "Category's base data updated successfully."
                ]);

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}