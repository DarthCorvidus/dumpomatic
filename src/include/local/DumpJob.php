<?php
class DumpJob {
	private $parsed;
	private $name;
	private $file;
	private $driver;
	private $host;
	private $password;
	private $user;
	private $storage;
	private $allowedRetention = array("daily", "weekly", "monthly", "yearly");
	private $include = array();
	private $exclude = array();
	private function __construct() {
		;
	}
	
	static function fromArray(array $array, string $filename): DumpJob {
		$job = new DumpJob();
		$job->parsed = $array;
		$job->file = $filename;
		$job->name = $job->importString("name");
		$job->driver = $job->importString("driver");
		$job->host = $job->importString("host");
		$job->password = $job->importString("password");
		$job->user = $job->importString("user");
		$job->storage = $job->importPath("storage");

		#$job->include = $job->importArray("include");
		#$job->exclude = $job->importArray("exclude");
	return $job;
	}
	
	private function importString(string $key): string {
		if(!isset($this->parsed[$key])) {
			throw new Exception("Configuration error in ".$this->file.": value ".$key." not defined");
		}
	return $this->parsed[$key];
	}
	
	private function importPath(string $key): string {
		$path = $this->importString($key);
		if(!is_dir($path)) {
			throw new Exception("Configuration in ".$this->file.": path ".$path." does not exist");
		}
	return $path;
	}
	
	private function importArray(string $key): array {
		#if(!isset($this->parsed[$key])) {
		#	return array();
		#}
		#if(empty($this->parsed[$key])) {
		#	return array();
		#}
		#$exp = preg_split("/ *,{1} */", $this->parsed[$key]);
	#return $exp;
	}

	function getName(): string {
		return $this->name;
	}
	
	function getHost(): string {
		return $this->host;
	}
	
	function getUser(): string {
		return $this->user;
	}
	
	function getDriver(): string {
		return $this->driver;
	}
	
	function getStorage(): string {
		return $this->storage;
	}
	
	private function validRetention($retention) {
		if(!in_array($retention, $this->allowedRetention)) {
			throw new InvalidArgumentException("retention period ".$retention." is invalid");
		}
	}

	private function existingRetention($retention) {
		if(!$this->hasRetention($retention)) {
			throw new OutOfBoundsException("retention period ".$retention." is undefined");
		}
	}
	
	function hasRetention(string $retention) {
		$this->validRetention($retention);
		return isset($this->parsed["retention"][$retention]);
	}
	
	function getRetention(string $retention): int {
		$this->validRetention($retention);
		$this->existingRetention($retention);
		return $this->parsed["retention"][$retention];
	}
}