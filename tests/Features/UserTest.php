<?php

namespace Tests\Feature\Controller;

use App\Invitation;
use App\Task;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;


class UserTest extends TestCase
{

	use DatabaseTransactions;



	public function test_user_can_change_avatar()
	{

		$user = $this->createAndAuthenticateUser();


		$response = $this->post(route('user.avatar'), [
				'avatar' => UploadedFile::fake()->image('avatar.jpg'),

		]);


		$this->assertEquals(true, file_exists(storage_path('app/' . Auth::user()->avatar)));


		$firstFile = Auth::user()->avatar;

		$response = $this->post(route('user.avatar'), [
			'avatar' => UploadedFile::fake()->image('avatar.jpg'),

		]);


		$this->assertEquals(true, file_exists(storage_path('app/' . Auth::user()->avatar)));


		$this->assertEquals(false, file_exists(storage_path('app/' . $firstFile)));

	}



	public function test_user_can_update_info()
	{
		$user = $this->createAndAuthenticateUser([ 'password' => bcrypt('password')]);

		$newInfo = [
			'password' =>'newPassword',
			'password_confirmation' => 'newPassword',
			'current_password' => 'password',
			'info' => 'Hello this is my new info',
			'name' => 'Mohamed'
		];

		$response = $this->post(route('user.info'), $newInfo);


		$user = User::find(Auth::id());

		$this->assertEquals($newInfo['info'], $user->info);

		$this->assertEquals($newInfo['name'], $user->name);

		$this->assertEquals(true, password_verify('newPassword', $user->password));


	}


	public function test_user_can_invite_another_user_to_private_task()
	{
		$user = factory(User::class)->create();

		$secondUser = $this->createAndAuthenticateUser();

		$task = factory(Task::class)->create(['user_id' => $secondUser->id, 'privacy' => 1]);

		$response = $this->json('GET', "api/invite/{$user->id}/to/{$task->id}");

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
		$invitee = $this->createAndAuthenticateUser();


		$task = factory(Task::class)->create(['privacy' => 1]);

		$invitor = factory(User::class)->create();

		$invitation = \factory(Invitation::class)->create([
			'invitor' => $invitor->id,
			'invitee' => $invitee->id,
			'task_id' => $task->id
		]);

		$response = $this->json('GET', "api/accept/{$invitation->id}");
		$invitation = Invitation::find($invitation->id);

		$this->assertEquals('accepted', $invitation->status);
	}



	public function test_user_can_search_for_users_by_username(  )
	{
		$user1 = factory(User::class)->create(['username' => 'hellomohamed']);
		$user2 = factory(User::class)->create(['username' => 'mohamedhello']);
		$user3 = factory(User::class)->create(['username' => 'mohamed']);



		$currentLoggedInUser = $this->createAndAuthenticateUser();


		$response = $this->get(route('user.find',[
			'searchMethod' => 'username',
			'search' => 'mohamed'
		]));


		$this->assertCount(3, $response->json()['data']);


		$user3 = factory(User::class)->create(['username' => 'hello']);

		$response = $this->get(route('user.find', [
			'searchMethod' => 'username',
			'search' => 'mohamed'
		] ));

		$this->assertCount(3, $response->json()['data']);

	}

	public function test_user_can_search_for_users_by_email()
	{
		$user1 = factory(User::class)->create(['email' => 'hellomohamed@unpluggedweb.com']);
		$user2 = factory(User::class)->create(['email' => 'mohamedhello@unpluggedweb.com']);
		$user3 = factory(User::class)->create(['email' => 'mohamed@unpluggedweb.com']);



		$currentLoggedInUser = $this->createAndAuthenticateUser();


		$response = $this->get(route('user.find', [
			'searchMethod' => 'email',
			'search' => 'mohamed'
		]) );


		$this->assertCount(3, $response->json()['data']);


		$user3 = factory(User::class)->create(['email' => 'hello@unpluggedweb.com']);

		$response = $this->get(route('user.find', [
			'searchMethod' => 'email',
			'search' => 'mohamed'
		]) );

		$this->assertCount(3, $response->json()['data']);

	}


