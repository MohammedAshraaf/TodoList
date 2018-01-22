<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Task;
use App\Transformers\TaskTransformer;
use Illuminate\Http\Request;
use Spatie\Fractalistic\Fractal;

class TaskController extends Controller
{

	/**
	 * Stores a new task
	 *
	 * @param TaskRequest $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
    public function store(TaskRequest $request)
    {

    	$task = Task::create($request->only([
    		'name',
		    'description',
		    'user_id',
		    'privacy',
		    'status',
		    'deadline',
	    ]));

    	if(!$task)
	    {
	    	return response()->json(['error' => "Couldn't Store the Task"], 200);
	    }



    	return response()->json(['created' => true],200);
    }


	/**
	 * Shows specific task
	 * @param Task $task
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
    public function show(Task $task)
    {

    	return response()->json(Fractal::create()
	                                   ->item($task)
	                                   ->transformWith(TaskTransformer::class), 200);
    }


    public function update(Task $task, TaskRequest $request)
    {
		$task->update($request->only([
			'description',
			'user_id',
			'privacy',
			'status',
			'deadline',
			'file'
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
	    $task->delete();

    	return response()->json(['deleted' => true], 200);
    }
}
