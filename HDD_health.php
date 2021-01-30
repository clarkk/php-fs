<?php

namespace FS;

class HDD_health extends Drive {
	private $verbose 	= false;
	private $disks 		= [];
	
	public function __construct(bool $verbose=false){
		parent::__construct();
		
		$this->verbose = $verbose;
	}
	
	public function check_health(): bool{
		$failed = false;
		
		if($this->get_drive_list()['md']){
			$Cmd = new \Utils\Cmd\Cmd;
			$err = $Cmd->exec('lsblk -dpno name');
			if(!$Cmd->is_success()){
				throw new Error('lsblk: '.$err);
			}
			
			foreach(explode("\n", $Cmd->output(true)) as $dev){
				$this->disks[$dev] = '';
				
				if(!$this->check_disk($dev)){
					$failed = true;
				}
			}
		}
		
		return !$failed;
	}
	
	public function get_disks(): array{
		return $this->disks;
	}
	
	private function check_disk(string $dev): bool{
		$health_result 			= 'SMART overall-health self-assessment test result:';
		$health_result_passed 	= $health_result.' PASSED';
		
		$Cmd 		= new \Utils\Cmd\Cmd;
		$err 		= $Cmd->exec('smartctl -H '.$dev);
		$output 	= $Cmd->output(true);
		
		$is_passed = strpos($output, $health_result_passed) !== false;
		
		if(!$Cmd->is_success()){
			if(strpos($output, $health_result) !== false && !$is_passed){
				$failed_result = $this->get_failed_result($dev, $output, $health_result);
				
				if($this->verbose){
					echo "$dev: $failed_result\n";
				}
				
				$this->disks[$dev] = $failed_result;
				
				return false;
			}
			
			throw new Error('smartctl: '.$err);
		}
		
		if($is_passed){
			if($this->verbose){
				echo "$dev: $health_result_passed\n";
			}
			
			$this->disks[$dev] = $health_result_passed;
			
			return true;
		}
		
		$failed_result = $this->get_failed_result($dev, $output, $health_result);
		
		if($this->verbose){
			echo "$dev: $failed_result\n";
		}
		
		$this->disks[$dev] = $failed_result;
		
		return false;
	}
	
	private function get_failed_result(string $dev, string $output, string $health_result): string{
		preg_match('/'.$health_result.'.*$/s', $output, $matches);
		
		$result = $matches[0];
		
		preg_match('/Serial Number: +(.*)$/m', shell_exec('smartctl -i '.$dev), $matches);
		
		$result .= "\nDisk serial number ($dev): ".$matches[1];
		
		return $result;
	}
}