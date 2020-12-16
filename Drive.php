<?php

namespace FS;

class Drive {
	private $drives 	= [];
	private $sd 		= [];
	private $md 		= [];
	
	const SIZE 			= 0;
	const USED 			= 1;
	const FREE 			= 2;
	const USE_PERCENT 	= 3;
	const MOUNT 		= 4;
	
	public function __construct(){
		$lines 		= array_filter(explode("\n", shell_exec('df -h')));
		$headers 	= $this->prepare_line(array_shift($lines));
		
		foreach($lines as $line){
			$line = $this->prepare_line($line);
			
			if(strpos($line[5], '/boot') !== 0){
				$dev = array_shift($line);
				
				if(strpos($dev, '/dev/sd') === 0){
					$this->drives[$dev]	= $line;
					$this->sd[$dev]		= $line;
				}
				
				if(strpos($dev, '/dev/md') === 0){
					$this->drives[$dev]	= $line;
					$this->md[$dev]		= $line;
				}
			}
		}
	}
	
	public function get_drive_list(): array{
		$list = [
			'all' => [],
			'sd' => [],
			'md' => []
		];
		
		foreach($this->drives as $dev => $drive){
			$list['all'][$dev] = $drive[0];
		}
		
		foreach($this->sd as $dev => $drive){
			$list['sd'][$dev] = $drive[0];
		}
		
		foreach($this->md as $drive){
			$list['md'][$dev] = $drive[0];
		}
		
		return $list;
	}
	
	public function get_drives(bool $human_readable=false): array{
		if($human_readable){
			$list = [];
			foreach($this->drives as $dev => $drive){
				$list[$dev] = $drive[self::SIZE].' ('.$drive[self::USE_PERCENT].' used)';
			}
			
			return $list;
		}
		else{
			return $this->drives;
		}
	}
	
	public function check_threshold_used(int $threshold): array{
		$warnings = [];
		foreach($this->drives as $dev => $drive){
			if((int)$drive[self::USE_PERCENT] >= $threshold){
				$warnings[$dev] = $drive;
			}
		}
		
		return $warnings;
	}
	
	private function prepare_line(string $line): array{
		return explode(' ', preg_replace('/ +/', ' ', $line));
	}
}