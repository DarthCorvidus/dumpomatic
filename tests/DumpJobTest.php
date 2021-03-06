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
class DumpJobTest extends TestCase {
	private function getBaseArray(): array {
		$array["name"] = "Sample database";
		$array["driver"] = "mysql";
		$array["host"] = "sample.example.com";
		$array["storage"] = "tests/storage/";
		$array["password"] = "SecretPassword";
		$array["user"] = "backup";
	return $array;
	}
	
	private function getRetentionArray(): array {
		$array = $this->getBaseArray();
		$array["retention"]["daily"] = 28;
		$array["retention"]["weekly"] = 52;
		$array["retention"]["monthly"] = 12;
		$array["retention"]["yearly"] = 10;
	return $array;
	}
	
	
	function testFromArray() {
		$job = DumpJob::fromArray($this->getBaseArray());
		$this->assertInstanceOf("DumpJob", $job);
	}
	
	function testGetName() {
		$job = DumpJob::fromArray($this->getBaseArray());
		$this->assertEquals("Sample database", $job->getName());
	}
	
	function testGetHost() {
		$job = DumpJob::fromArray($this->getBaseArray());
		$this->assertEquals("sample.example.com", $job->getHost());
	}

	function testGetUser() {
		$job = DumpJob::fromArray($this->getBaseArray());
		$this->assertEquals("backup", $job->getUser());
	}

	function testGetDriver() {
		$job = DumpJob::fromArray($this->getBaseArray());
		$this->assertEquals("mysql", $job->getDriver());
	}
	
	function testGetStorage() {
		$job = DumpJob::fromArray($this->getBaseArray());
		$this->assertEquals("tests/storage/", $job->getStorage());
	}

	function testHasRetentionDaily() {
		$array = $this->getRetentionArray();
		$job = DumpJob::fromArray($array);
		$this->assertEquals(true, $job->hasRetention("daily"));
		unset($array["retention"]["daily"]);
		$job = DumpJob::fromArray($array);
		$this->assertEquals(FALSE, $job->hasRetention("daily"));
	}

	function testGetRetentionDaily() {
		$job = DumpJob::fromArray($this->getRetentionArray());
		$this->assertEquals(28, $job->getRetention("daily"));
	}

	function testHasRetentionWeekly() {
		$array = $this->getRetentionArray();
		$job = DumpJob::fromArray($array);
		$this->assertEquals(true, $job->hasRetention("weekly"));
		unset($array["retention"]["weekly"]);
		$job = DumpJob::fromArray($array);
		$this->assertEquals(FALSE, $job->hasRetention("weekly"));
	}

	function testGetRetentionWeekly() {
		$job = DumpJob::fromArray($this->getRetentionArray());
		$this->assertEquals(52, $job->getRetention("weekly"));
	}

	function testHasRetentionMonthly() {
		$array = $this->getRetentionArray();
		$job = DumpJob::fromArray($array);
		$this->assertEquals(true, $job->hasRetention("monthly"));
		unset($array["retention"]["monthly"]);
		$job = DumpJob::fromArray($array);
		$this->assertEquals(FALSE, $job->hasRetention("monthly"));
	}

	function testGetRetentionMonthly() {
		$job = DumpJob::fromArray($this->getRetentionArray());
		$this->assertEquals(12, $job->getRetention("monthly"));
	}

	function testHasRetentionYearly() {
		$array = $this->getRetentionArray();
		$job = DumpJob::fromArray($array);
		$this->assertEquals(true, $job->hasRetention("yearly"));
		unset($array["retention"]["yearly"]);
		$job = DumpJob::fromArray($array);
		$this->assertEquals(FALSE, $job->hasRetention("yearly"));
	}

	function testGetRetentionYearly() {
		$job = DumpJob::fromArray($this->getRetentionArray());
		$this->assertEquals(10, $job->getRetention("yearly"));
	}

	function testGetInvalidRetention() {
		$job = DumpJob::fromArray($this->getRetentionArray());
		$this->expectException(InvalidArgumentException::class);
		$job->getRetention("baktun");
	}
	
	function testGetNonExistentRetention() {
		$array = $this->getRetentionArray();
		unset($array["retention"]["daily"]);
		$job = DumpJob::fromArray($array);
		$this->expectException(OutOfBoundsException::class);
		$job->getRetention("daily");
	}
	
	function testMutuallyExclusiveIncludeExclude() {
		$array = $this->getBaseArray();
		$array["include"] = array();
		$array["exclude"] = array();
		$this->expectException(InvalidArgumentException::class);
		$job = DumpJob::fromArray($array);
	}
}
