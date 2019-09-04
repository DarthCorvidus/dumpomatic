<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */
class DumpMySQL extends Dump {
	private $extra;
	function __construct(\Date $date, \Config $config) {
		parent::__construct($date, $config);
		/**
		 * We store MySQL's username & password within a temporary .cnf-file.
		 * As it will be stored in the same directory as the job configuration
		 * file, no additional security issues occur, but we do not have to
		 * use MySQLs password on the command line.
		 */
		$this->extra = realpath(dirname($this->config->file))."/".basename($this->config->file).".temp";
		$extra[] = "[client]";
		$extra[] = "host=".$this->config->host;
		$extra[] = "user=".$this->config->user;
		$extra[] = "password=".$this->config->password;
		file_put_contents($this->extra, implode(PHP_EOL, $extra));
	}
	public function getDatabaseNames():array {
		$dbnames = array();
		$dsn = "mysql:host=".$this->config->host.";dbname=information_schema";
		$pdo = new PDO($dsn, $this->config->user, $this->config->password);
		$stmt = $pdo->prepare("select SCHEMA_NAME from information_schema.SCHEMATA order by SCHEMA_NAME");
		$stmt->execute();
		foreach($stmt as $key => $value) {
			$dbnames[] = $value[0];
		}
	return $dbnames;
	}

	public function dumpDatabase(string $name, string $target) {
		$temp = $target."/".$name.".temp.sql";
		$tempzip = $target."/".$name.".temp.sql.gz";
		$final = $target."/".$name.".sql.gz";
		$command[] = "mysqldump ";
		$command[] = "--defaults-extra-file=".escapeshellarg($this->extra);
		$command[] = $name;
		$command[] = "-u".$this->config->user;
		$command[] = "-h ".$this->config->host;
		
		$command[] = "> ".escapeshellarg($temp);
		$cmd = implode(" ", $command);
		echo $cmd.PHP_EOL;
		exec($cmd);
		$zip = "gzip ".escapeshellarg($temp);
		echo $zip.PHP_EOL;
		exec($zip);
		$mv = "mv ".escapeshellarg($tempzip)." ".escapeshellarg($final);
		echo $mv.PHP_EOL;
		exec($mv);
		echo PHP_EOL;
	}

	public function getExcluded(): array {
		return array();
	}
	
	public function __destruct() {
		unlink($this->extra);
	}
}