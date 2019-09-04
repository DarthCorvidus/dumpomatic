<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */
class DumpPostgreSQL extends Dump {
	public function getDatabaseNames():array {
		$dbnames = array();
		$dsn = "pgsql:host=".$this->config->host.";dbname=postgres";
		$pdo = new PDO($dsn, $this->config->user, $this->config->password);
		$stmt = $pdo->prepare("select datname, usename from pg_database join pg_user ON (pg_database.datdba = pg_user.usesysid) order by datname");
		$stmt->execute();
		foreach($stmt as $key => $value) {
			$dbnames[] = $value[0];
		}
	return $dbnames;
	}

	public function dumpDatabase(string $name, string $target) {
		$temp = $target."/".$name.".temp.tar";
		$tempzip = $target."/".$name.".temp.tar.gz";
		$final = $target."/".$name.".tar.gz";
		$command[] = "pg_dump ".$name;
		$command[] = "-U".$this->config->user;
		$command[] = "-h".$this->config->host;
		$command[] = "-Ft";
		$command[] = "--no-password";
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
		return array("template0", "template1", "postgres");
	}

}