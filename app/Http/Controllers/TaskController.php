<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Task;
use App\Transformers\TaskTransformer;
use App\User;
use App\Watch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Spatie\Fractalistic\Fractal;

class TaskController extends Controller
{

	/**
	 * indexing the tasks
	 * @return mixed
	 */
	public function index()
	{
		$limit = Input::get('limit', 2);

		// user can't request more that 500 records
		$limit = min($limit, 500);

		// get the current user's tasks
		$tasks = Auth::user()->tasks()->paginate($limit);

		$taskCollection = $tasks->getCollection();

		// build the format
		return fractal()
			->collection($taskCollection)
			->parseIncludes(['group'])
			->transformWith(new TaskTransformer())
			->paginateWith(new IlluminatePaginatorAdapter($tasks))
			->toArray();
	}

	/**
	 * Stores a new task
	 *
	 * @param TaskRequest $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
    public function store(TaskRequest $request)
    {

    	// store the tasks and attach it with the current user
    	$task = Auth::user()->tasks()->create($request->only([
    		'name',
		    'description',
		    'privacy',
		    'status',
		    'deadline',
	    ]));

	    $task = Fractal::create()
	                   ->item(Task::first())
	                   ->transformWith(TaskTransformer::class)->toArray();

    	return response()->json(['success' => 'Task has been created!', $task],200);
    }


	/**
	 * Shows specific task
	 *
	 * @param Task $task
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
    public function show(Task $task)
    {
	    if(!Auth::user()->tasks->contains($task) && $task->privacy
	       && is_null(Watch::where(['user_id' => Auth::id(), 'task_id' => $task->id])->first()))
	    {
		    return response()->json(['error' => 'unauthorized to perform this action'], 401);
	    }

    	return response()->json(Fractal::create()
	                                   ->item($task)
	                                   ->transformWith(TaskTransformer::class), 200);
    }


	/**
	 * Updates a specific task
	 *
	 * @param Task $task
	 * @param TaskRequest $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
    public function update(Task $task, TaskRequest $request)
    {

    	if (!Auth::user()->tasks->contains($task))
	    {
	    	return response(['error' => 'unauthorized to perform this action'], 403);
	    }


		$task->update($request->only([
			'name',
			'description',
			'privacy',
			'status',
			'deadline',
		]));



		return response()->json(Fractal::create()
		                               ->item($task)
		                               ->transformWith(TaskTransformer::class), 200);
    }


	/**
	 * Deletes a task
	 *
	 * @param Task $task
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Exception
	 */
    public function destroy(Task $task)
    {
    	if(!Auth::user()->tasks->contains($task))
	    {
	    	return response()->json(['error' => 'unauthorized to perform this action'], 401);
	    }
	    $task->delete();

    	return response()->json(['success' => 'The Task has been deleted!'], 200);
    }


	/**
	 * Allow Guests to view users' tasks
	 * @param User $user
	 *
	 * @return mixed
	 */
    public function viewOthersTasks(User $user)
    {
	    $limit = Input::get('limit', 2);

	    // user can't request more that 500 records
	    $limit = min($limit, 500);

	    $tasks = $user->tasks()->where('privacy', 0)->paginate(2);

	    $taskCollection = $tasks->getCollection();

	    return fractal()
		    ->collection($taskCollection)
		    ->parseIncludes(['group'])
		    ->transformWith(new TaskTransformer())
		    ->paginateWith(new IlluminatePaginatorAdapter($tasks))
		    ->toArray();
    }


	/**
	 * shows single task to the guests
	 * @param User $user
	 * @param Task $task
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
	 */
    public function viewOthersTask(User $user, Task $task)
    {
    	// return the task unless it's a private one!
	    return $user->tasks->contains($task) && !$task->privacy ?
		    response()
			    ->json(Fractal::create()
			                  ->item($task)
			                  ->transformWith(TaskTransformer::class), 200) :
		    response(['error' => 'unauthorized to perform this action'], 403);

    }
}
