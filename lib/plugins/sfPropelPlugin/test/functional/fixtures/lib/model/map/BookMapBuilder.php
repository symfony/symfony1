<?php



class BookMapBuilder implements MapBuilder {

	
	const CLASS_NAME = 'lib.model.map.BookMapBuilder';

	
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
		$this->dbMap = Propel::getDatabaseMap(BookPeer::DATABASE_NAME);

		$tMap = $this->dbMap->addTable(BookPeer::TABLE_NAME);
		$tMap->setPhpName('Book');
		$tMap->setClassname('Book');

		$tMap->setUseIdGenerator(true);

		$tMap->addPrimaryKey('ID', 'Id', 'INTEGER', true, null);

		$tMap->addColumn('NAME', 'Name', 'VARCHAR', false, 255);

	} 
} 