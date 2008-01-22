<?php



class AuthorArticleMapBuilder {

	
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
		$this->dbMap = Propel::getDatabaseMap('propel');

		$tMap = $this->dbMap->addTable('author_article');
		$tMap->setPhpName('AuthorArticle');

		$tMap->setUseIdGenerator(false);

		$tMap->addForeignPrimaryKey('AUTHOR_ID', 'AuthorId', 'int' , CreoleTypes::INTEGER, 'author', 'ID', true, null);

		$tMap->addForeignPrimaryKey('ARTICLE_ID', 'ArticleId', 'int' , CreoleTypes::INTEGER, 'article', 'ID', true, null);

	} 
} 