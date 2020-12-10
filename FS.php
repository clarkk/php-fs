<?php

class FS {
	public function file_mimetype(string $file, bool $is_stream=false): string{
		$finfo = new finfo(FILEINFO_MIME);
		$type = $is_stream ? $finfo->buffer($file) : $finfo->file($file);
		$pos = strpos($type, ';');
		if($pos !== false){
			$type = substr($type, 0, $pos);
		}
		
		return $type;
	}
}