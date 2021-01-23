<?php

namespace FS;

class Purge_dirs {
	public function purge(string $dir){
		if(is_dir($dir)){
			while($this->is_empty($dir)){
				rmdir($dir);
				
				$dir = dirname($dir);
			}
		}
	}
	
	private function is_empty(string $dir): bool{
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