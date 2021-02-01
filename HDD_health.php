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
			
			if(!$this->check_raid_health()){
				$failed = true;
			}
		}
		
		return !$failed;
	}
	
	public function get_disks(): array{
		return $this->disks;
	}
	
	private function check_raid_health(){
		$partitions = [];
		$dev 		= null;
		$data 		= [];
		
		foreach(explode("\n", trim(shell_exec('cat /proc/mdstat'))) as $line){
			if(!$line = trim($line)){
				if($dev){
					$partitions[$dev]	= implode("\n", $data);
					$dev 				= null;
				}
				
				continue;
			}
			
			if(preg_match('/(md\d) : (.*)/', $line, $matches)){
				$dev 	= $matches[1];
				$data 	= [$matches[2]];
			}
			elseif($dev){
				$data[] = $line;
			}
		}
		
		if(!$partitions){
			throw new Error('No RAID partitions found');
		}
		
		$is_passed = true;
		
		foreach($partitions as $dev => $partition){
			if(!preg_match('/\[\d\/\d\] \[([U_]*)\]$/m', $partition, $matches)){
				throw new Error("No RAID status found: $dev");
			}
			
			if($this->verbose){
				echo "/dev/$dev: $partition\n";
			}
			
			$this->disks["/dev/$dev"] = $partition;
			
			if(strpos($matches[1], '_') !== false){
				$is_passed = false;
			}
		}
		
		return $is_passed;
	}
	
	private function check_disk(string $dev): bool{
		$health_result 			= 'SMART overall-health self-assessment test result:';
		$health_result_passed 	= $health_result.' PASSED';
		
		$Cmd 		= new \Utils\Cmd\Cmd;
		$err 		= $Cmd->exec('/usr/sbin/smartctl -H '.$dev);
		$output 	= $Cmd->output(true);
		
		$is_passed = strpos($output, $health_result_passed) !== false;
		
		if(!$Cmd->is_success()){
			if(!$is_passed && strpos($output, $health_result) !== false){
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
		
		preg_match('/Serial Number: +(.*)$/m', shell_exec('/usr/sbin/smartctl -i '.$dev), $matches);
		
		$result .= "\nDisk serial number ($dev): ".$matches[1];
		
		return $result;
	}
}