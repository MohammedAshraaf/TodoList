<?php

namespace Tests\Controller;

use App\Task;
use App\Transformers\TaskTransformer;
use App\User;
use App\Watch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Fractalistic\Fractal;
use Tests\TestCase;


class ShowTaskTest extends TestCase
{
	use DatabaseTransactions;



	public function test_that_user_can_show_his_own_task()
	{

		$user = $this->createAndAuthenticateUser();


		$task = factory(Task::class)->create(['user_id' => $user->id]);

		$response = $this->get(route('tasks.show', ['task' => $task->id]));

		$task = Fractal::create()->item($task)->transformWith(TaskTransformer::class)->toArray();


		$response->assertStatus(200);

		$response->assertJson($task);
	}


	public function test_user_can_not_show_task_he_does_not_own_and_it_is_not_public()
	{
		$someTask = factory(Task::class)->create(['privacy' => 1]);

		$currentUser = $this->createAndAuthenticateUser();

		$response = $this->get(route('tasks.show', ['task' => $someTask->id]));

		$response->assertStatus(403);

		$response->json(['error' => 'unauthorized to perform this action']);

	}


	public function test_user_can_show_task_he_does_not_own_but_he_watch()
	{
		$currentUser = $this->createAndAuthenticateUser();

		$task = factory(Task::class)->create();

		Watch::create(['user_id' => $currentUser->id, 'task_id' => $task->id]);

		$response = $this->get(route('tasks.show', ['task' => $task->id]));

		$task = Fractal::create()->item($task)->transformWith(TaskTransformer::class)->toArray();


		$response->assertStatus(200);

		$response->assertJson($task);
	}


}
