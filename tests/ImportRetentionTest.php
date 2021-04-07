<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */

class ImportRetentionTest extends TestCase {
	/**
	 * Test full retention
	 * 
	 * All possible retention values used.
	 */
	function testFullRetention() {
		$array["daily"] = 28;
		$array["weekly"] = 52;
		$array["monthly"] = 12;
		$array["yearly"] = 10;

		$importModel = new ImportRetention();
		$import = new Import($array, $importModel);
		$this->assertEquals($array, $import->getArray());
	}
	
	/**
	 * Test Retention Daily
	 * 
	 * Only daily retention used.
	 */
	function testRetentionDaily() {
		$array["daily"] = 28;

		$importModel = new ImportRetention();
		$import = new Import($array, $importModel);
		$this->assertEquals($array, $import->getArray());
	}

	/**
	 * Test Retention Weekly
	 * 
	 * Only weekly retention used.
	 */
	function testRetentionWeekly() {
		$array["weekly"] = 52;
		$importModel = new ImportRetention();
		$import = new Import($array, $importModel);
		$this->assertEquals($array, $import->getArray());
	}

	/**
	 * Test Retention Monthly
	 * 
	 * Only monthly retention used.
	 */
	function testRetentionMonthly() {
		$array["monthly"] = 12;
		$importModel = new ImportRetention();
		$import = new Import($array, $importModel);
		$this->assertEquals($array, $import->getArray());
	}

	/**
	 * Test Retention Yearly
	 * 
	 * Only yearly retention used.
	 */
	function testRetentionYearly() {
		$array["yearly"] = 10;
		$importModel = new ImportRetention();
		$import = new Import($array, $importModel);
		$this->assertEquals($array, $import->getArray());
	}

	/**
	 * Test Retention Unexpected
	 * 
	 * Test bogus key, in this case Yearly instead of yearly.
	 */
	function testRetentionUnexpected() {
		$array["Yearly"] = 10;
		$importModel = new ImportRetention();
		$import = new Import($array, $importModel);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("[\"Yearly\"] with value '10' is not expected in array");
		$import->getArray();
	}

	/**
	 * Test Retention Daily Bogus
	 * 
	 * Bogus integer used, which results in proper exception.
	 */
	function testRetentionDailyBogus() {
		$array["daily"] = "28string";
		$importModel = new ImportRetention();
		$import = new Import($array, $importModel);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("Validation failed for [\"daily\"]:");
		$import->getArray();
	}

	/**
	 * Test Retention Weekly Bogus
	 * 
	 * Bogus integer used, which results in proper exception.
	 */
	function testRetentionWeeklyBogus() {
		$array["weekly"] = "52string";
		$importModel = new ImportRetention();
		$import = new Import($array, $importModel);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("Validation failed for [\"weekly\"]:");
		$import->getArray();
	}

	/**
	 * Test Retention Monthly Bogus
	 * 
	 * Bogus integer used, which results in proper exception.
	 */
	function testRetentionMonthlyBogus() {
		$array["monthly"] = "12string";
		$importModel = new ImportRetention();
		$import = new Import($array, $importModel);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("Validation failed for [\"monthly\"]:");
		$import->getArray();
	}

	/**
	 * Test Retention Yearly Bogus
	 * 
	 * Bogus integer used, which results in proper exception.
	 */
	function testRetentionYearlyBogus() {
		$array["yearly"] = "10string";
		$importModel = new ImportRetention();
		$import = new Import($array, $importModel);
		$this->expectException(ImportException::class);
		$this->expectExceptionMessage("Validation failed for [\"yearly\"]:");
		$import->getArray();
	}


}
