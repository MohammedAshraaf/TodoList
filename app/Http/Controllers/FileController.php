<?php

namespace App\Http\Controllers;

use App\File;
use App\Http\Requests\FileRequest;
use App\Task;
use Illuminate\Http\Request;

class FileController extends Controller
{
	public function uploads(Task $task, FileRequest $request)
	{
		foreach ($request->files as $file)
		{
			$filename = $file->store('tasks/' . $task->id);
			File::create(['path' => $filename, 'task_id' => $task->id]);
		}
    }
}
