<?php
/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */

/**
 * Validate Storage
 * 
 * Basic validation for (storage) directory.
 */
class ValidateStorage implements Validate {
	function validate(string $validee) {
		if(!file_exists($validee)) {
			throw new ValidateException("path '".$validee."' does not exist");
		}
		if(!is_dir($validee)) {
			throw new ValidateException("path '".$validee."' is not a directory");
		}

	}
}
