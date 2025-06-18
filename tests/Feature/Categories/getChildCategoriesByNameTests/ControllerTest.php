<?php

namespace Tests\Feature\Categories\getChildCategoriesByNameTests;

use Throwable;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use App\Models\Products\Category;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ControllerTest extends TestCase
{
    use DatabaseMigrations;

    private function createFakeCategories()
    {

        try {

            Category::insert(array(
                [
                    'name' => 'Netflix',
                    'description' => 'Netflix Description',
                    'is_active' => true,
                    'image_path' => 'categories/id_1/image_1_name.png',
                    'is_leaf_category' => false,
                    'parent_id' => null,
                    'created_at' => now(),
                ],
                [
                    'name' => 'Netflix USA',
                    'description' => 'Netflix USA Description',
                    'is_active' => true,
                    'image_path' => 'categories/id_2/image_2_name.png',
                    'is_leaf_category' => false,
                    'parent_id' => 1,
                    'created_at' => now(),
                ],
                [
                    'name' => 'Netflix Turkey',
                    'description' => 'Netflix Turkey Description',
                    'is_active' => true,
                    'image_path' => 'categories/id_3/image_3_name.png',
                    'is_leaf_category' => false,
                    'parent_id' => 1,
                    'created_at' => now(),
                ],
                [
                    'name' => 'Netflix France',
                    'description' => 'Netflix France Description',
                    'is_active' => true,
                    'image_path' => 'categories/id_4/image_4_name.png',
                    'is_leaf_category' => false,
                    'parent_id' => 1,
                    'created_at' => now(),
                ]
            ));


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


    public function test_successfull_get_child_categories(): void
    {
        $this->createFakeCategories();

        $response = $this->getJson('api/categories/get-child-categories-by-name/netflix');

        try {

            $response->assertStatus(200)
                ->assertJsonPath(
                    'data.categories.0.name',
                    'Netflix USA'
                )->assertJsonPath(
                    'data.categories.1.name',
                    'Netflix Turkey'
                )->assertJsonPath(
                    'data.categories.2.name',
                    'Netflix France'
                );

        } catch (Throwable $th) {
            $this->fail("Test failed: " . $th->getMessage());
        }
    }
}