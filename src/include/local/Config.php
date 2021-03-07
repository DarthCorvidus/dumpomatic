<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */
class Config {
	private $parsed;
	private $file;
	private $driver;
	private $host;
	private $password;
	private $user;
	private $storage;
	private $include = array();
	private $exclude = array();
	function __construct($file) {
		$this->file = $file;
		$this->parsed = parse_ini_file($file);
		$this->driver = $this->importString("driver");
		$this->host = $this->importString("host");
		$this->password = $this->importString("password");
		$this->user = $this->importString("user");
		$this->storage = $this->importPath("storage");
		$this->include = $this->importArray("include");
		$this->exclude = $this->importArray("exclude");
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
		if(!isset($this->parsed[$key])) {
			return array();
		}
		if(empty($this->parsed[$key])) {
			return array();
		}
		$exp = preg_split("/ *,{1} */", $this->parsed[$key]);
	return $exp;
	}
}