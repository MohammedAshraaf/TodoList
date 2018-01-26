<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{


    public function changeAvatar(Request $request)
    {

	    $validatedData = $request->validate([
		    'avatar' => 'required|image|max:2000',
	    ]);

		$currentUser = Auth::user();

		$currentUser->avatar = $request->file('avatar')->store('users/' . $currentUser->id);

		$currentUser->save();

		return response()->json(['success' => 'Your avatar has been saved'], 200);
    }
}
