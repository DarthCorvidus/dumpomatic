#!/usr/bin/env php
<?php
/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */
#Include
#Imported from /home/hm/NetBeansProjects/dumpomatic/vendor/plibv4/import/src/ImportException.php

/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * Description of ImportException
 *
 * @author hm
 */
class ImportException extends RuntimeException {
	
}
#Imported from /home/hm/NetBeansProjects/dumpomatic/vendor/plibv4/import/src/Import.php

/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * Import
 * 
 * Uses an import model to import values from an array, considering default
 * values, mandatory values, validators and conversions.
 */
class Import {
	private $array = array();
	private $imported = array();
	private $model;
	private $path = array();
	/**
	 * Construct with the array you want to import from and an import model.
	 * @param array $array
	 * @param ImportModel $model
	 */
	function __construct(array $array, ImportModel $model) {
		$this->array = $array;
		$this->model = $model;
	}
	
	private function setPath(array $path) {
		$this->path = $path;
	}
	
	private function getPath():array {
	return $this->path;
	}
	
	private function getErrorPath($name):string {
		$path = $this->path;
		$path[] = $name;
		$niced = array();
		foreach ($path as $value) {
			if($value===NULL) {
				$niced[] = "[]";
				continue;
			}
			$niced[] = "[\"".$value."\"]";
		}
	return implode("", $niced);
	}
	
	
	private function checkUnexpected() {
		foreach($this->array as $key => $value) {
			if(!isset($this->imported[$key]) and is_scalar($value)) {
				throw new ImportException($this->getErrorPath($key)." with value '".$value."' is not expected in array");
			}
			if(!isset($this->imported[$key]) and is_array($value)) {
				throw new ImportException($this->getErrorPath($key)." is not expected in array");
			}

		}
	}
	
	private function noValue($key) {
		if(!isset($this->array[$key])) {
			return true;
		}
		if($this->array[$key]==="") {
			return true;
		}
		if($this->array[$key]===array()) {
			return true;
		}
	return false;
	}
	
	private function importScalars() {
		foreach($this->model->getScalarNames() as $value) {
			if($this->noValue($value) and $this->model->getScalarModel($value)->hasDefault()) {
				$this->imported[$value] = $this->model->getScalarModel($value)->getDefault();
				continue;
			}
			if($this->noValue($value) and $this->model->getScalarModel($value)->isMandatory()) {
				throw new ImportException($this->getErrorPath($value)." is missing from array");
			}
			if($this->noValue($value)) {
				continue;
			}
			$this->imported[$value] = $this->array[$value];
		}
	}
	
	private function validateScalars() {
		foreach($this->model->getScalarNames() as $key => $value) {
			if(!$this->model->getScalarModel($value)->hasValidate()) {
				continue;
			}
			// No need to call Validate on an optional, nonexisting value
			if(!isset($this->imported[$value])) {
				continue;
			}
			try {
				$this->model->getScalarModel($value)->getValidate()->validate($this->imported[$value]);
			} catch(ValidateException $e) {
				throw new ImportException("Validation failed for ".$this->getErrorPath($value).": ".$e->getMessage());
			}
		}
	}

	private function convertScalars() {
		foreach($this->model->getScalarNames() as $key => $value) {
			if(!$this->model->getScalarModel($value)->hasConvert()) {
				continue;
			}
			// No need to call Convert on an optional, nonexisting value
			if(!isset($this->imported[$value])) {
				continue;
			}
			$this->imported[$value] = $this->model->getScalarModel($value)->getConvert()->convert($this->imported[$value]);
		}
	}
	
	private function importDictionaries() {
		foreach($this->model->getImportNames() as $name) {
			$mypath = $this->getPath();
			$mypath[] = $name;
			if($this->noValue($name)) {
				$import = new Import(array(), $this->model->getImportModel($name));

				
				$import->setPath($mypath);
				$array = $import->getArray();
				// If $import returned an empty array - ie all values are
				// optional and none was defaulted - skip value altogether.
				if(empty($array)) {
					continue;
				}
				$this->imported[$name] = $array;
				continue;
			}
			$import = new Import($this->array[$name], $this->model->getImportModel($name));
			$import->setPath($mypath);
			$this->imported[$name] = $import->getArray();
		}
	}
	
	private function importLists() {
		foreach($this->model->getScalarListNames() as $name) {
			$scalarModel = $this->model->getScalarListModel($name);
			if($this->noValue($name) and $scalarModel->hasDefault()) {
				$this->imported[$name][] = $scalarModel->getDefault();
				continue;
			}
			if($this->noValue($name) and !$scalarModel->isMandatory()) {
				continue;
			}
			if($this->noValue($name) and $scalarModel->isMandatory()) {
				throw new ImportException($this->getErrorPath($name)."[] is mandatory, needs to contain at least one value");
			}
			if(!is_array($this->array[$name])) {
				throw new ImportException($this->getErrorPath($name)." is not an array");
			}
			/**
			 * There's a weak point here: $this->array[$name] could contain an
			 * associative array.
			 * @todo: Think about how to deal with this.
			 */
			$this->imported[$name] = $this->array[$name];
		}
		
	}

