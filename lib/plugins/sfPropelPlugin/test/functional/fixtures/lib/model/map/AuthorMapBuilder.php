<?php



class AuthorMapBuilder implements MapBuilder {

	
	const CLASS_NAME = 'lib.model.map.AuthorMapBuilder';

	
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
		$this->dbMap = Propel::getDatabaseMap(AuthorPeer::DATABASE_NAME);

		$tMap = $this->dbMap->addTable(AuthorPeer::TABLE_NAME);
		$tMap->setPhpName('Author');
		$tMap->setClassname('Author');

		$tMap->setUseIdGenerator(true);

		$tMap->addPrimaryKey('ID', 'Id', 'INTEGER', true, null);

		$tMap->addColumn('NAME', 'Name', 'VARCHAR', false, 255);

	} 
} 