<?php



class MovieI18nMapBuilder implements MapBuilder {

	
	const CLASS_NAME = 'lib.model.map.MovieI18nMapBuilder';

	
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
		$this->dbMap = Propel::getDatabaseMap(MovieI18nPeer::DATABASE_NAME);

		$tMap = $this->dbMap->addTable(MovieI18nPeer::TABLE_NAME);
		$tMap->setPhpName('MovieI18n');
		$tMap->setClassname('MovieI18n');

		$tMap->setUseIdGenerator(false);

		$tMap->addForeignPrimaryKey('ID', 'Id', 'INTEGER' , 'movie', 'ID', true, null);

		$tMap->addPrimaryKey('CULTURE', 'Culture', 'VARCHAR', true, 7);

		$tMap->addColumn('TITLE', 'Title', 'VARCHAR', false, null);

	} 
} 