	private function importDictionaryList() {
		foreach($this->model->getImportListNames() as $name) {
			$mypath = $this->getPath();
			$mypath[] = $name;
			if($this->noValue($name)) {
				$mypath[] = NULL;
				$import = new Import(array(), $this->model->getImportListModel($name));
				$import->setPath($mypath);
				$array = $import->getArray();
				// If $import returned an empty array - ie all values are
				// optional and none was defaulted - skip value altogether.
				if(empty($array)) {
					continue;
				}
				$this->imported[$name][] = $array;
				continue;
			}

			
			foreach($this->array[$name] as $id => $sub) {
				$mypath[] = $id;
				$importModel = $this->model->getImportListModel($name);
				$import = new Import($sub, $importModel);
				$this->imported[$name][] = $import->getArray();
			}
		}
	}

	/**
	 * Get Array
	 * 
	 * Return array according to rules laid down in import model. It also checks
	 * for missing or unexpected values (values that do not exist in import
	 * model). Throws import exception if anything goes awry; will throw through
	 * Exceptions other than ValidateException, however.
	 * @return type
	 * @throws ImportException
	 */
	function getArray() {
		if($this->imported==array()) {
			$this->importScalars();
			$this->importLists();
			$this->validateScalars();
			$this->convertScalars();
			
			$this->importDictionaries();
			$this->importDictionaryList();
					
			$this->checkUnexpected();
		}
	return $this->imported;
	}
}
#Imported from /home/hm/NetBeansProjects/dumpomatic/vendor/plibv4/import/src/ImportModel.php

/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * ImportModel
 * 
 * An ImportModel is a representation of an array, it basically tells Import
 * how it should import/validate/convert values from a given array.
 */
interface ImportModel {
	/**
	 * get scalar names
	 * 
	 * Return a list of names that should be imported as scalar values, whereas
	 * the name has to correspondent with an array key that contains a scalar
	 * value.
	 */
	function getScalarNames(): array;
	/**
	 * Get scalar model
	 * 
	 * Return the scalar model for a specific array key.
	 * @param type $name
	 */
	function getScalarModel($name): ScalarModel;
	
	/**
	 * Get scalar list names
	 * 
	 * Return a list of names that should be imported as an array containing
	 * scalar values.
	 */
	function getScalarListNames(): array;
	
	/**
	 * Get Scalar List model
	 * 
	 * Return a scalar model to be applied to the list below $name.
	 * @param type $name
	 */
	function getScalarListModel($name): ScalarModel;
	
	/**
	 * Get Import Names
	 * 
	 * Return a list of names that should be imported as an associative array,
	 * basically an import model within an import model, to account for nested
	 * associative arrays such as $array["birth"]["location"] = "New York",
	 * $array["birth"]["time"] = "18:15:00".
	 */
	function getImportNames(): array;
	/**
	 * Get Import Model
	 * 
	 * Return an import model to be applied to the associative array below
	 * $name.
	 * @param type $name
	 */
	function getImportModel($name): ImportModel;
	
	/**
	 * Get Import List Names
	 * 
	 * Get a list of names that should be imported as a numeric array containing
	 * associative arrays.
	 */
	function getImportListNames(): array;
	/**
	 * Get Import List Model
	 * 
	 * Returns an import model which will be applied to each entry of a list
	 * below an array key $name.
	 * @param type $name
	 */
	function getImportListModel($name): ImportModel;
}
#Imported from /home/hm/NetBeansProjects/dumpomatic/vendor/plibv4/import/src/ScalarModel.php

/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * Interface for a scalar import model
 * 
 * Scalar import models are supposed to import scalar values as well as to
 * validate and to convert them.
 */
Interface ScalarModel {
	/**
	 * Get default value, which will be used if value is not in array or empty.
	 */
	function getDefault(): string;
	
	/**
	 * True if model has a default, false if not.
	 */
	function hasDefault(): bool;
	
	/**
	 * True if value is mandatory, false if not.
	 */
	function isMandatory(): bool;
	
	/**
	 * True if model has a validator attached
	 */
	function hasValidate(): bool;
	
	/**
	 * Get validator if available. Won't be called if hasValidate equals FALSE.
	 */
	function getValidate(): Validate;
	
	/**
	 * True if model has converter attached
	 */
	function hasConvert(): bool;
	
	/**
	 * Get converter. Won't be called if hasConvert equals FALSE
	 */
	function getConvert(): Convert;
}
#Imported from /home/hm/NetBeansProjects/dumpomatic/vendor/plibv4/import/src/ScalarGeneric.php

/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * ScalarGeneric
 * 
 * Generic implementation for scalar model.
 */
