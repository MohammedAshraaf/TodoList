<?php

namespace Tests\Feature\Controller;

use App\Invitation;
use App\Task;
use App\User;
use App\Watch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;


class WatchTest extends TestCase
{

	use DatabaseTransactions;


	public function test_user_can_watch_public_task()
	{
		$userWhoOwnsTheTask = factory(User::class)->create();

		$currentLoggedInUser = $this->createAndAuthenticateUser();


		$publicTask = factory(Task::class)->create(['privacy' => 0, 'user_id' => $userWhoOwnsTheTask->id]);

		$response = $this->get(route('watch.task', ['task' => $publicTask->id]));

		$watcher = Watch::where(['user_id' => $currentLoggedInUser->id, 'task_id' => $publicTask->id])->first();

		$this->assertEquals(false, is_null($watcher));

		$this->assertCount(1, $currentLoggedInUser->tasksHeWatches);

		$privateTask = factory(Task::class)->create(['privacy' => 1, 'user_id' => $userWhoOwnsTheTask->id]);


		$response = $this->get(route('watch.task', ['task' => $privateTask->id]));

		$watcher = Watch::where(['user_id' => $currentLoggedInUser->id, 'task_id' => $privateTask->id])->first();

		$this->assertEquals(true, is_null($watcher));

		$this->assertEquals(401, $response->status());



	}

	public function test_user_can_watch_private_task_he_got_invited_to()
	{
		$invitedUser = $this->createAndAuthenticateUser();


		$userWhoOwnsTheTask = factory(User::class)->create();

		$privateTask = factory(Task::class)->create(['privacy' => 1, 'user_id' => $userWhoOwnsTheTask->id]);


		$invitation = Invitation::create([
			'invitee' => $invitedUser->id,
			'invitor' => $userWhoOwnsTheTask->id,
			'task_id' => $privateTask->id,
			'status' => 'accepted'
		]);


		$response = $this->get(route('watch.task', ['task' => $privateTask->id]));

		$watcher = Watch::where(['user_id' => $invitedUser->id, 'task_id' => $privateTask->id])->first();

		$this->assertEquals(false, is_null($watcher));

		$this->assertCount(1, $invitedUser->tasksHeWatches);
	}
}


