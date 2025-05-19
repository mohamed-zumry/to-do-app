<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Jobs\FileUpload;
use App\Models\Task;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TaskController extends Controller
{

    protected $fileService;

    public function __construct(FileUploadService  $fileService)
    {
        $this->fileService = $fileService;
    }
    // Ceate Tasks file files
    public function store(TaskRequest $request)
    {
        $task = Task::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
            'status' => 'pending',
        ]);


        Cache::forget('tasks_'.Auth::id());

        // offloead the Job to file uplaod service que
        if ($request->hasFile('files')) {
            $this->fileService->uploadFiles($request->file('files'), $task->id);
        }


        return response()->json(['message' => 'Task created successfully'], 201);
    }


    public function show($id)
    {
        $task = Task::where('user_id', Auth::id())
            ->with('files')  // Eager load the 'files' relationship
            ->findOrFail($id);

        $taskFiles = [];

        // Todo : move to services
        if ($task->files->isNotEmpty()) {
            foreach ($task->files as $file) {
                try {
                    // Retrieve file URLs from S3 using the FileService
                    $taskFiles[] = $this->fileService->getFileFromS3($file->file_path);
                } catch (\Exception $e) {
                    $taskFiles[] = ['error' => 'File not found.'];
                }
            }
        }

        return response()->json([
            'task' => $task,         // The task data
            'files' => $taskFiles,    // List of files and their URLs or errors
        ]);
    }

    // List all tasks for the authenticated user
    public function index()
    {

        $tasks = Task::where('user_id', Auth::id())
            ->select('id', 'name', 'status')
            ->paginate(5);

        return response()->json($tasks);
    }

    // Destroy a task and associated files
    public function destroy($id)
    {
        // Find the task by ID for the authenticated user
        $task = Task::where('user_id', Auth::id())->findOrFail($id);

        // delete related files (from S3) before deleting the task
        foreach ($task->files as $file) {
            Storage::disk('s3')->delete($file->file_path);  // Delete file from S3
            $file->delete();  // Delete file record from the database
        }

        // Delete the task
        $task->delete();

        // Invalidate cache
        Cache::forget('tasks_'.Auth::id());

        return response()->json(['message' => 'Task deleted successfully']);
    }

    // Update an existing task
    public function update(TaskRequest $request, $id)
    {
        // Find the task and check if the authenticated user owns it
        $task = Task::where('user_id', Auth::id())->findOrFail($id);


        $task->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
        ]);


        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                FileUpload::dispatch($file, $task->id, $this->fileService);
            }
        }
        // Invalidate cache
        Cache::forget('tasks_'.Auth::id());

        return response()->json(['message' => 'Task updated successfully.']);
    }
}
