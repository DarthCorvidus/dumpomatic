<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DumpSQLite
 *
 * @author hm
 */
class DumpSQLite extends Dump {
	public function dumpDatabase(string $name, string $target) {
		$temp = $target."/".$name.".temp.sql";
		$tempzip = $target."/".$name.".temp.sql.gz";
		$final = $target."/".$name.".sql.gz";
		$command[] = "echo '.dump' |";
		$command[] = "sqlite3 ".escapeshellarg($this->job->getHost());
		$command[] = "> ".$temp;
		#$command[] = "-h".$this->job->getHost();
		#$command[] = "-Ft";
		#$command[] = "--no-password";
		#$command[] = "> ".escapeshellarg($temp);
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

	public function getDatabaseNames(): array {
		$basename = basename($this->job->getHost());
		$basename = preg_replace("/\.sqlite$/", "", $basename);
		return array($basename);
	}

	public function getExcluded(): array {
		return array();
	}

}
