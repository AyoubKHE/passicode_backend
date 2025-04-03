<?php

namespace App\Http\Controllers\Categories;

use Storage;
use Exception;
use Throwable;
use App\Services\BackupService;
use App\Http\Controllers\Controller;
use App\Models\Products\Category;
use App\Http\Requests\Categories\UpdateImageRequest;


class UpdateImageController extends Controller
{
    private UpdateImageRequest $global_request_object;
    private Category|null $requested_category;

    protected function updateImagePathFieldInDatabase()
    {
        $this->requested_category->updated_at = now();

        try {
            $is_updated = $this->requested_category->save();
        } catch (Throwable $throwable) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        if (!$is_updated) {
            throw new Exception(
                'An error occurred while accessing the database. Please try again later.',
                500
            );
        }

        BackupService::deleteImagesBackup(
            "categories",
            $this->requested_category->id
        );
    }


    protected function storeNewCategoryImage()
    {
        $image_file = $this->global_request_object->file('new_image');
        $folder_path = 'categories/id_' . $this->requested_category->id;

        return $image_file->store($folder_path);
    }
    protected function deleteOldCategoryImage()
    {
        return Storage::delete($this->requested_category->image_path);
    }
    protected function createCategoryImageBackup()
    {
        return BackupService::createImagesBackup(
            "categories",
            $this->requested_category->id
        );
    }
    private function storeTheNewImageOnDisk()
    {
        if (
            !$this->createCategoryImageBackup()
        ) {
            throw new Exception(
                'An error occurred while creating a backup of the old image.',
                500
            );
        }

        if (
            !$this->deleteOldCategoryImage()
        ) {

            BackupService::deleteImagesBackup(
                "categories",
                $this->requested_category->id
            );

            throw new Exception(
                'An error occurred while deleting the old image.',
                500
            );
        }

        $this->requested_category->image_path = $this->storeNewCategoryImage();
        if (!$this->requested_category->image_path) {
            BackupService::makeImagesRestoration(
                "categories",
                $this->requested_category->id
            );

            throw new Exception(
                "An error occurred while saving the category's new image.",
                500
            );
        }
    }


    private function loadRequestedCategory()
    {
        try {

            $this->requested_category = Category::where(
                "id",
                $this->global_request_object->category_id
            )->first();
        } catch (Throwable $th) {
            throw new Exception('An error occurred while accessing the database. Please try again later.', 500);
        }

        if (!$this->requested_category) {
            throw new Exception('Requested category not found.', 404);
        }
    }


    public function __invoke(UpdateImageRequest $request)
    {
        $this->global_request_object = $request;

        $this->loadRequestedCategory();

        $this->storeTheNewImageOnDisk();

        try {
            $this->updateImagePathFieldInDatabase();

            return response()->json([
                'message' => "Category's image updated successfully!",
                'new_category_image_url' => Storage::url(
                    $this->requested_category->image_path
                )
            ], 200);

        } catch (Throwable $th) {

            BackupService::makeImagesRestoration(
                "categories",
                $this->requested_category->id
            );

            throw $th;
        }

    }
}
