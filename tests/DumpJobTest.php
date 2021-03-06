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
	function testFromArray() {
		$yaml = yaml_parse_file(__DIR__."/config/sample.yml");
		$job = DumpJob::fromArray($yaml[0], __DIR__."/config/sample.yml");
		$this->assertInstanceOf("DumpJob", $job);
	}
	
	function testGetName() {
		$yaml = yaml_parse_file(__DIR__."/config/sample.yml");
		$job = DumpJob::fromArray($yaml[0], __DIR__."/config/sample.yml");
		$this->assertEquals("Sample database", $job->getName());
	}
	
	function testGetHost() {
		$yaml = yaml_parse_file(__DIR__."/config/sample.yml");
		$job = DumpJob::fromArray($yaml[0], __DIR__."/config/sample.yml");
		$this->assertEquals("sample.example.com", $job->getHost());
	}

	function testGetUser() {
		$yaml = yaml_parse_file(__DIR__."/config/sample.yml");
		$job = DumpJob::fromArray($yaml[0], __DIR__."/config/sample.yml");
		$this->assertEquals("backup", $job->getUser());
	}

	function testGetDriver() {
		$yaml = yaml_parse_file(__DIR__."/config/sample.yml");
		$job = DumpJob::fromArray($yaml[0], __DIR__."/config/sample.yml");
		$this->assertEquals("mysql", $job->getDriver());
	}
	
	function testGetStorage() {
		$yaml = yaml_parse_file(__DIR__."/config/sample.yml");
		$job = DumpJob::fromArray($yaml[0], __DIR__."/config/sample.yml");
		$this->assertEquals("tests/storage/", $job->getStorage());
	}

	function testHasRetentionDaily() {
		$yaml = yaml_parse_file(__DIR__."/config/sample.yml");
		$job = DumpJob::fromArray($yaml[0], __DIR__."/config/sample.yml");
		$this->assertEquals(true, $job->hasRetention("daily"));
	}

	function testGetRetentionDaily() {
		$yaml = yaml_parse_file(__DIR__."/config/sample.yml");
		$job = DumpJob::fromArray($yaml[0], __DIR__."/config/sample.yml");
		$this->assertEquals(28, $job->getRetention("daily"));
	}

	function testHasRetentionWeekly() {
		$yaml = yaml_parse_file(__DIR__."/config/sample.yml");
		$job = DumpJob::fromArray($yaml[0], __DIR__."/config/sample.yml");
		$this->assertEquals(true, $job->hasRetention("weekly"));
	}

	function testGetRetentionWeekly() {
		$yaml = yaml_parse_file(__DIR__."/config/sample.yml");
		$job = DumpJob::fromArray($yaml[0], __DIR__."/config/sample.yml");
		$this->assertEquals(52, $job->getRetention("weekly"));
	}

	function testHasRetentionMonthly() {
		$yaml = yaml_parse_file(__DIR__."/config/sample.yml");
		$job = DumpJob::fromArray($yaml[0], __DIR__."/config/sample.yml");
		$this->assertEquals(true, $job->hasRetention("monthly"));
	}

	function testGetRetentionMonthly() {
		$yaml = yaml_parse_file(__DIR__."/config/sample.yml");
		$job = DumpJob::fromArray($yaml[0], __DIR__."/config/sample.yml");
		$this->assertEquals(12, $job->getRetention("monthly"));
	}

	function testHasRetentionYearly() {
		$yaml = yaml_parse_file(__DIR__."/config/sample.yml");
		$job = DumpJob::fromArray($yaml[0], __DIR__."/config/sample.yml");
		$this->assertEquals(true, $job->hasRetention("yearly"));
	}

	function testGetRetentionYearly() {
		$yaml = yaml_parse_file(__DIR__."/config/sample.yml");
		$job = DumpJob::fromArray($yaml[0], __DIR__."/config/sample.yml");
		$this->assertEquals(10, $job->getRetention("yearly"));
	}

	function testGetInvalidRetention() {
		$yaml = yaml_parse_file(__DIR__."/config/sample.yml");
		$job = DumpJob::fromArray($yaml[0], __DIR__."/config/sample.yml");
		$this->expectException(InvalidArgumentException::class);
		$job->getRetention("baktun");
	}
	
	function testGetNonExistentRetention() {
		$yaml = yaml_parse_file(__DIR__."/config/noretention.yml");
		$job = DumpJob::fromArray($yaml[0], __DIR__."/config/sample.yml");
		$this->expectException(OutOfBoundsException::class);
		$job->getRetention("daily");
	}

}
