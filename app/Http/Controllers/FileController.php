<?php

namespace App\Http\Controllers;

use App\File;
use App\Http\Requests\FileRequest;
use App\Task;

class FileController extends Controller
{

	/**
	 * uploads files and attaches them to a specific task
	 * @param Task $task
	 * @param FileRequest $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function uploads(Task $task, FileRequest $request)
	{
		// for more than one file
		foreach ($request->all()['files'] as $file)
		{
			// store the file in task's folder
			$filename = $file->store('tasks/' . $task->id);

			// create new file record in the DB
			File::create(['path' => $filename, 'task_id' => $task->id]);
		}

		// Done
		return response()->json(['Uploaded Files' => true], 200);
    }


	/**
	 * Deletes file and detach it from the task
	 * @param Task $task
	 * @param File $file
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
    public function detach(Task $task, File $file)
    {
    	// the file doesn't relate to the task
    	if(! $task->files->contains($file))
	    {
	    	return response()->json(['error' => 'The file specified doesn\'t belong to the this task'],403);
	    }

	    // delete the file from the DB
		$task->files()->delete($file);

    	// delete the file from the storage
	    unlink(storage_path('app/' . $file->path));


	    // Done!
	    return response()->json([
	    	'deleted' => true
	    ], 200);
    }
}

