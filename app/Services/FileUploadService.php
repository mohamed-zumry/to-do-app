<?php

namespace App\Services;

use App\Jobs\FileUpload;
use App\Models\TaskFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    /**
     * @return false|string
     */
    public function uploadToS3(string $filePath, int $taskId)
    {
        //Use the file path to get the file instance
        $file = new UploadedFile($filePath, basename($filePath));

        //Store the file on S3 in the 'tasks' folder
        $path = $file->store('tasks','s3'); // 'tasks' is the folder name on S3

        //Save the file path to the database (task_files table)
        TaskFile::create([
            'task_id' => $taskId,
            'file_path' => $path,
        ]);

        return $path;
    }


    public function uploadFiles($request,$taskId){

        try {
            if ($request) {
                foreach ($request as $file) {
                    $filename = uniqid() . '.' . $file->getClientOriginalExtension();

                    // Store the file locally
                    $localPath = $file->storeAs('uploads', $filename, 'local');

                    // Dispatch the job with the necessary data
                    FileUpload::dispatch($localPath, $filename, $taskId);

                    return true;
                }
            }
        } catch (\Exception $e) {

            // Optionally return a response with the error message
            return response()->json([
                'message' => 'There was an issue with file upload.',
                'error' => $e->getMessage()
            ], 500);


        }

    }

    /**
     * Retrieve a file from S3.
     *
     * @return string
     */
    public function getFileFromS3(string $path)
    {
        // Check if the file exists on S3
        if (Storage::disk('s3')->exists($path)) {

            // Return the URL to access the file from S3
            return Storage::disk('s3')->url($path);
        }

        throw new \Exception('File not found.');
    }
}
