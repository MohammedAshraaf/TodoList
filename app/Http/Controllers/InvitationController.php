<?php

namespace App\Http\Controllers;

use App\Invitation;
use App\Task;
use App\Transformers\InvitationTransformer;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class InvitationController extends Controller
{
	/**
	 * invites user to watch a private task
	 * @param User $user
	 * @param Task $task
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function inviteToWatchPrivateTask(User $user, Task $task)
	{

		if (!Auth::user()->tasks->contains($task))
		{
			return response(['error' => 'unauthorized to perform this action'], 401);
		}

		if (!$task->privacy)
		{
			return response(['error' => 'This task is public!'], 200);
		}


		$invitation = Invitation::create([
			'invitor' => Auth::id(),
			'invitee' => $user->id,
			'task_id' => $task->id,
			'status' => 'pending'
		]);


		return response()->json(['success' => 'Your invitation has been sent', 'data' => [
			'invitation_id' => $invitation->id,
			'status' => $invitation->status
		]], 200);
	}


	/**
	 *
	 * Accepts an invitation
	 * @param Invitation $invitation
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function acceptInvitation(Invitation $invitation)
	{
		if($invitation->status != 'pending' || $invitation->invitee != Auth::id())
		{
			return response()->json(['error' => 'You have responded to this invitation before']);
		}

		$invitation->status = 'accepted';

		$invitation->save();

		return response()->json(['success' => 'Invitation Accepted'], 200);
	}

	/**
	 * Accepts an invitation
	 * @param Invitation $invitation
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function denyInvitation(Invitation $invitation)
	{
		if($invitation->status != 'pending' || $invitation->invitee != Auth::id())
		{
			return response()->json(['error' => 'You have responded to this invitation before!']);
		}

		$invitation->status = 'rejected';

		$invitation->save();

		return response()->json(['success' => 'Invitation Rejected'], 200);
	}


	/**
	 * Displays users invitations he has been invited within
	 * @return mixed
	 */
	public function myInvitations()
	{
		$limit = Input::get('limit', 25);

		// user can't request more that 500 records
		$limit = min($limit, 500);

		// get the current user's invitations
		$invitations = Invitation::where('invitee', Auth::id())->paginate($limit);

		$invitesCollection = $invitations->getCollection();

		// build the format
		return fractal()
			->collection($invitesCollection)
			->parseIncludes(['group'])
			->transformWith(new InvitationTransformer())
			->paginateWith(new IlluminatePaginatorAdapter($invitations))
			->toArray();
	}
}
