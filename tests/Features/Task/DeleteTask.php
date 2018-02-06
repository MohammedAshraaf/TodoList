<?php

namespace Tests\Controller;

use App\Task;
use App\Transformers\TaskTransformer;
use App\User;
use App\Watch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Fractalistic\Fractal;
use Tests\TestCase;


class DeleteTask extends TestCase
{
	use DatabaseTransactions;



	public function test_user_can_delete_task_he_owns()
	{

		$user = $this->createAndAuthenticateUser();

		$task = factory(Task::class)->create(['user_id' => $user->id]);

		$response = $this->delete(route('tasks.destroy', ['task' => $task->id]));

		$response->assertStatus(200);

		$response->assertJson(['success' => 'The Task has been deleted!']);

		$this->assertEquals(null, Task::find($task->id));

	}

	public function test_user_can_not_delete_task_he_does_not_own()
	{
		$user = $this->createAndAuthenticateUser();

		$task = factory(Task::class)->create();

		$response = $this->delete(route('tasks.destroy', ['task' => $task->id]));

		$response->assertStatus(403);

		$response->assertJson(['error' => 'unauthorized to perform this action']);

		$this->assertEquals($task->id, Task::find($task->id)->id);
	}


}
