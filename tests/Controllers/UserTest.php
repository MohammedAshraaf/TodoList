<?php

namespace Tests\Feature\Controller;

use App\Invitation;
use App\Task;
use App\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
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


	public function test_user_can_change_avatar()
	{

		$user = $this->createNewUserWithClientRecord();

		$headers = $this->headers($user);

		$response = $this->json('POST', 'api/my/avatar', [
				'avatar' => UploadedFile::fake()->image('avatar.jpg'),

		], $headers);


		$this->assertEquals(true, file_exists(storage_path('app/' . Auth::user()->avatar)));


		$firstFile = Auth::user()->avatar;

		$response = $this->json('POST', 'api/my/avatar', [
			'avatar' => UploadedFile::fake()->image('avatar.jpg'),

		], $headers);


		$this->assertEquals(true, file_exists(storage_path('app/' . Auth::user()->avatar)));


		$this->assertEquals(false, file_exists(storage_path('app/' . $firstFile)));

	}



	public function test_user_can_update_info()
	{
		$user = $this->createNewUserWithClientRecord(['password' => bcrypt('password')]);

		$headers = $this->headers($user);

		$newInfo = [
			/*'password' =>'newPassword',
			'password_confirmation' => 'newPassword',
			'current_password' => 'password',*/
			'info' => 'Hello this is my new info',
			'name' => 'Mohamed'
		];

		$response = $this->json('POST', 'api/my/info', $newInfo, $headers);


		$user = User::find(Auth::id());

		$this->assertEquals($newInfo['info'], $user->info);

		$this->assertEquals($newInfo['name'], $user->name);


	}


	public function test_user_can_invite_another_user_to_private_task()
	{
		$user = $this->createNewUserWithClientRecord();

		Auth::logout($user);

		$secondUser = $this->createNewUserWithClientRecord();

		$task = factory(Task::class)->create(['user_id' => $secondUser->id, 'privacy' => 1]);
		$headers = $this->headers($secondUser);

		$response = $this->json('GET', "api/invite/{$user->id}/to/{$task->id}", [], $headers);

		$invitation = Invitation::first();

		$this->assertEquals($invitation->invitor, $secondUser->id);
		$this->assertEquals($invitation->invitee, $user->id);
		$this->assertEquals($invitation->task_id, $task->id);
		$response->assertJson([
			'data' => [
				'invitation_id' => $invitation->id,
				'status' => $invitation->status
			]
		]);
	}

	public function test_user_can_accept_invitation_to_watch_task()
	{
		$invitee = $this->createNewUserWithClientRecord();

		$headers = $this->headers($invitee);

		$task = factory(Task::class)->create(['privacy' => 1]);

		$invitor = factory(User::class)->create();

		$invitation = \factory(Invitation::class)->create([
			'invitor' => $invitor->id,
			'invitee' => $invitee->id,
			'task_id' => $task->id
		]);

		$response = $this->json('GET', "api/accept/{$invitation->id}", [], $headers);
		$invitation = Invitation::find($invitation->id);

		$this->assertEquals('accepted', $invitation->status);
	}
}