class ScalarGeneric implements ScalarModel {
	private $default;
	private $mandatory = FALSE;
	private $validate;
	private $convert;
	public function setDefault(string $default) {
		$this->default = $default;
	}
	public function getDefault(): string {
		return $this->default;
	}

	public function hasDefault(): bool {
		return $this->default!==NULL;
	}

	public function setMandatory() {
		$this->mandatory = true;
	}

	public function isMandatory(): bool {
		return $this->mandatory;
	}
	
	public function setValidate(Validate $validate) {
		$this->validate = $validate;
	}

	public function hasValidate(): bool {
		return $this->validate!=NULL;
	}

	public function getValidate(): Validate {
		return $this->validate;
	}

	public function setConvert(Convert $convert) {
		$this->convert = $convert;
	}
	
	public function hasConvert(): bool {
		return $this->convert!=NULL;
	}
	
	
	public function getConvert(): Convert {
		return $this->convert;
	}

}
#Imported from /home/hm/NetBeansProjects/dumpomatic/vendor/plibv4/validate/src/Validate.php


/**
 * Validate is an interface for a class that is supposed to validate strings,
 * which come from user input or other sources like command line arguments,
 * GET or POST parameters etc.
 */
interface Validate {
	/**
	 * Implementation is supposed to validate a string. Validate must not have
	 * a return value; if validation fails, a ValidateException has to be thrown.
	 * @param string $validee
	 * @throws ValidateException
	 */
	function validate(string $validee);
}
#Imported from /home/hm/NetBeansProjects/dumpomatic/vendor/plibv4/validate/src/ValidateException.php

/**
 * Exception if validation fails.
 *
 * @author Claus-Christoph Kuethe
 * @copyright (c) 2020, Claus-Christoph Kuethe
 */
class ValidateException extends UnexpectedValueException {
	
}
#Imported from ./include/local/ValidateStorage.php

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
#Imported from /home/hm/NetBeansProjects/dumpomatic/vendor/plibv4/convert/src/Convert.php

/**
 * Interface to convert one format into another.
 * 
 * The idea of Convert is to convert user input or other sources from one format
 * into another.
 * @author Claus-Christoph Küthe
 * @copyright (c) 2020, Claus-Christoph Küthe
 */
interface Convert {
	/**
	 * Convert string from one format to another.
	 * 
	 * @param string $convertee string to be converted
	 */
	public function convert(string $convertee): string;
}
#Imported from /home/hm/NetBeansProjects/dumpomatic/vendor/plibv4/assert/src/Assert.php


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Assert
 *
 * @author hm
 */
class Assert {
	static function isEnum($value, array $allowed) {
		if(!in_array($value, $allowed)) {
			throw new InvalidArgumentException("value ".$value." outside of set of allowed values (".implode(", ", $allowed).")");
		}
	}
	
	static function isClassConstant($class, $value, string $parameterName=NULL) {
		$reflection = new ReflectionClass($class);
		$constants = $reflection->getConstants();
		if(!in_array($value, $constants)) {
			if($parameterName==NULL) {
				$message = "value ".$value." not a class constant, allowed values are ";
			} else {
				$message = "\$".$parameterName." not a class constant, allowed values are ";
			}
			$allowed = array();
			foreach($constants as $key => $value) {
				$allowed[] = $class."::".$key;
			}
			$message .= implode(", ", $allowed);
			throw new InvalidArgumentException($message);
		}
	}
}
#Imported from /home/hm/NetBeansProjects/dumpomatic/vendor/plibv4/convert/src/ConvertTrailingSlash.php


/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <floss@vm01.telton.de>
 * @license LGPL
 */

/**
 * Convert Trailing Slash
 * 
 * Paths like /mnt/usb-drive//final/ do not look nice, and paths like
 * /mnt/usb-drivefinal are not nice either ;-). ConvertTrailingSlash removes
 * or adds trailing slashes from/to paths.
 */
class ConvertTrailingSlash implements Convert {
	const REMOVE = 1;
	const ADD = 2;
	private $format;
	/**
	 * 
	 * @param int $format add or remove slashes.
	 */
	function __construct(int $format = self::REMOVE) {
		Assert::isEnum($format, array(self::REMOVE, self::ADD));
		$this->format = $format;
	}
	
	/**
	 * convertRemove
	 * 
	 * Removes trailing slashes.
	 * @param string $convertee
	 * @return array
	 */
	private function convertRemove(string $convertee) {
		$matches = array();
		preg_match("/^(.*)\/*$/U", $convertee, $matches);
	return $matches[1];
	}

	/**
	 * Convert
	 * 
	 * Convert function as such. If ADD is used, first slashes will be removed
	 * and one will be added.
	 * @param string $convertee
	 * @return string
	 */
	function convert(string $convertee): string {
		if($this->format===self::REMOVE) {
			return $this->convertRemove($convertee);
		}
		if($this->format===self::ADD) {
			return $this->convertRemove($convertee)."/";
		}
	}
}
#Imported from ./include/local/ValidateDriver.php

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
#Imported from /home/hm/NetBeansProjects/dumpomatic/vendor/plibv4/validate/src/ValidateInteger.php


