<?php

namespace Tests\Controller;

use App\Notification;
use App\Task;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Mockery\Matcher\Not;
use Tests\TestCase;


class NotificationTest extends TestCase
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


	public function test_user_gets_notification_when_someone_watches_his_task()
	{
		$userWhoOwnsTheTask = factory(User::class)->create();

		$currentLoggedInUser = $this->createNewUserWithClientRecord();

		$headers = $this->headers($currentLoggedInUser);

		$publicTask = factory(Task::class)->create(['privacy' => 0, 'user_id' => $userWhoOwnsTheTask->id]);

		$response = $this->json('GET', "api/watch/{$publicTask->id}", [], $headers);

		$notification = Notification::where('notifiable_id', $userWhoOwnsTheTask->id)
		                            ->where('notifiable_type', 'App\User')
		                            ->first();

		$this->assertEquals(false, is_null($notification));

	}

}
