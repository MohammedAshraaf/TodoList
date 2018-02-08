<?php

namespace App\Http\Controllers;

use App\HelperClasses\FilesRemover;
use App\HelperClasses\TaskFilters;
use App\Http\Requests\UserProfileRequest;
use App\Invitation;
use App\Task;
use App\Transformers\TaskTransformer;
use App\Transformers\UserTransformer;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Mockery\Exception;

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
		$currentUser = Auth::user();

		// when the user wants to update the password, we validates the current password first
		if($request->filled('password') && !password_verify($request->current_password, $currentUser->password))
		{
			return response()->json(['error' => 'The current password is invalid!'], 401);
		}

		// update the plain password to encrypted one
		$request->merge(['password'=> bcrypt($request->password)]);

		$currentUser->update($request->only(['password', 'info', 'name']));

		return response()->json(['success' => 'Your information has been updated'], 200);
    }


	public function search(Request $request)
	{


		// user can't request more that 500 records
		$limit = min(($request->limit ?? 15), 500);

		// get the current user's tasks
		$users = User::where($request->searchMethod, 'LIKE', "%{$request->search}%")->paginate($limit);

		$userCollection = $users->getCollection();

		// build the format
		return fractal()
			->collection($userCollection)
			->parseIncludes(['group'])
			->transformWith(new UserTransformer())
			->paginateWith(new IlluminatePaginatorAdapter($users))
			->toArray();

    }


	/**
	 * Shows tasks in newsFeeds
	 * @param Request $request
	 *
	 * @return mixed
	 */
	public function newsFeed(TaskFilters $filters)
	{

		$limits = $request->limit ?? 15;

		$user = Auth::user();


		$totalTasks = Task::filter($filters)->get();

		return fractal()
			->collection($totalTasks)
			->parseIncludes(['group'])
			->transformWith(new TaskTransformer())
			->paginateWith(new IlluminatePaginatorAdapter($this->paginate($limits)))
			->toArray();
    }

	/**
	 * Paginates collections , mainly for different models
	 * @param $items
	 * @param int $perPage
	 * @param null $page
	 * @param array $options
	 *
	 * @return LengthAwarePaginator
	 */
	public function paginate($items, $perPage = 15, $page = null, $options = [])
	{
		$page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
		$items = $items instanceof Collection ? $items : Collection::make($items);
		return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
	}

}
