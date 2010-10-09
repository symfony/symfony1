<?php



class AuthorArticleMapBuilder implements MapBuilder {

	
	const CLASS_NAME = 'lib.model.map.AuthorArticleMapBuilder';

	
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
		$this->dbMap = Propel::getDatabaseMap(AuthorArticlePeer::DATABASE_NAME);

		$tMap = $this->dbMap->addTable(AuthorArticlePeer::TABLE_NAME);
		$tMap->setPhpName('AuthorArticle');
		$tMap->setClassname('AuthorArticle');

		$tMap->setUseIdGenerator(false);

		$tMap->addForeignPrimaryKey('AUTHOR_ID', 'AuthorId', 'INTEGER' , 'author', 'ID', true, null);

		$tMap->addForeignPrimaryKey('ARTICLE_ID', 'ArticleId', 'INTEGER' , 'article', 'ID', true, null);

	} 
} 