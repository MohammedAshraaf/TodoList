<?php

namespace App\Http\Controllers;

use App\HelperClasses\FilesRemover;
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



}
