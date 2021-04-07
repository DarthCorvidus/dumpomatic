<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */

class ValidateDriverTest extends TestCase {
	function testSupportedDriver() {
		$drivers = array("postgresql", "mysql");
		$validate = new ValidateDriver();
		foreach($drivers as $key => $value) {
			$this->assertEquals(NULL, $validate->validate($value));
		}
	}
	
	function testUnsupportedDriver() {
		$validate = new ValidateDriver();
		$this->expectException(ValidateException::class);
		$this->expectExceptionMessage("driver 'fancybase' not supported");
		$validate->validate("fancybase");
	}
}
