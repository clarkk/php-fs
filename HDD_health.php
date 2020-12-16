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
		$verify_passed = 'SMART overall-health self-assessment test result: PASSED';
		
		$Cmd = new \Utils\Cmd\Cmd;
		$err = $Cmd->exec('smartctl -H '.$dev);
		if(!$Cmd->is_success()){
			throw new Error('smartctl: '.$err);
		}
		
		if(strpos($Cmd->output(true), $verify_passed) !== false){
			if($this->verbose){
				echo "$dev: $verify_passed\n";
			}
			
			$this->disks[$dev] = $verify_passed;
			
			return true;
		}
		
		if($this->verbose){
			echo "$dev: FAILED health check\n";
		}
		
		$this->disks[$dev] = 'FAILED';
		
		return false;
	}
}