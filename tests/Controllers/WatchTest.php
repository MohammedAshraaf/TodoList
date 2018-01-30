<?php

namespace Tests\Feature\Controller;

use App\Invitation;
use App\Task;
use App\Transformers\InvitationTransformer;
use App\User;
use App\Watch;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WatchTest extends TestCase
{

	use DatabaseTransactions;

	/**
	 * Creates new user with client id to authenticate through passport
	 *
	 * @param array $attributes
	 *
	 * @return mixed
	 */
	public function createNewUserWithClientRecord($attributes = [])
	{
		$user = factory(User::class)->create($attributes);

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



	public function test_user_can_watch_public_task()
	{
		$userWhoOwnsTheTask = factory(User::class)->create();

		$currentLoggedInUser = $this->createNewUserWithClientRecord();

		$headers = $this->headers($currentLoggedInUser);

		$publicTask = factory(Task::class)->create(['privacy' => 0, 'user_id' => $userWhoOwnsTheTask->id]);

		$response = $this->json('GET', "api/watch/{$publicTask->id}", [], $headers);

		$watcher = Watch::where(['user_id' => $currentLoggedInUser->id, 'task_id' => $publicTask->id])->first();

		$this->assertEquals(false, is_null($watcher));

		$this->assertCount(1, $currentLoggedInUser->tasksHeWatches);

		$privateTask = factory(Task::class)->create(['privacy' => 1, 'user_id' => $userWhoOwnsTheTask->id]);


		$response = $this->json('GET', "api/watch/{$privateTask->id}", [], $headers);

		$watcher = Watch::where(['user_id' => $currentLoggedInUser->id, 'task_id' => $privateTask->id])->first();

		$this->assertEquals(true, is_null($watcher));

		$this->assertEquals(401, $response->status());



	}

	public function test_user_can_watch_private_task_he_got_invited_to()
	{
		$invitedUser = $this->createNewUserWithClientRecord();

		$headers = $this->headers($invitedUser);

		$userWhoOwnsTheTask = factory(User::class)->create();

		$privateTask = factory(Task::class)->create(['privacy' => 1, 'user_id' => $userWhoOwnsTheTask->id]);


		$invitation = Invitation::create([
			'invitee' => $invitedUser->id,
			'invitor' => $userWhoOwnsTheTask->id,
			'task_id' => $privateTask->id,
			'status' => 'accepted'
		]);


		$response = $this->json('GET', "api/watch/{$privateTask->id}", [], $headers);

		$watcher = Watch::where(['user_id' => $invitedUser->id, 'task_id' => $privateTask->id])->first();

		$this->assertEquals(false, is_null($watcher));

		$this->assertCount(1, $invitedUser->tasksHeWatches);
	}
}


