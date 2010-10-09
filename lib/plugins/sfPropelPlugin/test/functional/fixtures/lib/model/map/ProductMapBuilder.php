<?php



class ProductMapBuilder implements MapBuilder {

	
	const CLASS_NAME = 'lib.model.map.ProductMapBuilder';

	
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
		$this->dbMap = Propel::getDatabaseMap(ProductPeer::DATABASE_NAME);

		$tMap = $this->dbMap->addTable(ProductPeer::TABLE_NAME);
		$tMap->setPhpName('Product');
		$tMap->setClassname('Product');

		$tMap->setUseIdGenerator(true);

		$tMap->addPrimaryKey('ID', 'Id', 'INTEGER', true, null);

		$tMap->addColumn('PRICE', 'Price', 'FLOAT', false, null);

	} 
} 