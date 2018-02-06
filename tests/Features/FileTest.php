<?php

namespace Tests\Controller;

use App\File;
use App\Task;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;


class FileTest extends TestCase
{
	use DatabaseTransactions;


	/** @test */
	function test_uploading_files_to_task()
	{
		$this->withoutExceptionHandling();

		$task = factory(Task::class)->create();

		$user = $this->createAndAuthenticateUser();

		$files =   [
			'files' =>[
				UploadedFile::fake()->image('file1.jpg'),

			]
		];

		$response = $this->hitUploadFilesEndpoint($task, $files);

		$response->assertStatus(200);

		foreach (File::all() as $file)
		{
			$this->assertEquals(true, file_exists(storage_path('app/'. $file->path)));
		}

	}

	public function test_it_detach_files_from_specific_task(  )
	{
		// assuming we have task
		$task = factory(Task::class)->create();


		$user = $this->createAndAuthenticateUser();


		// and we attached file to it
		$response = $this->post('api/tasks/'.$task->id.'/files', [
			'files' =>[
				UploadedFile::fake()->image('file1.jpg'),

			]]);

		$file = $task->files()->first();

		// when removing the file
		$response = $this->delete(route('deleteFile', ['task' => $task->id, 'file' => $file->id]));



		// then we don't see the file
		$this->assertEquals(false, file_exists(storage_path('app/' . $file->path)));

		$this->assertEquals(null, File::find($file->id));
	}

	private function hitUploadFilesEndpoint(Task $task, array $files ) {
		return $this->post(route('uploadFile', ['task' => $task->id]),$files);
	}


}
