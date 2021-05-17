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
class ImportJobTest extends TestCase {
	function testImportJobSimple() {
		$job["name"] = "Sample database";
		$job["host"] = "sample.example.com";
		$job["user"] = "backup";
		$job["password"] = "TotallySecurePassword";
		$job["storage"] = "tests/storage";
		$job["driver"] = "mysql";
		$importModel = new ImportJob();
		$import = new Import($job, $importModel);
		$this->assertEquals($job, $import->getArray());
	}

	function testImportJobRetention() {
		$job["name"] = "Sample database";
		$job["host"] = "sample.example.com";
		$job["user"] = "backup";
		$job["password"] = "TotallySecurePassword";
		$job["storage"] = "tests/storage";
		$job["driver"] = "mysql";
		$job["retention"]["daily"] = "28";
		$job["retention"]["weekly"] = "52";
		$job["retention"]["monthly"] = "12";
		$job["retention"]["yearly"] = "10";
		$importModel = new ImportJob();
		$import = new Import($job, $importModel);
		$this->assertEquals($job, $import->getArray());
	}

	function testImportJobInclude() {
		$job["name"] = "Sample database";
		$job["host"] = "sample.example.com";
		$job["user"] = "backup";
		$job["password"] = "TotallySecurePassword";
		$job["storage"] = "tests/storage";
		$job["driver"] = "mysql";
		$job["include"] = array("accounting", "employees");
		$importModel = new ImportJob();
		$import = new Import($job, $importModel);
		$this->assertEquals($job, $import->getArray());
	}

	function testImportJobExclude() {
		$job["name"] = "Sample database";
		$job["host"] = "sample.example.com";
		$job["user"] = "backup";
		$job["password"] = "TotallySecurePassword";
		$job["storage"] = "tests/storage";
		$job["driver"] = "mysql";
		$job["exclude"] = array("logs");
		$importModel = new ImportJob();
		$import = new Import($job, $importModel);
		$this->assertEquals($job, $import->getArray());
	}
	
	function testImportJobInvalidDriver() {
		$job["name"] = "Sample database";
		$job["host"] = "sample.example.com";
		$job["user"] = "backup";
		$job["password"] = "TotallySecurePassword";
		$job["storage"] = "tests/storage/";
		$job["driver"] = "fancybase";
		$importModel = new ImportJob();
		$import = new Import($job, $importModel);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"driver\"]: driver 'fancybase' not supported");
		$import->getArray();
	}

}