	public function test_user_can_search_for_users_by_name()
	{
		$user1 = factory(User::class)->create(['name' => 'hellomohamed@unpluggedweb.com']);
		$user2 = factory(User::class)->create(['name' => 'mohamedhello@unpluggedweb.com']);
		$user3 = factory(User::class)->create(['name' => 'mohamed@unpluggedweb.com']);



		$currentLoggedInUser = $this->createAndAuthenticateUser();


		$response = $this->get(route('user.find',  [
			'searchMethod' => 'name',
			'search' => 'mohamed'
		] ));


		$this->assertCount(3, $response->json()['data']);


		$user3 = factory(User::class)->create(['email' => 'hello']);

		$response = $this->get(route('user.find',[
			'searchMethod' => 'name',
			'search' => 'mohamed'
		] ));

		$this->assertCount(3, $response->json()['data']);

	}


	public function test_user_can_see_tasks_he_created_or_he_watches_on_his_news_feed_without_filters(  )
	{

		$currentUser = $this->createAndAuthenticateUser();


		$taskIOwn = factory(Task::class)->create(['user_id' => $currentUser->id]);

		$user = factory(User::class)->create();

		$taskIshouldBeWatching = factory(Task::class)->create(['user_id' => $user->id, 'privacy' => 0]);

		$response = $this->get(route('watch.task', ['task' => $taskIshouldBeWatching]));

		$taskThatINowWatch = $taskIshouldBeWatching;

		$response = $this->get(route('user.feed'));


		$this->assertCount(2, $response->json()['data']);

	}


	public function test_user_can_see_tasks_he_created_or_he_watches_on_his_news_feed_with_Username_filter()
	{

		$currentUser = $this->createAndAuthenticateUser();

		$taskIOwn = factory(Task::class)->create(['user_id' => $currentUser->id]);

		$user = factory(User::class)->create();

		$taskIShouldBeWatching = factory(Task::class)->create(['user_id' => $user->id, 'privacy' => 0]);

		$response = $this->get(route('watch.task', ['task' => $taskIShouldBeWatching->id]));

		$taskThatINowWatch = $taskIShouldBeWatching;

		$response = $this->get(route('user.feed',  [
			'filters' => [
				'username' => $user->username
			]
		]));

		$this->assertCount(1, $response->json()['data']);

	}

	public function test_user_can_see_tasks_he_created_or_he_watches_on_his_news_feed_with_Status_filter()
	{

		$currentUser = $this->createAndAuthenticateUser();


		$taskIOwn = factory(Task::class)->create(['user_id' => $currentUser->id]);

		$user = factory(User::class)->create();

		$taskIShouldBeWatching = factory(Task::class)->create(['user_id' => $user->id, 'privacy' => 0, 'status' => 1]);

		$response = $this->get(route('watch.task', ['task' => $taskIShouldBeWatching->id]));

		$taskThatINowWatch = $taskIShouldBeWatching;

		$response = $this->get(route('user.feed', [
			'filters' => [
				'status' => 1
			]
		]));

		$this->assertCount(1, $response->json()['data']);

	}


	public function test_user_can_see_tasks_he_created_or_he_watches_on_his_news_feed_with_Deadline_filter()
	{

		$currentUser = $this->createAndAuthenticateUser();

		$taskIOwn = factory(Task::class)->create(['user_id' => $currentUser->id, 'deadline' => '2018-8-15 00:00:00']);

		$user = factory(User::class)->create();

		$taskIShouldBeWatching = factory(Task::class)->create(['user_id' => $user->id, 'privacy' => 0, 'deadline' => '2019-2-1']);

		$response = $this->get(route('watch.task', ['task' => $taskIShouldBeWatching->id]));

		$taskThatINowWatch = $taskIShouldBeWatching;

		$response = $this->get(route('user.feed', [
			'filters' => [
				'deadline' => '2018-10-1'
			]
		]));

		$this->assertCount(1, $response->json()['data']);

	}
}

