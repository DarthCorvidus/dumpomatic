<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */
class DumpJobs {
	private $date;
	private $configs = [];
	private $failed = [];
	function __construct(string $config) {
		$this->date = new Date();
		if(!file_exists($config)) {
			echo "Configuration '".$config."' not available.".PHP_EOL;
			die();
		}
		if(is_file($config)) {
			$this->configs[] = new Config($config);
			return;
		}
		foreach(glob($config."/*.conf") as $value) {
			try {
				$this->configs[] = new Config($value);
			} catch (Exception $e) {
				echo $e->getMessage().PHP_EOL;
				$this->failed[] = basename($value);
			}
		}
	}
	
	private function runJob(Config $config) {
		if($config->driver=="pgsql") {
			$backup = new DumpPostgreSQL($this->date, $config);
			$backup->run();
		return;
		}
		if($config->driver=="mysql") {
			$backup = new DumpMySQL($this->date, $config);
			$backup->run();
		return;
		}
	}
			
	function run() {
		foreach($this->configs as $value) {
			try {
				$this->runJob($value);
			} catch (Exception $e) {
				echo "Configuration ".$value->file." failed:".PHP_EOL;
				$this->failed[] = $value->file;
				echo $e->getMessage().PHP_EOL.PHP_EOL;
			}
		}
		if(empty($this->failed)) {
			return;
		}
		echo "Some jobs failed:".PHP_EOL;
		foreach($this->failed as $key => $value) {
			echo "\t".basename($value).PHP_EOL;
		}
	}
}