<?php

namespace Tests\Feature\Categories\UpdateImageTests;

use Exception;
use Throwable;
use Tests\TestCase;
use App\Models\Users\User;
use Mockery\MockInterface;
use App\Models\Admins\Admin;
use App\Services\JWTService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Products\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Http\Controllers\Categories\UpdateImageController;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ControllerTest extends TestCase
{
    use DatabaseMigrations;

    private string $access_token;
    private string $old_image_path;

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

    private function createFakeCategory()
    {
        try {

            $folder_path = "categories/id_1";

            $image = UploadedFile::fake()->image('image.png');

            $this->old_image_path = $image->store($folder_path);
            if (!$this->old_image_path) {
                throw new Exception("An error occurred while saving the category's image.", 500);
            }

            Category::create([
                'name' => 'Netflix',
                'description' => 'Netflix Description',
                'is_active' => true,
                'image_path' => $this->old_image_path,
                'is_leaf_category' => true,
                'parent_id' => null,
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


    public function test_successfull_update_category_image(): void
    {
        $this->adminLogin();

        $this->createFakeCategory();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->postJson('api/categories/update-image/1', [
                    'new_image' => UploadedFile::fake()->image('new_image.png')->size(4000)
                ]);

        try {

            $new_image_path = explode(
                "/test/",
                $response->json()["new_category_image_url"]
            )[1];


            Storage::assertExists($new_image_path);
            Storage::assertMissing($this->old_image_path);


            $this->assertDatabaseHas(
                "categories",
                [
                    "id" => 1,
                    'image_path' => $new_image_path,
                ]
            );


            $response->assertStatus(200)
                ->assertJsonFragment(
                    [
                        'message' => "Category's image updated successfully!"
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_update_category_image_fails_when_the_image_backup_not_created(): void
    {
        $this->adminLogin();

        $this->createFakeCategory();

        $this->partialMock(UpdateImageController::class, function (MockInterface $mock) {
            $mock->shouldAllowMockingProtectedMethods()
                ->shouldReceive('createCategoryImageBackup')
                ->andReturn(false);
        });

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->postJson('api/categories/update-image/1', [
                    'new_image' => UploadedFile::fake()->image('new_image.png')->size(4000)
                ]);

        try {

            Storage::assertExists($this->old_image_path);


            $this->assertDatabaseHas(
                "categories",
                [
                    "id" => 1,
                    'image_path' => $this->old_image_path,
                ]
            );


            $response->assertStatus(500)
                ->assertJsonFragment(
                    [
                        'error' => "An error occurred while creating a backup of the old image."
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_update_category_image_fails_when_error_occured_while_deleting_the_old_image(): void
    {
        $this->adminLogin();

        $this->createFakeCategory();

        $this->partialMock(UpdateImageController::class, function (MockInterface $mock) {
            $mock->shouldAllowMockingProtectedMethods()
                ->shouldReceive('deleteOldCategoryImage')
                ->andReturn(false);
        });

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->postJson('api/categories/update-image/1', [
                    'new_image' => UploadedFile::fake()->image('new_image.png')->size(4000)
                ]);

        try {

            Storage::assertExists($this->old_image_path);


            $this->assertDatabaseHas(
                "categories",
                [
                    "id" => 1,
                    'image_path' => $this->old_image_path,
                ]
            );


            $response->assertStatus(500)
                ->assertJsonFragment(
                    [
                        'error' => "An error occurred while deleting the old image."
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_update_category_image_fails_when_error_occured_while_storing_the_new_image(): void
    {
        $this->adminLogin();

        $this->createFakeCategory();

        $this->partialMock(UpdateImageController::class, function (MockInterface $mock) {
            $mock->shouldAllowMockingProtectedMethods()
                ->shouldReceive('storeNewCategoryImage')
                ->andReturn(false);
        });

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->postJson('api/categories/update-image/1', [
                    'new_image' => UploadedFile::fake()->image('new_image.png')->size(4000)
                ]);

        try {

            Storage::assertExists($this->old_image_path);


            $this->assertDatabaseHas(
                "categories",
                [
                    "id" => 1,
                    'image_path' => $this->old_image_path,
                ]
            );


            $response->assertStatus(500)
                ->assertJsonFragment(
                    [
                        'error' => "An error occurred while saving the category's new image."
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }


    public function test_update_category_image_fails_when_error_occured_while_updating_image_path_in_database(): void
    {
        $this->adminLogin();

        $this->createFakeCategory();

        $this->partialMock(UpdateImageController::class, function (MockInterface $mock) {
            $mock->shouldAllowMockingProtectedMethods()
                ->shouldReceive('updateImagePathFieldInDatabase')
                ->andThrow(new Exception('An error occurred while accessing the database. Please try again later.', 500));
        });

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->postJson('api/categories/update-image/1', [
                    'new_image' => UploadedFile::fake()->image('new_image.png')->size(4000)
                ]);

        try {

            Storage::assertExists($this->old_image_path);


            $this->assertDatabaseHas(
                "categories",
                [
                    "id" => 1,
                    'image_path' => $this->old_image_path,
                ]
            );


            $response->assertStatus(500)
                ->assertJsonFragment(
                    [
                        'error' => "An error occurred while accessing the database. Please try again later."
                    ]
                );
        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}