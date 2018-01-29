<?php

namespace Tests\Feature\Controller;

use App\Invitation;
use App\Task;
use App\Transformers\InvitationTransformer;
use App\User;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvitationTest extends TestCase
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

	public function test_user_can_deny_an_invitation()
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

		$response = $this->json('GET', "api/deny/{$invitation->id}", [], $headers);

		$invitation = Invitation::find($invitation->id);

		$this->assertEquals('rejected', $invitation->status);
	}

	public function test_user_can_display_the_invitations_he_got()
	{
		$tasks = array();
		$users = array();
		$invitationsIds = array();
		$currentUser = $this->createNewUserWithClientRecord();

		for($i = 0; $i < 5; ++$i)
		{
			$tasks[] = factory(Task::class)->create(['privacy' => 1]);
			$users[] = factory(User::class)->create();

			$invitationsIds[] = factory(Invitation::class)->create([
				'invitor' => $users[$i]->id,
				'invitee' => $currentUser->id,
				'task_id' => $tasks[$i]-> id
			])->id;
		}

		$headers = $this->headers($currentUser);

		$response = $this->json('GET', 'api/my/invitations',[], $headers);

		$this->assertCount(5, $currentUser->invited);


		$invitations = Invitation::where('invitee', $currentUser->id)->paginate(25);

		$invitesCollection = $invitations->getCollection();

		// build the format
		$data =  fractal()
			->collection($invitesCollection)
			->parseIncludes(['group'])
			->transformWith(new InvitationTransformer())
			->paginateWith(new IlluminatePaginatorAdapter($invitations))->toJson();


		$this->assertJson($data, $response->json());

	}
}


