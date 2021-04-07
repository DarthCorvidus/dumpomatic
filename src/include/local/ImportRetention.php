<?php
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
