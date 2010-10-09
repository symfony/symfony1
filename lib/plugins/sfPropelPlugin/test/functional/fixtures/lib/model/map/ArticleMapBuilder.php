<?php



class ArticleMapBuilder implements MapBuilder {

	
	const CLASS_NAME = 'lib.model.map.ArticleMapBuilder';

	
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
		$this->dbMap = Propel::getDatabaseMap(ArticlePeer::DATABASE_NAME);

		$tMap = $this->dbMap->addTable(ArticlePeer::TABLE_NAME);
		$tMap->setPhpName('Article');
		$tMap->setClassname('Article');

		$tMap->setUseIdGenerator(true);

		$tMap->addPrimaryKey('ID', 'Id', 'INTEGER', true, null);

		$tMap->addColumn('TITLE', 'Title', 'VARCHAR', true, 255);

		$tMap->addColumn('BODY', 'Body', 'LONGVARCHAR', false, null);

		$tMap->addColumn('ONLINE', 'Online', 'BOOLEAN', false, null);

		$tMap->addColumn('EXCERPT', 'Excerpt', 'VARCHAR', false, null);

		$tMap->addForeignKey('CATEGORY_ID', 'CategoryId', 'INTEGER', 'category', 'ID', true, null);

		$tMap->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', false, null);

		$tMap->addColumn('END_DATE', 'EndDate', 'TIMESTAMP', false, null);

		$tMap->addForeignKey('BOOK_ID', 'BookId', 'INTEGER', 'book', 'ID', false, null);

	} 
} 