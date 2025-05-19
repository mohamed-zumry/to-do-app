<?php

namespace App\Jobs;

use App\Models\TaskFile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileUpload implements ShouldQueue
{
    use InteractsWithQueue , Queueable , SerializesModels;

    protected $filePath;
    protected $fileName;
    protected $taskId;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $fileName, $taskId)
    {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->taskId = $taskId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        try {
            // Retrieve the file from local storage
            $file = Storage::disk('local')->get($this->filePath);

            // Store the file on S3
            Storage::disk('s3')->put('tasks/' . $this->fileName, $file);

            // Save the file path in the database
            TaskFile::create([
                'task_id' => $this->taskId,
                'file_path' => 'tasks/' . $this->fileName,
            ]);
        } catch (\Exception $e) {
            // Log the exception message
            Log::error('File upload failed: ' . $e->getMessage());

        }
    }
}
