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
class DumpJobsTest extends TestCase {
	function testFromFile() {
		$jobs = DumpJobs::fromYAML(__DIR__."/config/sample.yml");
		$this->assertInstanceOf("DumpJobs", $jobs);
	}
	
	function testFromDirectory() {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("path '".__DIR__."/config/' is a directory");
		$jobs = DumpJobs::fromYAML(__DIR__."/config/");
	}
	
	function testFromNonExistent() {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("file '".__DIR__."/config/fantasy.yml' does not exist");
		$jobs = DumpJobs::fromYAML(__DIR__."/config/fantasy.yml");
	}
	
	function testFromNoYAML() {
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage("file '".__DIR__."/config/malformed.yml' could not be parsed.");
		$jobs = DumpJobs::fromYAML(__DIR__."/config/malformed.yml");
	}


}
