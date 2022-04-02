<?php

namespace FS;

class Purge_structure extends Dir {
	static public function purge(string $path){
		if(!is_dir($path)){
			throw new Error('Path is not a directory: '.$path);
		}
		
		while(ctype_digit(basename($path)) && self::is_empty($path)){
			rmdir($path);
			
			$path = dirname($path);
		}
	}
}