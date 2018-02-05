<?php

namespace Tests\Controller;

use App\Task;
use App\Transformers\TaskTransformer;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Spatie\Fractalistic\Fractal;
use Tests\TestCase;


class TaskTest extends TestCase
{
	use DatabaseTransactions;




	public function test_it_stores_new_task()
	{
		$attributes = factory(Task::class)->make([
			'privacy' => false,
			'status' => true,
		])->toArray();

		$user = $this->createAndAuthenticateUser();


		$response = $this->post( route('tasks.store'), $attributes);

		$response->assertJson(['success' => 'Task has been created!', 'id' => Task::first()->id]);
	}



	public function test_that_it_shows_single_task()
	{

		$user = $this->createAndAuthenticateUser();


		$task = factory(Task::class)->create();

		$response = $this->get(route('tasks.show', ['task' => $task->id]));

		$response
			->assertJson([
				'data' => [
					'id' => $task->id,
					'description' => $task->description,
					'privacy' => $task->private ? 'Private' : 'Public',
					'status' => $task->status ? 'Done' : 'Todo',
					'deadline' => Carbon::parse( $task->deadline)->format('Y-m-d H')
				]
			]);
	}

	public function test_it_updates_task()
	{
		$task = factory(Task::class)->create()->toArray();

		$user = $this->createAndAuthenticateUser();


		$response = $this->put(route('tasks.update', ['task' => $task['id']]), $task);

		$task = Task::find($task['id']);

		$task = Fractal::create()->item($task)->transformWith(TaskTransformer::class)->toArray();

		$response->assertJson($task);


	}


	public function test_it_deletes_task()
	{
		$task = factory(Task::class)->create();

		$user = $this->createAndAuthenticateUser();


		$response = $this->delete(route('tasks.destroy', ['task' => $task->id]));


		$response->assertJson(['success' => 'The Task has been deleted!']);

	}

	public function test_guests_can_see_public_tasks()
	{

		$task = factory(Task::class)->create();

		$userId = $task->user_id;

		$task = Fractal::create()
		               ->item($task)
		               ->transformWith(TaskTransformer::class)->toJson();



		$response = $this->get(route('users.tasks.view', ['user' => $userId]));

		$this->assertJson($task, $response->json());
	}

}
