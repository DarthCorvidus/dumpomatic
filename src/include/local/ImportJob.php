<?php
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
		$this->scalarValues["name"] = UserValue::asMandatory();
		
		$this->scalarValues["host"] = UserValue::asMandatory();
		
		$this->scalarValues["user"] = UserValue::asMandatory();

		$this->scalarValues["password"] = UserValue::asMandatory();

		
		$this->scalarValues["storage"] = UserValue::asMandatory();
		$this->scalarValues["storage"]->setValidate(new ValidateStorage());
		$this->scalarValues["storage"]->setConvert(new ConvertTrailingSlash());
		
		$this->scalarValues["driver"] = UserValue::asMandatory();
		$this->scalarValues["driver"]->setValidate(new ValidateDriver());
		
		$this->scalarLists["include"] = UserValue::asOptional();
		$this->scalarLists["exclude"] = UserValue::asOptional();
		
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

	public function getScalarListModel($name): UserValue {
		return $this->scalarLists[$name];
	}

	public function getScalarListNames(): array {
		return array_keys($this->scalarLists);
	}

	public function getScalarModel($name): UserValue {
		return $this->scalarValues[$name];
	}

	public function getScalarNames(): array {
		return array_keys($this->scalarValues);
	}

}