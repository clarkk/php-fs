<?php

namespace FS;

require_once 'Dir.php';

class Tmp_dir extends Dir {
	private $path;
	private $is_purged = false;
	
	public function create(string $name): string{
		$this->path = $this->base_path.'/'.date('Y-m-d-His', time() + (new \DateTimeZone('Europe/Copenhagen'))->getOffset(new \DateTime('now'))).'_'.$this->id.'_'.$name;
		
		if(!is_dir($this->path)){
			mkdir($this->path);
		}
		
		return realpath($this->path);
	}
	
	public function purge(){
		shell_exec('rm '.$this->path.'/*');
		$this->__destruct();
		$this->is_purged = true;
	}
	
	public function __destruct(){
		if(!$this->is_purged && count(scandir($this->path)) <= 2){
			rmdir($this->path);
		}
	}
}