<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */
class DumpJob {
	private $parsed;
	private $name;
	private $driver;
	private $host;
	private $password;
	private $user;
	private $storage;
	private $retention;
	private $allowedRetention = array("daily", "weekly", "monthly", "yearly");
	private $include;
	private $exclude;
	private function __construct() {
		;
	}
	
	static function fromArray(array $array): DumpJob {
		$job = new DumpJob();
		$import = new Import($array, new ImportJob());
		$array = $import->getArray();
		$job->sanityCheck($array);
		$job->parsed = $array;
		$job->name = $array["name"];
		$job->driver = $array["driver"];
		$job->host = $array["host"];
		$job->password = $array["password"];
		$job->user = $array["user"];
		$job->storage = $array["storage"];
		if(isset($job->parsed["retention"])) {
			$job->retention = $array["retention"];
		}
		if(isset($job->parsed["include"])) {
			$job->include = $array["include"];
		}
		if(isset($job->parsed["exclude"])) {
			$job->exclude = $array["exclude"];
		}
		
	return $job;
	}
	
	private function sanityCheck(array $array) {
		if(isset($array["exclude"]) && isset($array["include"])) {
			throw new InvalidArgumentException("parameters 'exclude' and 'include' are mutually exclusive");
		}
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
	
	function getPassword(): string {
		return $this->password;
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
	
	function hasInclude(): bool {
		return is_array($this->include);
	}
	
	function getInclude(): array {
		if(!$this->hasInclude()) {
			throw new OutOfBoundsException("No include list defined");
		}
		return $this->include;
	}
	
	function hasExclude(): bool {
		return is_array($this->exclude);
	}
	
	function getExclude(): array {
		if(!$this->hasExclude()) {
			throw new OutOfBoundsException("No exclude list defined");
		}
		return $this->exclude;
	}

}