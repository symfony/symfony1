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

		$tMap->setUseIdGenerator(true);

		$tMap->addForeignKey('AUTHOR_ID', 'AuthorId', 'int', CreoleTypes::INTEGER, 'author', 'ID', false, null);

		$tMap->addForeignKey('ARTICLE_ID', 'ArticleId', 'int', CreoleTypes::INTEGER, 'article', 'ID', false, null);

		$tMap->addPrimaryKey('ID', 'Id', 'int', CreoleTypes::INTEGER, true, null);

	} 
} 