class ValidateInteger implements Validate {
	public function validate(string $validee) {
		if(preg_match("/^[0-9]*$/", $validee)) {
			return;
		}
	throw new ValidateException("not a valid integer");
	}
}
#Imported from ./include/local/ImportRetention.php

/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */

/**
 * Import Retention
 * 
 * Imports retention values from configuration files as part of ImportJob.
 */
class ImportRetention implements ImportModel {
	public $scalarValues = array();
	function __construct() {
		$this->scalarValues["daily"] = new ScalarGeneric();
		$this->scalarValues["daily"]->setValidate(new ValidateInteger());

		$this->scalarValues["weekly"] = new ScalarGeneric();
		$this->scalarValues["weekly"]->setValidate(new ValidateInteger());

		$this->scalarValues["monthly"] = new ScalarGeneric();
		$this->scalarValues["monthly"]->setValidate(new ValidateInteger());

		$this->scalarValues["yearly"] = new ScalarGeneric();
		$this->scalarValues["yearly"]->setValidate(new ValidateInteger());
	}

	public function getImportListModel($name): \ImportModel {
		
	}

	public function getImportListNames(): array {
		return array();
	}

	public function getImportModel($name): \ImportModel {
		
	}

	public function getImportNames(): array {
		return array();
	}

	public function getScalarListModel($name): \ScalarModel {
		
	}

	public function getScalarListNames(): array {
		return array();
	}

	public function getScalarModel($name): \ScalarModel {
		return $this->scalarValues[$name];
	}

	public function getScalarNames(): array {
		return array_keys($this->scalarValues);
	}

}
#Imported from ./include/local/ImportJob.php

/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */

/**
 * Import Job
 * 
 * Import Job from configuration file array, with ImportRetention to import
 * retention if defined.
 */
class ImportJob implements ImportModel {
	private $scalarValues = array();
	private $scalarLists = array();
	private $import = array();
	function __construct() {
		$this->scalarValues["name"] = new ScalarGeneric();
		$this->scalarValues["name"]->setMandatory();
		
		$this->scalarValues["host"] = new ScalarGeneric();
		$this->scalarValues["host"]->setMandatory();
		
		$this->scalarValues["user"] = new ScalarGeneric();
		$this->scalarValues["user"]->setMandatory();

		$this->scalarValues["password"] = new ScalarGeneric();
		$this->scalarValues["password"]->setMandatory();

		
		$this->scalarValues["storage"] = new ScalarGeneric();
		$this->scalarValues["storage"]->setMandatory();
		$this->scalarValues["storage"]->setValidate(new ValidateStorage());
		$this->scalarValues["storage"]->setConvert(new ConvertTrailingSlash());
		
		$this->scalarValues["driver"] = new ScalarGeneric();
		$this->scalarValues["driver"]->setMandatory();
		$this->scalarValues["driver"]->setValidate(new ValidateDriver());
		
		$this->scalarLists["include"] = new ScalarGeneric();
		$this->scalarLists["exclude"] = new ScalarGeneric();
		
		$this->import["retention"] = new ImportRetention();;
		
		
	}
	public function getImportListModel($name): \ImportModel {
		
	}

	public function getImportListNames(): array {
		return array();
	}

	public function getImportModel($name): \ImportModel {
		return $this->import[$name];
	}

	public function getImportNames(): array {
		return array_keys($this->import);
	}

	public function getScalarListModel($name): \ScalarModel {
		return $this->scalarLists[$name];
	}

	public function getScalarListNames(): array {
		return array_keys($this->scalarLists);
	}

	public function getScalarModel($name): \ScalarModel {
		return $this->scalarValues[$name];
	}

	public function getScalarNames(): array {
		return array_keys($this->scalarValues);
	}

}
#Imported from ./include/local/DumpJob.php

/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */
class DumpJob {
	private $parsed;
	private $name;
	private $driver;
	private $host;
	private $password;
	private $user;
	private $storage;
	private $retention;
	private $allowedRetention = array("daily", "weekly", "monthly", "yearly");
	private $include;
	private $exclude;
	private function __construct() {
		;
	}
	
	static function fromArray(array $array): DumpJob {
		$job = new DumpJob();
		$import = new Import($array, new ImportJob());
		$array = $import->getArray();
		$job->sanityCheck($array);
		$job->parsed = $array;
		$job->name = $array["name"];
		$job->driver = $array["driver"];
		$job->host = $array["host"];
		$job->password = $array["password"];
		$job->user = $array["user"];
		$job->storage = $array["storage"];
		if(isset($job->parsed["retention"])) {
			$job->retention = $array["retention"];
		}
		if(isset($job->parsed["include"])) {
			$job->include = $array["include"];
		}
		if(isset($job->parsed["exclude"])) {
			$job->exclude = $array["exclude"];
		}
		
	return $job;
	}
	
