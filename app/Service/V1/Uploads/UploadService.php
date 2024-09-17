<?php

namespace App\Service\V1\Uploads;

use Exception;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UploadService implements UploadInterfaceService
{

    /**
     * store files in base64 to bucket
     * @param $relativePath
     * @param $base64Image
     * @return mixed
     */
    public function storeBase64($relativePath, $base64Image)
    {
        try {

            $path = Storage::put($relativePath, base64_decode($base64Image));

            if ($path === false) {
                return false;
            }
            return $relativePath;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * store files to bucket
     * @param $file
     * @param string $type
     * @return mixed
     */
    public function store($file, $type)
    {
        try {
            $path = $file->storePublicly('public/' . $type);

            if ($path === false) {
                return false;
            }
            return $path;
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($file)
    {
        try {
            Storage::disk('s3')->delete('profiles/' . $file);
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'File deleting was not successful, please try again.',
                'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }
    /**
     * Update method to replace an old file with a new one.
     *
     * @param file $newFile      The path to the new file that will replace the old file.
     * @param string|null $oldFilePath  The optional path to the old file that will be replaced. If not provided, it may be determined internally.
     *
     * @return void  This method does not return anything (void).
     */
    public function update($newFile, $oldFilePath = null)
    {
        try {
            // Upload the new file to the storage
            $newFilePath = $newFile->storePublicly('public/images');

            // If an old file path is provided, delete the old file
            if ($oldFilePath) {
                Storage::delete($oldFilePath);
            }

            // Return the URL of the new file
            return Storage::url($newFilePath);
        } catch (\Throwable $th) {
            // Handle any exceptions that occur during the process
            return null;
        }
    }
}