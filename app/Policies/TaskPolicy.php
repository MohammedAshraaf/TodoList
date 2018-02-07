<?php

namespace App\Policies;

use App\User;
use App\Task;
use App\Watch;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the task.
     *
     * @param  \App\User  $user
     * @param  \App\Task  $task
     * @return mixed
     */
    public function view(User $user, Task $task)
    {
	    if($user->id === $task->user_id || !$task->privacy)
	    {
		    return true;
	    }
	    $userWatchTask = Watch::where(['user_id' => $user->id, 'task_id' => $task->id])->first();

	    if($userWatchTask)
	    {
	    	return true;
	    }

	    return false;

    }

    /**
     * Determine whether the user can create tasks.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the task.
     *
     * @param  \App\User  $user
     * @param  \App\Task  $task
     * @return mixed
     */
    public function update(User $user, Task $task)
    {
        return $user->id === $task->user_id;
    }

    /**
     * Determine whether the user can delete the task.
     *
     * @param  \App\User  $user
     * @param  \App\Task  $task
     * @return mixed
     */
    public function delete(User $user, Task $task)
    {
	    return $user->id === $task->user_id;
    }
}
