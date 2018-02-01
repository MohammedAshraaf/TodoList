<?php


namespace App\Events;


use App\Notifications\WatchedNotification;
use App\Task;
use App\User;
use App\Watch;

class WatchSaver
{
	public function saved(Watch $watch)
	{
		$task = Task::find($watch->task_id);

		if(!$task)
			return;

		$taskWatcher = User::find($watch->user_id);

		if(!$taskWatcher)
			return;


		$taskOwner = User::find($task->user_id);

		$taskOwner->notify(new WatchedNotification(['watcher_username' => $taskWatcher->username, 'task_name' => $task->name]));
	}
}

