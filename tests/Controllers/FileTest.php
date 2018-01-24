<?php

namespace Tests\Controller;

use App\File;
use App\Task;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;


class FileTest extends TestCase
{
	use DatabaseTransactions;

	/**
	 * Creates new user with client id to authenticate through passport
	 * @return mixed
	 */
	public function createNewUserWithClientRecord()
	{
		$user = factory(User::class)->create();

		Auth::login($user);

		$response = $this->json('POST' , '/oauth/clients', [
			'name' => 'MyClient',
			'redirect' => 'http://localhost'
		]);

		return $user;

	}

	/**
	 * Creates headers for passport token
	 * @param null $user
	 *
	 * @return array
	 */
	protected function headers($user = null)
	{
		$headers = ['Accept' => 'application/json'];

		if (!is_null($user)) {
			$token = $user->createToken('Token Name')->accessToken;
			$headers['Authorization'] = 'Bearer ' . $token;
		}

		return $headers;
	}



	public function test_uploading_files_to_task()
	{

		$task = factory(Task::class)->create();

		$user = $this->createNewUserWithClientRecord();

		dd($user);
		$headers = $this->headers($user);


		$response = $this->json('POST', 'api/tasks/'.$task->id.'/files', [
			'files' =>[
				UploadedFile::fake()->image('file1.jpg'),

			]
		], $headers);

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


		$user = $this->createNewUserWithClientRecord();

		$headers = $this->headers($user);


		// and we attached file to it
		$response = $this->json('POST', 'api/tasks/'.$task->id.'/files', [
			'files' =>[
				UploadedFile::fake()->image('file1.jpg'),

			]
		], $headers);

		$file = $task->files()->first();

		// when removing the file
		$response = $this->json('delete', 'api/tasks/' . $task->id . '/files/'.$file->id, [], $headers);

		$path = explode('/', $file->path);

		$path = array_pop($path);

		// then we don't see the file
		$this->assertEquals(false, file_exists(storage_path('app/tasks/' . $task->id . '/' . $path)));

		$this->assertEquals(null, File::find($file->id));
	}


}
