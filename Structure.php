<?php

namespace FS;

class Structure extends Dir {
	public function create(int $min_digits=2): string{
		$path 		= $this->base_path.'/';
		$length 	= strlen($this->id);
		
		for($i=0; $i<$length; $i++){
			$len = $length - $i;
			
			if($len <= $min_digits){
				break;
			}
			
			$path .= str_pad($this->id[$i], $len, 0, STR_PAD_RIGHT).'/';
			
			if(is_dir($path)){
				if(!is_writable($path)){
					throw new Error('Sub-directory is not writeable: '.$path);
				}
			}
			else{
				mkdir($path);
			}
		}
		
		return realpath(rtrim($path, '/'));
	}
}