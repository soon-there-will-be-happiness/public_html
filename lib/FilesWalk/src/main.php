<?php defined('FILESWALK') or die;

defined('ROOT_DIR') or define('ROOT_DIR', str_replace(['\lib', '\FilesWalk', '\src'], '', __DIR__));

class FilesWalk {

	public static function getFiles($root = ''){
		$data = [];

		$files = scandir(ROOT_DIR . $root);

		foreach ($files as $file) {
			if(in_array($file, ['.', '..', '.git', '.phpintel']))
				continue;
			
			elseif(is_dir(ROOT_DIR . $root . '\\' . $file) === true)
				$data[trim($root . '\\' . $file)] = [
					'type' => 'folder'
				] + self::folderInfo($root . '\\' . $file);

			else
				$data[trim($root . '\\' . $file)] = [
					'type' => substr($file, strrpos($file, '.') + 1),
					'time' => fileatime(ROOT_DIR . $root . '\\' . $file),
					'size' => filesize(ROOT_DIR . $root . '\\' . $file)
				];
		}

		return $data;
	}

	public static function printBlock($root, array $use){
		
	}

	public static function folderInfo($root = ''){
		$data = [
			'files' => 0,
			'folders' => 0
		];

		$files = scandir(ROOT_DIR . $root);

		foreach ($files as $file) {
			if(in_array($file, ['.', '..']))
				continue;
			
			elseif(is_dir(ROOT_DIR . $root . '\\' . $file) === true)
				$data['folders']++;

			else
				$data['files']++;
		}

		return $data;
	}
}