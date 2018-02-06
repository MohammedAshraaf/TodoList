<?php

namespace Tests\Feature\Controller;

use App\Invitation;
use App\Task;
use App\Transformers\InvitationTransformer;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Tests\TestCase;


class InvitationTest extends TestCase
{

	use DatabaseTransactions;

	public function test_user_can_invite_another_user_to_private_task()
	{
		$user = factory(User::class)->create();

		$authenticatedUser = $this->createAndAuthenticateUser();

		$task = factory(Task::class)->create(['user_id' => $authenticatedUser->id, 'privacy' => 1]);

		$response = $this->get(route('invite.users', ['user' => $user->id, 'task' => $task->id]));

		$invitation = Invitation::first();

		$this->assertEquals($invitation->invitor, $authenticatedUser->id);
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

		$response = $this->get(route('invite.accept', ['invitation' => $invitation->id]));

		$invitation = Invitation::find($invitation->id);

		$this->assertEquals('accepted', $invitation->status);
	}


	public function test_user_can_deny_an_invitation()
	{
		$invitee = $this->createAndAuthenticateUser();

		$task = factory(Task::class)->create(['privacy' => 1]);

		$invitor = factory(User::class)->create();

		$invitation = \factory(Invitation::class)->create([
			'invitor' => $invitor->id,
			'invitee' => $invitee->id,
			'task_id' => $task->id
		]);

		$response = $this->get(route('invite.deny', ['invitation' => $invitation->id]));

		$invitation = Invitation::find($invitation->id);

		$this->assertEquals('rejected', $invitation->status);
	}


	public function test_user_can_display_the_invitations_he_got()
	{
		$tasks = array();
		$users = array();
		$invitationsIds = array();
		$currentUser = $this->createAndAuthenticateUser();

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


		$response = $this->get(route('invite.list'));



		$invitations = Invitation::where('invitee', $currentUser->id)->paginate(25);


		$invitesCollection = $invitations->getCollection();

		// build the format
		$data =  fractal()
			->collection($invitesCollection)
			->parseIncludes(['group'])
			->transformWith(new InvitationTransformer())
			->paginateWith(new IlluminatePaginatorAdapter($invitations))->toJson();


		$this->assertJson($data, $response->json());

		$this->assertCount(5, $invitations);


	}
}


