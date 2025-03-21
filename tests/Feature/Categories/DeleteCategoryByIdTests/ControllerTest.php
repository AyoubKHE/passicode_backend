<?php

namespace Tests\Feature\Categories\DeleteCategoryByIdTests;

use Exception;
use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use App\Models\Admins\Admin;
use App\Services\JWTService;
use Illuminate\Http\UploadedFile;
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
    private string $old_category_image_path;

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

    private function createFakeCategory()
    {
        try {

            $folder_path = "categories/id_1";

            $image = UploadedFile::fake()->image('category_image.png');

            $this->old_category_image_path = $image->store($folder_path);
            if (!$this->old_category_image_path) {
                throw new Exception(
                    "An error occurred while saving the category's image.",
                    500
                );
            }

            Category::create([
                'name' => 'Netflix',
                'description' => 'Netflix Description',
                'is_active' => true,
                'is_leaf_category' => true,
                'parent_id' => null,
                'image_path' => $this->old_category_image_path,
                'created_at' => now(),
            ]);


        } catch (Throwable $th) {
            $this->markTestSkipped("test skipped because a problem occured while creating a category manually");
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


    public function test_successfull_delete_category_by_id(): void
    {
        $this->adminLogin();

        $this->createFakeCategory();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->deleteJson('api/categories/delete-by-id/1');

        try {

            Storage::assertMissing($this->old_category_image_path);


            $this->assertDatabaseMissing(
                "categories",
                [
                    'id' => 1,
                    'name' => 'Netflix',
                    'description' => 'Netflix Description',
                    'is_leaf_category' => true,
                    'parent_id' => null,
                    'is_active' => true,
                ]
            );


            $response->assertStatus(200)
                ->assertJsonFragment(
                    [
                        'message' => 'Category deleted successfully.'
                    ]
                );

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}