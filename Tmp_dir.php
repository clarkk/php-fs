<?php

namespace FS;

class Tmp_dir extends Dir {
	private $path;
	private $is_purged = false;
	private $auto_purge;
	
	public function create(string $name, bool $auto_purge=true): string{
		$this->auto_purge = $auto_purge;
		
		$this->path = $this->base_path.'/'.\Time\Time::file_timestamp().'_'.uniqid().'_'.$name.($this->id ? '_'.$this->id : '');
		
		if(!is_dir($this->path)){
			mkdir($this->path);
		}
		
		return realpath($this->path);
	}
	
	public function purge(bool $delete_recursive=false){
		if($delete_recursive){
			shell_exec('rm -r '.$this->path);
		}
		else{
			if($this->is_empty()){
				rmdir($this->path);
			}
		}
		
		$this->is_purged = true;
	}
	
	private function is_empty(): bool{
		return count(scandir($this->path)) <= 2;
	}
	
	public function __destruct(){
		if($this->auto_purge && !$this->is_purged && $this->is_empty()){
			rmdir($this->path);
		}
	}
}