<?php



class AttachmentMapBuilder implements MapBuilder {

	
	const CLASS_NAME = 'lib.model.map.AttachmentMapBuilder';

	
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
		$this->dbMap = Propel::getDatabaseMap(AttachmentPeer::DATABASE_NAME);

		$tMap = $this->dbMap->addTable(AttachmentPeer::TABLE_NAME);
		$tMap->setPhpName('Attachment');
		$tMap->setClassname('Attachment');

		$tMap->setUseIdGenerator(true);

		$tMap->addPrimaryKey('ID', 'Id', 'INTEGER', true, null);

		$tMap->addForeignKey('ARTICLE_ID', 'ArticleId', 'INTEGER', 'article', 'ID', false, null);

		$tMap->addColumn('NAME', 'Name', 'VARCHAR', false, 255);

		$tMap->addColumn('FILE', 'File', 'VARCHAR', false, 255);

	} 
} 