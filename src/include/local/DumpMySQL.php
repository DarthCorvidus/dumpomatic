<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */
class DumpMySQL extends Dump {
	private $extra;
	function __construct(JulianDate $date, DumpJob $job) {
		parent::__construct($date, $job);
		/**
		 * We store MySQL's username & password within a temporary .cnf-file.
		 * As it will be stored in the same directory as the job configuration
		 * file, no additional security issues occur, but we do not have to
		 * use MySQLs password on the command line.
		 */
		$this->extra = $job->getStorage()."/credentials.temp";
		$extra[] = "[client]";
		$extra[] = "host=".$this->job->getHost();
		$extra[] = "user=".$this->job->getUser();
		$extra[] = "password=".$this->job->getPassword();
		file_put_contents($this->extra, implode(PHP_EOL, $extra));
	}
	public function getDatabaseNames():array {
		$dbnames = array();
		$dsn = "mysql:host=".$this->job->getHost().";dbname=information_schema";
		#$pdo = new PDO($dsn, $this->job->getUser(), $this->job->getPassword());
		$pdo = new PDO($dsn, $this->job->getUser(), $this->job->getPassword());
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
		
		$command[] = "> ".escapeshellarg($temp);
		$cmd = implode(" ", $command);
		echo $cmd.PHP_EOL;
		$out = "";
		$returnCode = 0;
		exec($cmd, $out, $returnCode);
		if($returnCode>=2) {
			echo "Error dumping ".$name.PHP_EOL.PHP_EOL;
			unlink($temp);
			return;
		}
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