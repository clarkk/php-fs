<?php

namespace FS;

class Dir {
	protected $id;
	protected $base_path;
	
	public function __construct(int $id, string $base_path){
		$this->id 			= (string)$id;
		$this->base_path 	= rtrim($base_path, '/');
		
		if(!is_dir($this->base_path)){
			throw new \Error('Base path is not a directory: '.$this->base_path);
		}
		
		if(!is_writable($this->base_path)){
			throw new \Error('Base path is not writeable: '.$this->base_path);
		}
	}
}