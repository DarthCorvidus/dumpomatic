<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */
abstract class Dump {
	protected $config;
	private $date;
	function __construct(Date $date, Config $config) {
		$this->date = $date;
		$this->config = $config;
	}
	
	abstract function getDatabaseNames(): array;
	abstract function dumpDatabase(string $name, string $target);
	abstract function getExcluded(): array;

	private function cleanup(string $dir) {
		if(!file_exists($dir)) {
			return;
		}
		
		$rm = "rm ".escapeshellarg($dir)." -rvf";
		echo $rm.PHP_EOL;
		exec($rm);
	}
	
	function run() {
		echo "Running ".$this->config->file.PHP_EOL;
		$names = $this->getDatabaseNames();
		$temp = $this->config->storage."/temp";
		$final = $this->config->storage."/".$this->date->getDate("Y-m-d");
		$this->cleanup($temp);
		$this->cleanup($final);
		mkdir($temp);
		foreach($names as $key => $value) {
			if(in_array($value, $this->getExcluded())) {
				continue;
			}
			if(!empty($this->config->include) && !in_array($value, $this->config->include)) {
				echo "Skipping ".$value." (not included)".PHP_EOL;
				continue;
			}
			if(!empty($this->config->exclude) && in_array($value, $this->config->exclude)) {
				echo "Skipping ".$value." (excluded)".PHP_EOL;
				continue;
			}
			echo "Dumping ".$value."...".PHP_EOL;
			$this->dumpDatabase($value, $temp);
		}
		rename($temp, $final);
		echo PHP_EOL;
	}
}