	private function sanityCheck(array $array) {
		if(isset($array["exclude"]) && isset($array["include"])) {
			throw new InvalidArgumentException("parameters 'exclude' and 'include' are mutually exclusive");
		}
	}
	
	function getName(): string {
		return $this->name;
	}
	
	function getHost(): string {
		return $this->host;
	}
	
	function getUser(): string {
		return $this->user;
	}
	
	function getPassword(): string {
		return $this->password;
	}
	
	function getDriver(): string {
		return $this->driver;
	}
	
	function getStorage(): string {
		return $this->storage;
	}
	
	private function validRetention($retention) {
		if(!in_array($retention, $this->allowedRetention)) {
			throw new InvalidArgumentException("retention period ".$retention." is invalid");
		}
	}

	private function existingRetention($retention) {
		if(!$this->hasRetention($retention)) {
			throw new OutOfBoundsException("retention period ".$retention." is undefined");
		}
	}
	
	function hasRetention(string $retention) {
		$this->validRetention($retention);
		return isset($this->parsed["retention"][$retention]);
	}
	
	function getRetention(string $retention): int {
		$this->validRetention($retention);
		$this->existingRetention($retention);
		return $this->parsed["retention"][$retention];
	}
	
	function hasInclude(): bool {
		return is_array($this->include);
	}
	
	function getInclude(): array {
		if(!$this->hasInclude()) {
			throw new OutOfBoundsException("No include list defined");
		}
		return $this->include;
	}
	
	function hasExclude(): bool {
		return is_array($this->exclude);
	}
	
	function getExclude(): array {
		if(!$this->hasExclude()) {
			throw new OutOfBoundsException("No exclude list defined");
		}
		return $this->exclude;
	}

}
#Imported from /home/hm/NetBeansProjects/dumpomatic/vendor/plibv4/juliandate/src/JulianDate.php

/**
 * @copyright (c) 2021, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <plibv4@vm01.telton.de>
 * @license LGPLv2.1
 */

/**
 * Julian Date class
 * 
 * JulianDate is pure date only, using julian days. JulianDate is immutable, ie.
 * no function changes the internal state of an JulianDate instance, but returns
 * a new instance of JulianDate instead.
 */
class JulianDate {
	const DAY = 1;
	const WEEK = 2;
	const MONTH = 3;
	const YEAR = 4;
	/** @var int Date as Julian days */
	private $numeric;
	/**
	 * 
	 * @param int $year Year
	 * @param int $month Month
	 * @param int $day Day
	 */
	function __construct(int $year=NULL, int $month=NULL, int $day=NULL) {
		if($year==NULL)	{
			$now = time();
			$day = date("d", $now);
			$month = date("m", $now);
			$year = date("Y", $now);
		}
		$this->testRange($year, $month, $day);
		$this->numeric = gregoriantojd($month, $day, $year);
	}
	
	/**
	 * Tests if months and days are valid; doesn't allow for dates like
	 * 2020-06-31 or 2021-02-29.
	 * @param int $year Year
	 * @param int $month Month
	 * @param int $day Day
	 * @throws RangeException
	 */
	private function testRange(int $year, int $month, int $day) {
		if($month<1 || $month>12) {
			throw new RangeException("month is out of range");
		}
		if($day<1) {
			throw new RangeException("day is out of range");
		}
		if($day<=28) {
			return;
		}
		$date = new JulianDate($year, $month, 1);
		if($date->getFormat("t")<$day) {
			throw new RangeException("day is out of range");
		}
	}
	
	/**
	 * Creates a date from an ISO 6801 compliant string (YYYY-MM-DD)
	 * @param String $string Date as ISO 6801
	 * @return JulianDate
	 * @throws InvalidArgumentException
	 */
	static function fromString(String $string): JulianDate {
		$tmp = explode("-", $string);
		if(!preg_match("/[0-9]*-[0-9]{2}-[0-9]{2}/", $string)) {
			throw new InvalidArgumentException("invalid isodate, must be YYYY-MM-DD");	
		}
		if(!preg_match("/[0-9]+-[0-9]{2}-[0-9]{2}/", $string)) {
			throw new InvalidArgumentException("invalid isodate, must be YYYY-MM-DD");	
		}
	return new JulianDate($tmp[0], $tmp[1], $tmp[2]);
	}
	
	/**
	 * From Int
	 * 
	 * Constructs instance of JulianDate directly from julian days.
	 * 
	 * @param int $julian Julian Days
	 * @return JulianDate
	 */
	static function fromInt(int $julian): JulianDate {
		$date = new JulianDate();
		$date->numeric = $julian;
	return $date;
	}
	
	/**
	 * To Int
	 * 
	 * Return julian days as integer.
	 * @return int
	 */
	function toInt(): int {
		return $this->numeric;
	}
	
