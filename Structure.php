<?php

namespace FS;

class Structure extends Dir {
	const WWW_USER = 'www-data';
	
	static public function purge(string $path){
		if(!is_dir($path)){
			throw new Error('Path is not a directory: '.$path);
		}
		
		while(ctype_digit(basename($path)) && self::is_empty($path)){
			rmdir($path);
			
			$path = dirname($path);
		}
	}
	
	public function get(int $min_digits=2): string{
		return $this->path($min_digits);
	}
	
	public function create(int $min_digits=2, string $chown=''): string{
		return $this->path($min_digits, $chown, true);
	}
	
	private function path(int $min_digits, string $chown='', bool $create=false): string{
		$path 		= $this->base_path.'/';
		$length 	= strlen($this->id);
		
		for($i=0; $i<$length; $i++){
			$len = $length - $i;
			
			if($len <= $min_digits){
				break;
			}
			
			//	Continue if digit is 0
			if(!$this->id[$i]){
				continue;
			}
			
			$path .= str_pad($this->id[$i], $len, 0, STR_PAD_RIGHT).'/';
			
			if($create && !$this->is_url){
				if(is_dir($path)){
					if(!is_writable($path)){
						throw new Error('Directory is not writeable: '.$path);
					}
				}
				else{
					mkdir($path);
					
					if($chown){
						chown($path, $chown);
					}
				}
			}
		}
		
		$path = rtrim($path, '/');
		
		//	Return error if directory path is not found
		if(!$create && !$this->is_url && !is_dir($path)){
			throw new Error('Directory is not found: '.$path);
		}
		
		return $this->is_url ? $path : realpath($path);
	}
}