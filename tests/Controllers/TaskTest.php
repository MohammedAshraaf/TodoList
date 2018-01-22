<?php

namespace Tests\Controller;

use App\Task;
use App\Transformers\TaskTransformer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

		$response = $this->json('POST', 'api/tasks', $attributes);

		$response
			->assertJson([
				'created' => true,
			]);
	}

	public function test_that_it_shows_single_task()
	{
		$task = factory(Task::class)->create();

		$response = $this->json('GET', 'api/tasks/'.$task->id);

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


		$response = $this->json('PUT', 'api/tasks/'.$task['id'], $task);

		$task = Task::find($task['id']);

		$task = Fractal::create()->item($task)->transformWith(TaskTransformer::class)->toArray();

		$response->assertJson($task);


	}

	public function test_it_deletes_task()
	{
		$task = factory(Task::class)->create();

		$response = $this->json('DELETE', 'api/tasks/'.$task->id);


		$response->assertJson([
			'deleted' => true,
		]);

	}

}
