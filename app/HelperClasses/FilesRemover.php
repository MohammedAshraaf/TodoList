<?php

namespace App\HelperClasses;


use DirectoryIterator;

class FilesRemover
{

	/**
	 * Clean all files within a directory
	 * @param $path
	 * @param array $leftFiles
	 *
	 * @return bool
	 */
	public function removeFilesFromDirectory($path, $leftFiles = [])
	{
		$files = glob($path);

		foreach ($files as $file)
		{
			if(!in_array($file, $leftFiles))
			{
				unlink($file);
			}
		}

		return true;

	}
}