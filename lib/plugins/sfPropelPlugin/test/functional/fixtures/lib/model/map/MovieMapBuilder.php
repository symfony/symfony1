<?php



class MovieMapBuilder implements MapBuilder {

	
	const CLASS_NAME = 'lib.model.map.MovieMapBuilder';

	
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
		$this->dbMap = Propel::getDatabaseMap(MoviePeer::DATABASE_NAME);

		$tMap = $this->dbMap->addTable(MoviePeer::TABLE_NAME);
		$tMap->setPhpName('Movie');
		$tMap->setClassname('Movie');

		$tMap->setUseIdGenerator(true);

		$tMap->addPrimaryKey('ID', 'Id', 'INTEGER', true, null);

		$tMap->addColumn('DIRECTOR', 'Director', 'VARCHAR', false, 255);

	} 
} 