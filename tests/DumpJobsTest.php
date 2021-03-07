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
}
