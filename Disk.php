<?php

namespace FS;

class Disk {
	private $drives 	= [];
	
	const DRIVE 		= 0;
	const SIZE 			= 1;
	const USED 			= 2;
	const FREE 			= 3;
	const USE_PERCENT 	= 4;
	
	public function __construct(){
		$lines 		= array_filter(explode("\n", shell_exec('df -h')));
		$headers 	= $this->prepare_line(array_shift($lines));
		
		foreach($lines as $line){
			$line = $this->prepare_line($line);
			if(strpos($line[0], '/dev/sd') === 0 && strpos($line[5], '/boot/') !== 0){
				$this->drives[] = $line;
			}
		}
	}
	
	public function get_drives(bool $human_readable=false): array{
		if($human_readable){
			$output = [];
			foreach($this->drives as $drive){
				$output[] = $drive[self::DRIVE].': '.$drive[self::SIZE].' ('.$drive[self::USE_PERCENT].' used)';
			}
			
			return $output;
		}
		else{
			return $this->drives;
		}
	}
	
	public function check_threshold_used(int $threshold): array{
		$warnings = [];
		foreach($this->drives as $drive){
			if((int)$drive[4] >= $threshold){
				$warnings[] = $drive;
			}
		}
		
		return $warnings;
	}
	
	private function prepare_line(string $line): array{
		return explode(' ', preg_replace('/ +/', ' ', $line));
	}
}