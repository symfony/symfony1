<?php



class ProductI18nMapBuilder implements MapBuilder {

	
	const CLASS_NAME = 'lib.model.map.ProductI18nMapBuilder';

	
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
		$this->dbMap = Propel::getDatabaseMap(ProductI18nPeer::DATABASE_NAME);

		$tMap = $this->dbMap->addTable(ProductI18nPeer::TABLE_NAME);
		$tMap->setPhpName('ProductI18n');
		$tMap->setClassname('ProductI18n');

		$tMap->setUseIdGenerator(false);

		$tMap->addForeignPrimaryKey('ID', 'Id', 'INTEGER' , 'product', 'ID', true, null);

		$tMap->addPrimaryKey('CULTURE', 'Culture', 'VARCHAR', true, 7);

		$tMap->addColumn('NAME', 'Name', 'VARCHAR', false, 50);

	} 
} 