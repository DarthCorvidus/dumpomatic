<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AssertTest
 *
 * @author hm
 */
class DumpTest extends TestCase {
	private $date;
	function __construct() {
		parent::__construct();
		/*
		 * To prevent a possible race condition if someone runs the test at
		 * 23:59:59, we have to use a global date that stays the same for the
		 * whole test.
		 */
		$this->date = new JulianDate();
	}
	
	/**
	 * tearDown
	 * Remove Backup made by DumpSQLite
	 */
	function tearDown() {
		exec("rm -rf ".escapeshellarg(__DIR__."/storage/sqlite/"));
		exec("rm -rf ".escapeshellarg(__DIR__."/storage/".$this->date->__toString()));
		
	}
	
	function setUp() {
		exec("mkdir ".escapeshellarg(__DIR__."/storage/sqlite/"));
	}
	
	/**
	 * Get Array
	 * 
	 * Get Array containing the configuration from tests/config/dumpomatic.yml.
	 * @return array
	 */
	function getArray(): array {
		$parsed = yaml_parse_file(__DIR__."/config/dumpomatic.yml");
		$model = new ImportJob();
		$import = new Import($parsed[0], $model);
	return $import->getArray();
	}
	
	/**
	 * Construct
	 * 
	 * Simply constructing an instance of DumpSQLite from Array
	 */
	function testConstruct() {
		$job = DumpJob::fromArray($this->getArray());
		$dump = new DumpSQLite($this->date, $job);
		$this->assertInstanceOf("DumpSQLite", $dump);
		$this->assertInstanceOf("Dump", $dump);
	}
	
	/**
	 * Test Run
	 * 
	 * Test simple run and check whether expected file exists.
	 */
	function testRun() {
		$job = DumpJob::fromArray($this->getArray());
		$dump = new DumpSQLite($this->date, $job);
		$dump->run();
		$output[] = "Running Sample database";
		$output[] = "Dumping dumpomatic...";
		$output[] = "echo '.dump' | sqlite3 'tests/dumpomatic.sqlite' > 'tests/storage/sqlite/temp/dumpomatic.temp.sql'";
		$output[] = "gzip 'tests/storage/sqlite/temp/dumpomatic.temp.sql'";
		$output[] = "mv 'tests/storage/sqlite/temp/dumpomatic.temp.sql.gz' 'tests/storage/sqlite/temp/dumpomatic.sql.gz'";
		$output[] = "";
		$output[] = "";
		$this->expectOutputString(implode(PHP_EOL, $output).PHP_EOL);
		$this->assertFileExists(__DIR__."/storage/sqlite/".$this->date->getIsodate());
		$this->assertFileExists(__DIR__."/storage/sqlite/".$this->date->getIsodate()."/dumpomatic.sql.gz");
	}
	
	/**
	 * Test run again
	 * 
	 * Test two runs: the second run should delete and rewrite the backup.
	 * @todo: Maaaaybe the backup should not be deleted before the new backup
	 * is successful.
	 */
	function testRunAgain() {
		exec("mkdir ".escapeshellarg(__DIR__."/storage/sqlite/".$this->date->getIsodate()));
		$date = new JulianDate();
		$job = DumpJob::fromArray($this->getArray());
		$dump = new DumpSQLite($date, $job);

		$output = array();
		$output[] = "Running Sample database";
		$output[] = "rm 'tests/storage/sqlite/".$this->date->getIsodate()."' -rvf";
		$output[] = "Dumping dumpomatic...";
		$output[] = "echo '.dump' | sqlite3 'tests/dumpomatic.sqlite' > 'tests/storage/sqlite/temp/dumpomatic.temp.sql'";
		$output[] = "gzip 'tests/storage/sqlite/temp/dumpomatic.temp.sql'";
		$output[] = "mv 'tests/storage/sqlite/temp/dumpomatic.temp.sql.gz' 'tests/storage/sqlite/temp/dumpomatic.sql.gz'";
		$output[] = "";
		$output[] = "";
		$this->expectOutputString(implode(PHP_EOL, $output).PHP_EOL);
		
		$dump->run();
		$this->assertFileExists(__DIR__."/storage/sqlite/".$date->getIsodate());
		$this->assertFileExists(__DIR__."/storage/sqlite/".$date->getIsodate()."/dumpomatic.sql.gz");
	}

	function testMonthNoRetention() {
		$array = $this->getArray();
		unset($array["retention"]);
		$job = DumpJob::fromArray($array);
		$date = JulianDate::fromString("2020-01-01");
		for($i=0;$i<31;$i++) {
			$dump = new DumpSQLite(JulianDate::fromInt($date->toInt()+$i), $job);
			$this->expectOutputRegex("/.*/");
			$dump->run();
		}
		$this->assertFileExists(__DIR__."/storage/sqlite/2020-01-01/dumpomatic.sql.gz");
		$this->assertFileExists(__DIR__."/storage/sqlite/2020-01-31/dumpomatic.sql.gz");
	}

	function testMonthRetention28DaysLater() {
		$array = $this->getArray();
		$job = DumpJob::fromArray($array);
		$date = JulianDate::fromString("2020-01-01");
		for($i=0;$i<31;$i++) {
			$dump = new DumpSQLite(JulianDate::fromInt($date->toInt()+$i), $job);
			$this->expectOutputRegex("/.*/");
			$dump->run();
		}
		$this->assertEquals(FALSE, file_exists(__DIR__."/storage/sqlite/2020-01-01/dumpomatic.sql.gz"));
		$this->assertEquals(FALSE, file_exists(__DIR__."/storage/sqlite/2020-01-02/dumpomatic.sql.gz"));
		$this->assertEquals(FALSE, file_exists(__DIR__."/storage/sqlite/2020-01-03/dumpomatic.sql.gz"));
		$this->assertFileExists(__DIR__."/storage/sqlite/2020-01-04/dumpomatic.sql.gz");
		$this->assertFileExists(__DIR__."/storage/sqlite/2020-01-31/dumpomatic.sql.gz");
	}

}
