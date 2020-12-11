<?php

namespace FS;

class Structure {
	private $id;
	private $base_path;
	private $min_digits;
	
	public function __construct(int $id, string $base_path, int $min_digits=2){
		$this->id 			= (string)$id;
		$this->base_path 	= $base_path;
		$this->min_digits 	= $min_digits;
	}
	
	public function create(): string{
		if(!is_dir($this->base_path)){
			throw new \Error('Base path is not a directory: '.$this->base_path);
		}
		
		if(!is_writable($this->base_path)){
			throw new \Error('Base path is not writeable: '.$this->base_path);
		}
		
		$path 		= $this->base_path.'/';
		$length 	= strlen($this->id);
		
		for($i=0; $i<$length; $i++){
			$len = $length - $i;
			
			if($len <= $this->min_digits){
				break;
			}
			
			$path .= str_pad($this->id[$i], $len, 0, STR_PAD_RIGHT).'/';
			
			if(is_dir($path)){
				chmod($path, 0777);
				
				if(!is_writable($path)){
					throw new \Error('Sub-directory is not writeable: '.$path);
				}
			}
			else{
				mkdir($path);
				chmod($path, 0777);
			}
		}
		
		return rtrim($path, '/');
	}
}