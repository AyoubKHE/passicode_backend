<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class BackupService
{
    private static function copyFiles(string $source_folder, string $destination_folder): bool
    {
        $files = Storage::files($source_folder);

        foreach ($files as $file) {

            $file_name_parts = explode("/", $file);
            $file_name = $file_name_parts[count($file_name_parts) - 1];

            $source_path = $source_folder . '/' . $file_name;
            $destination_path = $destination_folder . '/' . $file_name;
            if (!Storage::copy($source_path, $destination_path)) {
                Storage::deleteDirectory($destination_folder);
                return false;
            }
        }

        return true;
    }

    public static function createImagesBackup(string $table_name, string $id): bool
    {
        $source_folder = $table_name . "/id_" . $id;
        $backup_folder = $table_name . "/temp/id_" . $id;

        return self::copyFiles($source_folder, $backup_folder);
    }

    public static function deleteImagesBackup(string $table_name, string $id)
    {
        $backup_folder = $table_name . "/temp/id_" . $id;
        Storage::deleteDirectory($backup_folder);
    }

    public static function makeImagesRestoration(string $table_name, string $id)
    {
        $backup_folder = $table_name . "/temp/id_" . $id;
        $source_folder = $table_name . "/id_" . $id;

        // supprimer l'ancien dossier
        Storage::deleteDirectory($source_folder);

        if (self::copyFiles($backup_folder, $source_folder)) {
            // supprimer le dossier de la sauvegarde
            Storage::deleteDirectory($backup_folder);
        }
    }
}
