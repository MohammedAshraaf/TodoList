<?php

namespace App\Http\Controllers;

use App\Invitation;
use App\Task;
use App\Watch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchController extends Controller
{

	public function watch(Task $task)
	{
		$currentUserInvitedToTheTask = Invitation::where([
			'task_id' => $task->id,
			'invitee' => Auth::id(),
			'status' => 'accepted'
		])->first();

		if($task->privacy && is_null($currentUserInvitedToTheTask))
		{
			return response()->json(['error' => 'You are not Authorized!'], 401);
		}

		try
		{
			Watch::create(['user_id' => Auth::id(), 'task_id' => $task->id]);
		}
		catch (\Exception $exception)
		{
			return response()->json(['error' => 'You are watching this task already!']);
		}

		return response()->json(['success' => 'You are watching the task now!'], 200);
	}
}
