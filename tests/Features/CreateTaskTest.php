<?php

namespace Tests\Controller;

use App\Task;
use App\Transformers\TaskTransformer;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Fractalistic\Fractal;
use Tests\TestCase;


class CreateTaskTest extends TestCase
{
	use DatabaseTransactions;


	public function test_logged_in_user_can_store_task_for_himself()
	{
		$attributes = factory(Task::class)->make([
			'privacy' => false,
			'status' => false,
		])->toArray();

		$user = $this->createAndAuthenticateUser();

		$response = $this->post( route('tasks.store'), $attributes);

		$task = Fractal::create()
		               ->item(Task::first())
		               ->transformWith(TaskTransformer::class)->toArray();

		$response->assertJson([
			'success' => 'Task has been created!',
			$task
		]);

		$response->assertStatus(200);
	}

	public function test_logged_in_user_can_not_create_task_for_other_user(  )
	{
		$someUser = factory(User::class)->create();

		$attributes = factory(Task::class)->make([
			'privacy' => false,
			'status' => true,
			'user_id' => $someUser->id
		])->toArray();

		$currentUser = $this->createAndAuthenticateUser();

		$response = $this->post( route('tasks.store'), $attributes);

		$task = Fractal::create()
		               ->item(Task::first())
		               ->transformWith(TaskTransformer::class)->toArray();

		$response->assertJson([
			'success' => 'Task has been created!',
			$task
		]);

		$response->assertStatus(200);

		$this->assertEquals($currentUser->username, $task['data']['owner']);
	}

	public function test_guest_can_not_create_task_at_all( )
	{
		$attributes = factory(Task::class)->make([
			'privacy' => false,
			'status' => true,
		])->toArray();

		$response = $this->post( route('tasks.store'), $attributes);

		$this->assertCount(0, Task::all());

		$response->assertStatus(302);
	}

}
