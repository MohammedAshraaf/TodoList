<?php

namespace Tests\Controller;

use App\Notification;
use App\Task;
use App\User;
use Carbon\Carbon;
use function GuzzleHttp\Psr7\str;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Mockery\Matcher\Not;
use Tests\TestCase;


class NotificationTest extends TestCase
{
	use DatabaseTransactions;


	public function test_user_gets_notification_when_someone_watches_his_task()
	{
		$userWhoOwnsTheTask = factory(User::class)->create();

		$currentLoggedInUser = $this->createAndAuthenticateUser();

		$publicTask = factory(Task::class)->create(['privacy' => 0, 'user_id' => $userWhoOwnsTheTask->id]);

		$response = $this->get( "api/watch/{$publicTask->id}");

		$notification = Notification::where('notifiable_id', $userWhoOwnsTheTask->id)
		                            ->where('notifiable_type', 'App\User')
		                            ->first();

		$this->assertEquals(false, is_null($notification));

	}



	public function test_user_gets_notification_when_task_passes_80_percent_of_its_time()
	{
		$deadlineAfterTenMinutes = strtotime('now') + 10 * 60;

		$updatedAtBeforeHour = strtotime('now') -  60 * 60;


		$secondUpdatedAtBeforeOneMinutes = strtotime('now') - 3 * 60;



		$taskForFirstUser = factory(Task::class)->create([
			'deadline' => Carbon::createFromTimestamp($deadlineAfterTenMinutes)->toDateTimeString(),
			'updated_at' => Carbon::createFromTimestamp($updatedAtBeforeHour)->toDateTimeString()
		]);



		$taskForSecondUser = factory(Task::class)->create([
			'deadline' => Carbon::createFromTimestamp($deadlineAfterTenMinutes)->toDateTimeString(),
			'updated_at' => Carbon::createFromTimestamp($secondUpdatedAtBeforeOneMinutes)->toDateTimeString()
		]);

		Artisan::call('task:reminder');

		$this->assertCount(1, Notification::all());
	}

}
