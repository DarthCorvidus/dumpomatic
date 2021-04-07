<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */

class ValidateStorageTest extends TestCase {
	function testValidStorage() {
		$validate = new ValidateStorage();
		$this->assertEquals(NULL, $validate->validate("tests/storage/"));
	}
	
	function testInvalidStorageFile() {
		$validate = new ValidateStorage();
		$this->expectException(ValidateException::class);
		$this->expectExceptionMessage("path 'tests/ValidateStorageTest.php' is not a directory");
		$validate->validate("tests/ValidateStorageTest.php");
	}

	function testInvalidStorageMissing() {
		$validate = new ValidateStorage();
		$this->expectException(ValidateException::class);
		$this->expectExceptionMessage("path 'tests/storages/' does not exist");
		$validate->validate("tests/storages/");
	}

}
