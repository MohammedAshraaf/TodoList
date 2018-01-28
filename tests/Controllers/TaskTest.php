<?php

namespace Tests\Controller;

use App\Task;
use App\Transformers\TaskTransformer;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Fractalistic\Fractal;
use Tests\TestCase;


class TaskTest extends TestCase
{
	use DatabaseTransactions;

	/**
	 * Creates new user with client id to authenticate through passport
	 * @return mixed
	 */
	public function createNewUserWithClientRecord()
	{
		$user = factory(User::class)->create();

		Auth::login($user);

		$response = $this->json('POST' , '/oauth/clients', [
			'name' => 'MyClient',
			'redirect' => 'http://localhost'
		]);

		return $user;

	}

	/**
	 * Creates headers for passport token
	 * @param null $user
	 *
	 * @return array
	 */
	protected function headers($user = null)
	{
		$headers = ['Accept' => 'application/json'];

		if (!is_null($user)) {
			$token = $user->createToken('Token Name')->accessToken;
			$headers['Authorization'] = 'Bearer ' . $token;
		}

		return $headers;
	}



	public function test_it_stores_new_task()
	{
		$attributes = factory(Task::class)->make([
			'privacy' => false,
			'status' => true,
		])->toArray();

		$user = $this->createNewUserWithClientRecord();

		$headers = $this->headers($user);

		$response = $this->json('POST', 'api/tasks', $attributes, $headers);

		$response
			->assertJson(['success' => 'Task has been created!', 'id' => Task::first()->id]);
	}

	public function test_that_it_shows_single_task()
	{
		$task = factory(Task::class)->create();

		$user = $this->createNewUserWithClientRecord();

		$headers = $this->headers($user);

		$response = $this->json('GET', 'api/tasks/'.$task->id, [], $headers);

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

		$user = $this->createNewUserWithClientRecord();

		$headers = $this->headers($user);

		$response = $this->json('PUT', 'api/tasks/'.$task['id'], $task, $headers);

		$task = Task::find($task['id']);

		$task = Fractal::create()->item($task)->transformWith(TaskTransformer::class)->toArray();

		$response->assertJson($task);


	}


	public function test_it_deletes_task()
	{
		$task = factory(Task::class)->create();

		$user = $this->createNewUserWithClientRecord();

		$headers = $this->headers($user);

		$response = $this->json('DELETE', 'api/tasks/'.$task->id, [], $headers);


		$response->assertJson(['success' => 'The Task has been deleted!']);

	}

	public function test_guests_can_see_public_tasks()
	{
		$user = $this->createNewUserWithClientRecord();

		$task = factory(Task::class)->create(['user_id' => $user->id]);

		$task = Fractal::create()
		               ->item($task)
		               ->transformWith(TaskTransformer::class)->toJson();


		Auth::logout();


		$response = $this->json('GET', $user->id.'/tasks/');

		$this->assertJson($task, $response->json());
	}

}
