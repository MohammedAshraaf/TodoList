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
				UploadedFile::fake()->image('file2.jpg'),
				UploadedFile::fake()->image('file3.jpg')
			]
		]);

		foreach (File::all() as $file)
		{
			$file = explode('/', $file->path);
			$s = array_pop($file);
			$this->assertEquals(true, file_exists(storage_path('app/tasks/' . $task->id . '/' . $s)));
		}

	}
}
