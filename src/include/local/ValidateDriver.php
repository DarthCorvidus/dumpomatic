<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */

/**
 * Validate Driver
 * 
 * Validates value for supported drivers.
 */
class ValidateDriver implements Validate {
	private $supported = array("mysql", "postgresql", "sqlite");
	function validate(string $validee) {
		if(!in_array($validee, $this->supported)) {
			throw new ValidateException("driver '".$validee."' not supported");
		}
	}
}
