<?php

namespace Tests\Models;

use App\Task;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TaskTest extends TestCase
{
	use DatabaseTransactions;

	/** @test */
	public function test_task_can_be_created_with_default_values()
	{
		$attributes = factory(Task::class)->make()->toArray();

		$task = new Task;
		$task->create($attributes);

		$task = Task::first();

		$this->assertEquals($attributes['description'], $task->description);
		$this->assertEquals($attributes['deadline'], $task->deadline);
	}

	/** @test */
	public function test_task_can_be_created_without_file()
	{
		$attributes = factory(Task::class)->make([
			'private' => false,
			'status' => true,
		])->toArray();

		$task = new Task;

		$task->create($attributes);

		$task = Task::first();

		$this->assertEquals($attributes['description'], $task->description);
		$this->assertEquals($attributes['deadline'], $task->deadline);
		$this->assertEquals($attributes['private'], $task->private);
		$this->assertEquals($attributes['status'], $task->status);
	}


}
