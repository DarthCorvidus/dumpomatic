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