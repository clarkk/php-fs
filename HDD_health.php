<?php

namespace FS;

class HDD_health extends Drive {
	private $verbose 	= false;
	private $disks 		= [];
	
	const HEALTH_RESULT 		= 'SMART overall-health self-assessment test result:';
	const HEALTH_RESULT_PASSED 	= self::HEALTH_RESULT.' PASSED';
	
	public function __construct(bool $verbose=false){
		parent::__construct();
		
		$this->verbose = $verbose;
	}
	
	public function diagnostics(int $disk_use_threshold=90): array{
		$result 	= [];
		$mail 		= [];
		
		$disk_health_passed = $this->check_disk_health();
		foreach($this->disks as $dev => $disk){
			$result[] = "$dev: $disk";
		}
		
		foreach($this->get_drives(true) as $dev => $drive){
			$result[] = "$dev: $drive";
			
			if($this->verbose){
				echo "$dev: $drive\n";
			}
		}
		
		if(!$disk_health_passed){
			$host = shell_exec('hostname');
			$mail = [
				'Disk failure '.$host,
				"$host\n".implode("\n", $this->disks)
			];
		}
		elseif($warnings = $this->check_threshold_used($disk_use_threshold)){
			$host = shell_exec('hostname');
			$mail = [
				'Disk usage ('.count($warnings).' warnings) '.$host,
				"$host\n".implode("\n", $warnings)
			];
		}
		
		return [
			'passed'	=> $disk_health_passed,
			'result'	=> $result,
			'mail'		=> $mail
		];
	}
	
	public function check_disk_health(): bool{
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
		
		foreach(explode("\n", trim(file_get_contents('/proc/mdstat'))) as $line){
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
		$Cmd 		= new \Utils\Cmd\Cmd;
		$err 		= $Cmd->exec('/usr/sbin/smartctl -H '.$dev);
		$output 	= $Cmd->output(true);
		
		$is_passed = strpos($output, self::HEALTH_RESULT_PASSED) !== false;
		
		if(!$Cmd->is_success()){
			if(!$is_passed && strpos($output, self::HEALTH_RESULT) !== false){
				$this->disk_failed($dev, $output);
				
				return false;
			}
			
			throw new Error('smartctl: '.$err);
		}
		
		if($is_passed){
			if($this->verbose){
				echo "[PASS Disk] $dev: ".self::HEALTH_RESULT_PASSED."\n";
			}
			
			$this->disks[$dev] = self::HEALTH_RESULT_PASSED;
			
			return true;
		}
		
		$this->disk_failed($dev, $output);
		
		return false;
	}
	
	private function disk_failed(string $dev, string $output): void{
		$failed_result = $this->get_failed_result($dev, $output);
		
		if($this->verbose){
			echo "[FAILURE Disk] $dev: $failed_result\n";
		}
		
		$this->disks[$dev] = $failed_result;
	}
	
	private function get_failed_result(string $dev, string $output): string{
		preg_match('/'.self::HEALTH_RESULT.'.*$/s', $output, $matches);
		
		$result = $matches[0];
		
		preg_match('/Serial Number: +(.*)$/m', shell_exec('/usr/sbin/smartctl -i '.$dev), $matches);
		
		$result .= "\nDisk serial number ($dev): ".$matches[1];
		
		return $result;
	}
}