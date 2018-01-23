<?php

namespace Tests\Controller;

use App\File;
use App\Task;
use App\Transformers\TaskTransformer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Fractalistic\Fractal;
use Tests\TestCase;


class FileTest extends TestCase
{
	use DatabaseTransactions;

	public function test_uploading_files_to_task()
	{

		$task = factory(Task::class)->create();


		$response = $this->json('POST', 'api/tasks/'.$task->id.'/files', [
			'files' =>[
				UploadedFile::fake()->image('file1.jpg'),

			]
		]);

		foreach (File::all() as $file)
		{
			$file = explode('/', $file->path);
			$path = array_pop($file);
			$this->assertEquals(true, file_exists(storage_path('app/tasks/' . $task->id . '/' . $path)));
		}

	}

	public function test_it_detach_files_from_specific_task(  )
	{
		// assuming we have task
		$task = factory(Task::class)->create();


		// and we attached file to it
		$response = $this->json('POST', 'api/tasks/'.$task->id.'/files', [
			'files' =>[
				UploadedFile::fake()->image('file1.jpg'),

			]
		]);

		$file = $task->files()->first();

		// when removing the file
		$response = $this->json('delete', 'api/tasks/' . $task->id . '/files/'.$file->id);

		$path = explode('/', $file->path);

		$path = array_pop($path);

		// then we don't see the file
		$this->assertEquals(false, file_exists(storage_path('app/tasks/' . $task->id . '/' . $path)));

		$this->assertEquals(null, File::find($file->id));
	}


}