	/**
	 * 
	 * @param int $unit
	 * @throws InvalidArgumentException
	 */
	private function allowedUnit(int $unit) {
		if(!in_array($unit, array(self::DAY, self::WEEK, self::MONTH, self::YEAR))) {
			throw new InvalidArgumentException("\$unit does not contain an allowed unit");
		}
	}

	/**
	 * Get first day of a given unit.
	 * @param int $unit
	 * @return \JulianDate
	 */
	function getFirstOf(int $unit): JulianDate {
		if($unit==self::DAY) {
			return $this;
		}
		if($unit==self::WEEK) {
			$days = $this->numeric-($this->getFormat("N")-1);
		return JulianDate::fromInt($days);
		}

		if($unit==self::MONTH) {
			$array = cal_from_jd($this->numeric, CAL_GREGORIAN);
		return new JulianDate($array["year"], $array["month"], 1);
		}

		if($unit==self::YEAR) {
			$array = cal_from_jd($this->numeric, CAL_GREGORIAN);
		return new JulianDate($array["year"], 1, 1);
		}
	}
	
	/**
	 * Get last day of a given unit
	 * @param int $unit
	 * @return \JulianDate
	 */
	function getLastOf(int $unit): JulianDate {
		if($unit==self::DAY) {
			return $this;
		}
		if($unit==self::WEEK) {
			$days = $this->numeric+(7-$this->getFormat("N"));
		return JulianDate::fromInt($days);
		}

		if($unit==self::MONTH) {
			$array = cal_from_jd($this->numeric, CAL_GREGORIAN);
			return new JulianDate($array["year"], $array["month"], (int)$this->getFormat("t"));
		}

		if($unit==self::YEAR) {
			$array = cal_from_jd($this->numeric, CAL_GREGORIAN);
			return new JulianDate($array["year"], 1, 1);
		}
	}
	
	/**
	 * Get date in custom format.
	 * @param string $format
	 * @return string
	 */
	function getFormat(string $format): string {
		$time = strtotime($this->__toString());
	return date($format, $time);
	}

	/**
	 * Adds a specific Unit
	 * @param int $amount
	 * @param int $unit
	 * @return \JulianDate
	 */
	function addUnit(int $amount, int $unit): JulianDate {
		if($amount==0) {
			return $this;
		}
		$this->allowedUnit($unit);
		if($unit==self::WEEK) {
			return JulianDate::fromInt($this->numeric+($amount*7));
		}
		if($unit==self::MONTH) {
			return $this->addMonths($amount);
		}
		if($unit==self::YEAR) {
			return new JulianDate($this->getFormat("Y")+$amount, $this->getFormat("n"), $this->getFormat("j"));
		}
	return JulianDate::fromInt($this->numeric+$amount);
	}
	/**
	 * 
	 * @param int $amount Amount of Months to add.
	 * @return \JulianDate
	 */
	private function addMonths(int $amount): JulianDate {
		if($amount>0) {
			$years = floor($amount/12)+$this->getFormat("Y");
		} else {
			$years = ceil($amount/12)+$this->getFormat("Y");
		}
		$months = ($amount%12)+$this->getFormat("n");
	return new JulianDate($years, $months, $this->getFormat("j"));
	}
	
	/**
	 * Returns date as string (YYYY-MM-DD).
	 * @return string
	 */
	function __toString(): string {
		$array = cal_from_jd($this->numeric, CAL_GREGORIAN);
	return sprintf("%d-%02d-%02d", $array["year"], $array["month"], $array["day"]);
	}
	
	/**
	 * Get Isodate
	 * 
	 * Returns date as isodate. Basically the same as __toString()
	 * @return string 
	 */
	function getIsodate(): string {
		return $this->__toString();
	}
}
#Imported from ./include/local/Dump.php

/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */
abstract class Dump {
	protected $job;
	private $date;
	function __construct(JulianDate $date, DumpJob $job) {
		$this->date = $date;
		$this->job = $job;
	}
	
	abstract function getDatabaseNames(): array;
	abstract function dumpDatabase(string $name, string $target);
	abstract function getExcluded(): array;

	private function cleanup(string $dir) {
		if(!file_exists($dir)) {
			return;
		}
		
		$rm = "rm ".escapeshellarg($dir)." -rvf";
		echo $rm.PHP_EOL;
		exec($rm);
	}
	
	private function honorRetention() {
		if($this->job->hasRetention("daily")) {
			$this->honorRetentionDaily();
		}
	}
	
