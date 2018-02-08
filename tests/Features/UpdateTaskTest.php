<?php

namespace Tests\Controller;

use App\Task;
use App\Transformers\TaskTransformer;
use App\User;
use App\Watch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Fractalistic\Fractal;
use Tests\TestCase;


class UpdateTaskTest extends TestCase
{
	use DatabaseTransactions;



	public function test_it_updates_task()
	{

		$currentUser = $this->createAndAuthenticateUser();

		$task = factory(Task::class)->create(['user_id' => $currentUser->id])->toArray();

		$task['name'] = 'name';
		$task['description'] = 'description';
		$task['privacy'] = true;


		$response = $this->put(route('tasks.update', ['task' => $task['id']]), $task);

		$updatedTask = Task::find($task['id']);

		$taskResponse = Fractal::create()->item($updatedTask)->transformWith(TaskTransformer::class)->toArray();

		$response->assertJson($taskResponse);

		$response->assertStatus(200);

		$this->assertArraySubset($task, $updatedTask->toArray());


	}

	public function test_that_user_can_not_update_task_he_does_not_own()
	{
		$task = factory(Task::class)->create()->toArray();

		$currentUser = $this->createAndAuthenticateUser();

		$task['name'] = 'name';
		$task['description'] = 'description';
		$task['privacy'] = true;


		$response = $this->put(route('tasks.update', ['task' => $task['id']]), $task);

		$response->assertStatus(403);

		$response->assertJson(['error' => 'unauthorized to perform this action']);

		$this->assertCount(0, Task::where(['name' => $task['name']])->get());

	}



}
