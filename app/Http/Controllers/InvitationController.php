<?php

namespace App\Http\Controllers;

use App\Invitation;
use App\Task;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
