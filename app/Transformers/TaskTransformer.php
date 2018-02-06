<?php

namespace App\Transformers;

use App\Task;
use App\User;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class TaskTransformer extends TransformerAbstract
{
	/**
	 * A Fractal transformer.
	 *
	 * @param Task $task
	 *
	 * @return array
	 */
    public function transform(Task $task)
    {
        return [
            'id' => $task->id,
	        'description' => $task->description,
	        'privacy' => $task->privacy ? 'Private' : 'Public',
	        'status' => $task->status ? 'Done' : 'Todo',
	        'deadline' => Carbon::parse( $task->deadline)->format('Y-m-d H'),
	        'owner' => User::find($task->user_id)->username
        ];
    }
}
