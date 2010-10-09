<?php



class CategoryMapBuilder implements MapBuilder {

	
	const CLASS_NAME = 'lib.model.map.CategoryMapBuilder';

	
	private $dbMap;

	
	public function isBuilt()
	{
		return ($this->dbMap !== null);
	}

	
	public function getDatabaseMap()
	{
		return $this->dbMap;
	}

	
	public function doBuild()
	{
		$this->dbMap = Propel::getDatabaseMap(CategoryPeer::DATABASE_NAME);

		$tMap = $this->dbMap->addTable(CategoryPeer::TABLE_NAME);
		$tMap->setPhpName('Category');
		$tMap->setClassname('Category');

		$tMap->setUseIdGenerator(true);

		$tMap->addPrimaryKey('ID', 'Id', 'INTEGER', true, null);

		$tMap->addColumn('NAME', 'Name', 'VARCHAR', false, 255);

	} 
} 