	private function honorRetentionDaily() {
		$delete = array();
		$nowJulian = $this->date->toInt() ;
		$uptoJulian = $nowJulian-$this->job->getRetention("daily");
		foreach(glob($this->job->getStorage()."/*") as $value) {
			if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", basename($value))) {
				continue;
			}
			$date = JulianDate::fromString(basename($value));
			if($date->toInt()<=$uptoJulian) {
				$delete[] = $value;
			}
		}
		if(empty($delete)) {
			return;
		}
		echo "Apply daily retention (".$this->job->getRetention("daily")." days)".PHP_EOL;
		foreach($delete as $key => $value) {
			echo "\tExpiring ".$value."...";
			exec("rm ".escapeshellarg($value)." -r");
			echo PHP_EOL;
					
		}
	}
	
	function run() {
		echo "Running ".$this->job->getName().PHP_EOL;
		$names = $this->getDatabaseNames();
		$temp = $this->job->getStorage()."/temp";
		$final = $this->job->getStorage()."/".$this->date->getFormat("Y-m-d");
		$this->cleanup($temp);
		$this->cleanup($final);
		mkdir($temp);
		foreach($names as $key => $value) {
			if(in_array($value, $this->getExcluded())) {
				continue;
			}
			if($this->job->hasInclude() && !in_array($value, $this->job->getInclude())) {
				echo "Skipping ".$value." (not included)".PHP_EOL;
				continue;
			}
			if($this->job->hasExclude() && in_array($value, $this->job->getExclude())) {
				echo "Skipping ".$value." (excluded)".PHP_EOL;
				continue;
			}
			echo "Dumping ".$value."...".PHP_EOL;
			$this->dumpDatabase($value, $temp);
		}
		rename($temp, $final);
		$this->honorRetention();
		echo PHP_EOL;
	}
}
#Imported from ./include/local/DumpPostgreSQL.php

/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */
class DumpPostgreSQL extends Dump {
	public function getDatabaseNames():array {
		$dbnames = array();
		$dsn = "pgsql:host=".$this->job->getHost().";dbname=postgres";
		$pdo = new PDO($dsn, $this->job->getUser(), $this->job->getPassword());
		$stmt = $pdo->prepare("select datname, usename from pg_database join pg_user ON (pg_database.datdba = pg_user.usesysid) order by datname");
		$stmt->execute();
		foreach($stmt as $key => $value) {
			$dbnames[] = $value[0];
		}
	return $dbnames;
	}

	public function dumpDatabase(string $name, string $target) {
		$temp = $target."/".$name.".temp.tar";
		$tempzip = $target."/".$name.".temp.tar.gz";
		$final = $target."/".$name.".tar.gz";
		$command[] = "pg_dump ".$name;
		$command[] = "-U".$this->job->getUser();
		$command[] = "-h".$this->job->getHost();
		$command[] = "-Ft";
		$command[] = "--no-password";
		$command[] = "> ".escapeshellarg($temp);
		$cmd = implode(" ", $command);
		echo $cmd.PHP_EOL;
		exec($cmd);
		$zip = "gzip ".escapeshellarg($temp);
		echo $zip.PHP_EOL;
		exec($zip);
		$mv = "mv ".escapeshellarg($tempzip)." ".escapeshellarg($final);
		echo $mv.PHP_EOL;
		exec($mv);
		echo PHP_EOL;
	}

	public function getExcluded(): array {
		return array("template0", "template1", "postgres");
	}

}
#Imported from ./include/local/DumpMySQL.php

/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */
class DumpMySQL extends Dump {
	private $extra;
	function __construct(JulianDate $date, DumpJob $job) {
		parent::__construct($date, $job);
		/**
		 * We store MySQL's username & password within a temporary .cnf-file.
		 * As it will be stored in the same directory as the job configuration
		 * file, no additional security issues occur, but we do not have to
		 * use MySQLs password on the command line.
		 */
		$this->extra = $job->getStorage()."/credentials.temp";
		$extra[] = "[client]";
		$extra[] = "host=".$this->job->getHost();
		$extra[] = "user=".$this->job->getUser();
		$extra[] = "password=".$this->job->getPassword();
		file_put_contents($this->extra, implode(PHP_EOL, $extra));
	}
	public function getDatabaseNames():array {
		$dbnames = array();
		$dsn = "mysql:host=".$this->job->getHost().";dbname=information_schema";
		#$pdo = new PDO($dsn, $this->job->getUser(), $this->job->getPassword());
		$pdo = new PDO($dsn, $this->job->getUser(), $this->job->getPassword());
		$stmt = $pdo->prepare("select SCHEMA_NAME from information_schema.SCHEMATA order by SCHEMA_NAME");
		$stmt->execute();
		foreach($stmt as $key => $value) {
			$dbnames[] = $value[0];
		}
	return $dbnames;
	}

	public function dumpDatabase(string $name, string $target) {
		$temp = $target."/".$name.".temp.sql";
		$tempzip = $target."/".$name.".temp.sql.gz";
		$final = $target."/".$name.".sql.gz";
		$command[] = "mysqldump ";
		$command[] = "--defaults-extra-file=".escapeshellarg($this->extra);
		$command[] = $name;
		
		$command[] = "> ".escapeshellarg($temp);
		$cmd = implode(" ", $command);
		echo $cmd.PHP_EOL;
		$out = "";
		$returnCode = 0;
		exec($cmd, $out, $returnCode);
		if($returnCode>=2) {
			echo "Error dumping ".$name.PHP_EOL.PHP_EOL;
			unlink($temp);
			return;
		}
		$zip = "gzip ".escapeshellarg($temp);
		echo $zip.PHP_EOL;
		exec($zip);
		$mv = "mv ".escapeshellarg($tempzip)." ".escapeshellarg($final);
		echo $mv.PHP_EOL;
		exec($mv);
		echo PHP_EOL;
	}

