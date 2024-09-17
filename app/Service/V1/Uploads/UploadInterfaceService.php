<?php

namespace App\Service\V1\Uploads;

interface UploadInterfaceService
{
       /**
     * store files in base64 to bucket
     * @param $relativePath
     * @param $base64Image
     * @return mixed
     */
    public function storeBase64($relativePath, $base64Image);

    /**
     * store files
     * @param $file
     * @param string $type
     * @return mixed
     */
    public function store($file, $type);

    /**
     * Delete a file from the storage.
     *
     * @param string $file The file path to be deleted.
     * @return array An array containing the result of the deletion operation.
     */
    public function delete($file);

    /**
     * Update method to replace an old file with a new one.
     *
     * @param file $newFile      The path to the new file that will replace the old file.
     * @param string|null $oldFilePath  The optional path to the old file that will be replaced. If not provided, it may be determined internally.
     *
     * @return void  This method does not return anything (void).
     */
    public function update($newFile, $oldFilePath = null);
}