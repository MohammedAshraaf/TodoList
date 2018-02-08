<?php

namespace Tests\Controller;

use App\Task;
use App\Transformers\TaskTransformer;
use App\User;
use App\Watch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Spatie\Fractalistic\Fractal;
use Tests\TestCase;


class ListTasksTest extends TestCase
{
	use DatabaseTransactions;


	public function test_guests_can_see_public_tasks_for_specific_user()
	{
		$user = factory(User::class)->create();

		for ($i = 0; $i < random_int(10, 25); $i++)
		{
			if($i % 2)
				$task = factory(Task::class)->states('private')->create(['user_id' => $user->id]);
			else
				$task = factory(Task::class)->create(['user_id' => $user->id]);
		}


		$response = $this->get(route('users.tasks.view', ['user' => $user->id]));

		$tasks = $user->tasks()->where('privacy', 0)->paginate(100);

		$taskCollection = $tasks->getCollection();

		$tasks = fractal()
			->collection($taskCollection)
			->parseIncludes(['group'])
			->transformWith(new TaskTransformer())
			->paginateWith(new IlluminatePaginatorAdapter($tasks))->toJson();

		$this->assertJson($tasks, $response->json());

		$response->assertStatus(200);
	}


	public function test_guests_can_see_public_tasks_for_specific_user_with_limits()
	{
		$user = factory(User::class)->create();

		for ($i = 0; $i < 50; $i++)
		{
			$task = factory(Task::class)->create(['user_id' => $user->id]);
		}


		$limit = random_int(20, 50);

		$response = $this->get(route('users.tasks.view', ['user' => $user->id, 'limit' => $limit]));

		$tasks = $user->tasks()->where('privacy', 0)->paginate($limit);

		$taskCollection = $tasks->getCollection();

		$tasks = fractal()
			->collection($taskCollection)
			->parseIncludes(['group'])
			->transformWith(new TaskTransformer())
			->paginateWith(new IlluminatePaginatorAdapter($tasks))->toArray();


		$this->assertEquals(count($tasks['data']), count($response->json()['data']));

		$this->assertJson(json_encode($tasks), $response->json());

		$response->assertStatus(200);
	}


	public function test_user_can_list_his_own_tasks()
	{
		$user = $this->createAndAuthenticateUser();

		for ($i = 0; $i < 50; $i++)
		{
			$task = factory(Task::class)->create(['user_id' => $user->id]);
		}

		$limit = random_int(1, 49);
		$response = $this->get(route('tasks.index', ['limit' => $limit]));

		$this->assertCount($limit, $response->json()['data']);


	}
}

