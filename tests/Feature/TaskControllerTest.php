<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_task_with_image()
    {

        Storage::fake('local');


        $user = User::factory()->create();
        $this->actingAs($user);


        $image = UploadedFile::fake()->image('task_image.jpg');

        $data = [
            'name' => 'Sample Task',
            'description' => 'This is a sample task description.',
            'status' => 'pending',
            'files' => [$image],
        ];


        $response = $this->postJson('api/tasks',$data);


        // Assert the task was created
        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'name' => 'Sample Task',
            'description' => 'This is a sample task description.',
        ]);

        // Assert the file was stored
        //TOdo : check file
        //  Storage::disk('local')->assertExists('uploads/' . $image->hashName());

    }


    public function test_authenticated_user_can_list_tasks()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        Task::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('api/tasks');

        $response->assertStatus(200);
    }


    public function test_authenticated_user_can_view_task()
    {
        // Arrange: Authenticate the user and create a task
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $task = Task::factory()->create(['user_id' => $user->id]);

        // Act: Send a GET request to view the task
        $response = $this->getJson("api/tasks/{$task->id}");

        // Assert: Check if the task details are returned
        $response->assertStatus(200)
            ->assertJson([
                'task' => [
                    'id' => $task->id,
                    'name' => $task->name,
                    'status' => $task->status,
                ],
            ]);
    }

    public function test_authenticated_user_can_update_task()
    {
        // Arrange: Authenticate the user and create a task
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $task = Task::factory()->create(['user_id' => $user->id]);

        // Act: Send a PUT request to update the task
        $response = $this->putJson("api/tasks/{$task->id}", [
            'name' => 'Updated Task Name',
            'description' => 'Updated description',
            'status' => 'completed',
            'files' => [], // Todo : add files and check
        ]);

        // Assert: Check if the task was updated successfully
        $response->assertStatus(200)
            ->assertJson(['message' => 'Task updated successfully.']);
    }

    public function test_authenticated_user_can_delete_task()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $task = Task::factory()->create(['user_id' => $user->id]);

        // Act: Send a DELETE request to remove the task
        $response = $this->deleteJson("api/tasks/{$task->id}");


        $response->assertStatus(200)
            ->assertJson(['message' => 'Task deleted successfully']);
    }



}