	public function getExcluded(): array {
		return array();
	}
	
	public function __destruct() {
		unlink($this->extra);
	}
}
#Imported from ./include/local/DumpSQLite.php


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DumpSQLite
 *
 * @author hm
 */
class DumpSQLite extends Dump {
	public function dumpDatabase(string $name, string $target) {
		$temp = $target."/".$name.".temp.sql";
		$tempzip = $target."/".$name.".temp.sql.gz";
		$final = $target."/".$name.".sql.gz";
		$command[] = "echo '.dump' |";
		$command[] = "sqlite3 ".escapeshellarg($this->job->getHost());
		$command[] = "> ".escapeshellarg($temp);
		#$command[] = "-h".$this->job->getHost();
		#$command[] = "-Ft";
		#$command[] = "--no-password";
		#$command[] = "> ".escapeshellarg($temp);
		$cmd = implode(" ", $command);
		echo $cmd.PHP_EOL;
		exec($cmd);
		$zip = "gzip ".escapeshellarg($temp);
		echo $zip.PHP_EOL;
		exec($zip);
		$mv = "mv ".escapeshellarg($tempzip)." ".escapeshellarg($final);
		echo $mv.PHP_EOL;
		exec($mv);
		echo PHP_EOL;

	}

	public function getDatabaseNames(): array {
		$basename = basename($this->job->getHost());
		$basename = preg_replace("/\.sqlite$/", "", $basename);
		return array($basename);
	}

	public function getExcluded(): array {
		return array();
	}

}
#Imported from ./include/local/DumpJobs.php

/**
 * @copyright (c) 2019, Claus-Christoph Küthe
 * @author Claus-Christoph Küthe <dumpomatic@vm01.telton.de>
 * @license GPLv3
 */
class DumpJobs {
	private $date;
	private $configs = [];
	private $failed = [];
	private $jobs = [];
	static function fromYAML($file): DumpJobs {
		if(is_dir($file)) {
			throw new InvalidArgumentException("path '".$file."' is a directory.");
		}
		if(!file_exists($file)) {
			throw new InvalidArgumentException("file '".$file."' does not exist.");
		}
		$parsed = yaml_parse_file($file);
		if(empty($parsed) or ! is_array($parsed)) {
			throw new InvalidArgumentException("file '".$file."' could not be parsed.");
		}
		$jobs = new DumpJobs();
		/**
		 * @todo: Rework for testing
		 * This is reasonable for production use, but defeats testing, as it
		 * can never fail.
		 */
		foreach($parsed as $key => $value) {
			try {
				$jobs->jobs[] = DumpJob::fromArray($value);
				$jobs->date = new JulianDate();
			} catch (Exception $e) {
				$jobs->failed[] = $file." ".$e->getMessage();
			}
		}
	return $jobs;
	}
	/*
	function __construct(array $argv) {
		$this->date = new Date();
		if(!isset($argv[1])) {
			echo "Configuration parameter missing.".PHP_EOL;
			die();
		}
		if(!file_exists($argv[1])) {
			echo "Configuration '".$argv[1]."' not available.".PHP_EOL;
			die();
		}
		if(is_file($argv[1])) {
			$this->configs[] = new Config($argv[1]);
			return;
		}
		foreach(glob($argv[1]."/*.conf") as $value) {
			try {
				$this->configs[] = new Config($value);
			} catch (Exception $e) {
				echo $e->getMessage().PHP_EOL;
				$this->failed[] = basename($value);
			}
		}
	}
	*/
	private function runJob(DumpJob $job) {
		if($job->getDriver()=="pgsql") {
			$backup = new DumpPostgreSQL($this->date, $job);
			$backup->run();
		return;
		}
		if($job->getDriver()=="mysql") {
			$backup = new DumpMySQL($this->date, $job);
			$backup->run();
		return;
		}
		if($job->getDriver()=="sqlite") {
			$backup = new DumpSQLite($this->date, $job);
			$backup->run();
		return;
		}

	}
			
	function run() {
		foreach($this->jobs as $value) {
			try {
				$this->runJob($value);
			} catch (Exception $e) {
				echo "Configuration '".$value->getName()."' failed:".PHP_EOL;
				$this->failed[] = $value->getName().":".$e->getMessage();
				echo $e->getMessage().PHP_EOL.PHP_EOL;
			}
		}
		if(empty($this->failed)) {
			return;
		}
		echo "Some jobs failed:".PHP_EOL;
		foreach($this->failed as $key => $value) {
			echo "\t".$value.PHP_EOL;
		}
	}
}
#/Include

$jobs = DumpJobs::fromYAML($argv[1]);
$jobs->run();
