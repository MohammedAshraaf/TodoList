<?php

namespace App\Console\Commands;

use App\Notifications\ReminderNotification;
use App\Task;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends reminder for tasks which deadline became close!';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
	    Task::with('user')
	                      ->where('notified', 0)
	                      ->chunk(100, function($tasks){
	                      	    foreach ($tasks as $task)
	                            {
	                            	$timeBetweenTaskAndDeadline = strtotime($task->deadline) - strtotime($task->updated_at);
	                            	$timePassed = (strtotime('now') - strtotime($task->updated_at));

	                            	if( $timePassed / $timeBetweenTaskAndDeadline < 0.8)
		                            {
		                            	continue;
		                            }

									$task['user']->notify(new ReminderNotification([
										'task_name' => $task->name,
										'task_deadline' => $task->deadline
									]));
	                            	$task->notified = 1;
	                            	$task->save();
	                            }
	                      });
    }
}
