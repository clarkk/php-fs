<?php

namespace FS;

class Purge_structure {
	static public function purge(string $path){
		if(!is_dir($path)){
			throw new Error('Path is not a directory: '.$path);
		}
		
		while($this->is_empty($path)){
			rmdir($path);
			
			$path = dirname($path);
		}
	}
	
	static private function is_empty(string $dir): bool{
		if(!ctype_digit(basename($dir))){
			return false;
		}
		
		$handle = opendir($dir);
		while(false !== ($entry = readdir($handle))){
			if($entry != '.' && $entry != '..'){
				closedir($handle);
				
				return false;
			}
		}
		closedir($handle);
		
		return true;
	}
}