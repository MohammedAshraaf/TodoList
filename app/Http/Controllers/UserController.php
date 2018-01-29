<?php

namespace App\Http\Controllers;

use App\HelperClasses\FilesRemover;
use App\Http\Requests\UserProfileRequest;
use App\Invitation;
use App\Task;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{


	/**
	 * Changes user's avatar
	 *
	 * @param Request $request
	 *
	 * @param FilesRemover $filesRemover
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
    public function changeAvatar(Request $request, FilesRemover $filesRemover)
    {

    	// validate the avatar
	    $validatedData = $request->validate([
		    'avatar' => 'required|image|max:2000',
	    ]);


		$currentUser = Auth::user();

		// remove previous avatars
		$filesRemover->removeFilesFromDirectory(storage_path('app/users/' . $currentUser->id . '/*'));

		// save the apth of the file in avatar field
		$currentUser->avatar = $request->file('avatar')->store('users/' . $currentUser->id);

		$currentUser->save();

		return response()->json(['success' => 'Your avatar has been saved'], 200);
    }


	/**
	 * Allows user to change his info
	 * @param UserProfileRequest $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function changeInfo(UserProfileRequest $request)
	{
		/*if ($request->filled('current_password') && bcrypt($request->current_pasword)!= Auth::user()->password)
		{
			return response()->json(['error' => 'The current password doesn\'t match!']);
		}*/

		$currentUser = Auth::user();
		$currentUser->update($request->only(['password', 'info', 'name']));

		return response()->json(['success' => 'Your information has been updated'], 200);
    }



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

}
