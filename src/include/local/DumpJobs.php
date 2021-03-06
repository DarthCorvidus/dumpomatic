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
	private $jobs = [];
	static function fromYAML($file): DumpJobs {
		if(is_dir($file)) {
			throw new InvalidArgumentException("path '".$file."' is a directory.");
		}
		if(!file_exists($file)) {
			throw new InvalidArgumentException("file '".$file."' does not exist.");
		}
		$parsed = yaml_parse_file($file);
		if(empty($parsed) or ! is_array($parsed)) {
			throw new InvalidArgumentException("file '".$file."' could not be parsed.");
		}
		$jobs = new DumpJobs();
		/**
		 * @todo: Rework for testing
		 * This is reasonable for production use, but defeats testing, as it
		 * can never fail.
		 */
		foreach($parsed as $key => $value) {
			try {
				$jobs->jobs[] = DumpJob::fromArray($value);
				$jobs->date = new JulianDate();
			} catch (Exception $e) {
				$jobs->failed[] = $file." ".$e->getMessage();
			}
		}
	return $jobs;
	}
	/*
	function __construct(array $argv) {
		$this->date = new Date();
		if(!isset($argv[1])) {
			echo "Configuration parameter missing.".PHP_EOL;
			die();
		}
		if(!file_exists($argv[1])) {
			echo "Configuration '".$argv[1]."' not available.".PHP_EOL;
			die();
		}
		if(is_file($argv[1])) {
			$this->configs[] = new Config($argv[1]);
			return;
		}
		foreach(glob($argv[1]."/*.conf") as $value) {
			try {
				$this->configs[] = new Config($value);
			} catch (Exception $e) {
				echo $e->getMessage().PHP_EOL;
				$this->failed[] = basename($value);
			}
		}
	}
	*/
	private function runJob(DumpJob $job) {
		if($job->getDriver()=="pgsql") {
			$backup = new DumpPostgreSQL($this->date, $job);
			$backup->run();
		return;
		}
		if($job->getDriver()=="mysql") {
			$backup = new DumpMySQL($this->date, $job);
			$backup->run();
		return;
		}
		if($job->getDriver()=="sqlite") {
			$backup = new DumpSQLite($this->date, $job);
			$backup->run();
		return;
		}

	}
			
	function run() {
		foreach($this->jobs as $value) {
			try {
				$this->runJob($value);
			} catch (Exception $e) {
				echo "Configuration '".$value->getName()."' failed:".PHP_EOL;
				$this->failed[] = $value->getName().":".$e->getMessage();
				echo $e->getMessage().PHP_EOL.PHP_EOL;
			}
		}
		if(empty($this->failed)) {
			return;
		}
		echo "Some jobs failed:".PHP_EOL;
		foreach($this->failed as $key => $value) {
			echo "\t".$value.PHP_EOL;
		}
	}
}