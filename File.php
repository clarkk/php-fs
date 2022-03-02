<?php

namespace FS;

class File {
	static public function mimetype(string $file, bool $is_stream=false): string{
		$finfo 	= new \finfo(FILEINFO_MIME);
		$type 	= $is_stream ? $finfo->buffer($file) : $finfo->file($file);
		
		if($pos = strpos($type, ';')){
			$type = substr($type, 0, $pos);
		}
		
		return $type;
	}
}