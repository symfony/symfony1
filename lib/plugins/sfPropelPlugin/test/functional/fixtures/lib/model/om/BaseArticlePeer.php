<?php


abstract class BaseArticlePeer {

	
	const DATABASE_NAME = 'propel';

	
	const TABLE_NAME = 'article';

	
	const CLASS_DEFAULT = 'lib.model.Article';

	
	const NUM_COLUMNS = 9;

	
	const NUM_LAZY_LOAD_COLUMNS = 0;

	
	const ID = 'article.ID';

	
	const TITLE = 'article.TITLE';

	
	const BODY = 'article.BODY';

	
	const ONLINE = 'article.ONLINE';

	
	const EXCERPT = 'article.EXCERPT';

	
	const CATEGORY_ID = 'article.CATEGORY_ID';

	
	const CREATED_AT = 'article.CREATED_AT';

	
	const END_DATE = 'article.END_DATE';

	
	const BOOK_ID = 'article.BOOK_ID';

	
	public static $instances = array();

	
	private static $mapBuilder = null;

	
	private static $fieldNames = array (
		BasePeer::TYPE_PHPNAME => array ('Id', 'Title', 'Body', 'Online', 'Excerpt', 'CategoryId', 'CreatedAt', 'EndDate', 'BookId', ),
		BasePeer::TYPE_STUDLYPHPNAME => array ('id', 'title', 'body', 'online', 'excerpt', 'categoryId', 'createdAt', 'endDate', 'bookId', ),
		BasePeer::TYPE_COLNAME => array (self::ID, self::TITLE, self::BODY, self::ONLINE, self::EXCERPT, self::CATEGORY_ID, self::CREATED_AT, self::END_DATE, self::BOOK_ID, ),
		BasePeer::TYPE_FIELDNAME => array ('id', 'title', 'body', 'Online', 'excerpt', 'category_id', 'created_at', 'end_date', 'book_id', ),
		BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, )
	);

	
	private static $fieldKeys = array (
		BasePeer::TYPE_PHPNAME => array ('Id' => 0, 'Title' => 1, 'Body' => 2, 'Online' => 3, 'Excerpt' => 4, 'CategoryId' => 5, 'CreatedAt' => 6, 'EndDate' => 7, 'BookId' => 8, ),
		BasePeer::TYPE_STUDLYPHPNAME => array ('id' => 0, 'title' => 1, 'body' => 2, 'online' => 3, 'excerpt' => 4, 'categoryId' => 5, 'createdAt' => 6, 'endDate' => 7, 'bookId' => 8, ),
		BasePeer::TYPE_COLNAME => array (self::ID => 0, self::TITLE => 1, self::BODY => 2, self::ONLINE => 3, self::EXCERPT => 4, self::CATEGORY_ID => 5, self::CREATED_AT => 6, self::END_DATE => 7, self::BOOK_ID => 8, ),
		BasePeer::TYPE_FIELDNAME => array ('id' => 0, 'title' => 1, 'body' => 2, 'Online' => 3, 'excerpt' => 4, 'category_id' => 5, 'created_at' => 6, 'end_date' => 7, 'book_id' => 8, ),
		BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, )
	);

	
	public static function getMapBuilder()
	{
		if (self::$mapBuilder === null) {
			self::$mapBuilder = new ArticleMapBuilder();
		}
		return self::$mapBuilder;
	}
	
	static public function translateFieldName($name, $fromType, $toType)
	{
		$toNames = self::getFieldNames($toType);
		$key = isset(self::$fieldKeys[$fromType][$name]) ? self::$fieldKeys[$fromType][$name] : null;
		if ($key === null) {
			throw new PropelException("'$name' could not be found in the field names of type '$fromType'. These are: " . print_r(self::$fieldKeys[$fromType], true));
		}
		return $toNames[$key];
	}

	

	static public function getFieldNames($type = BasePeer::TYPE_PHPNAME)
	{
		if (!array_key_exists($type, self::$fieldNames)) {
			throw new PropelException('Method getFieldNames() expects the parameter $type to be one of the class constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME, BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM. ' . $type . ' was given.');
		}
		return self::$fieldNames[$type];
	}

	
	public static function alias($alias, $column)
	{
		return str_replace(ArticlePeer::TABLE_NAME.'.', $alias.'.', $column);
	}

	
	public static function addSelectColumns(Criteria $criteria)
	{

		$criteria->addSelectColumn(ArticlePeer::ID);

		$criteria->addSelectColumn(ArticlePeer::TITLE);

		$criteria->addSelectColumn(ArticlePeer::BODY);

		$criteria->addSelectColumn(ArticlePeer::ONLINE);

		$criteria->addSelectColumn(ArticlePeer::EXCERPT);

		$criteria->addSelectColumn(ArticlePeer::CATEGORY_ID);

		$criteria->addSelectColumn(ArticlePeer::CREATED_AT);

		$criteria->addSelectColumn(ArticlePeer::END_DATE);

		$criteria->addSelectColumn(ArticlePeer::BOOK_ID);

	}

	
	public static function doCount(Criteria $criteria, $distinct = false, PropelPDO $con = null)
	{
				$criteria = clone $criteria;

								$criteria->setPrimaryTableName(ArticlePeer::TABLE_NAME);

		if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->setDistinct();
		}

		if (!$criteria->hasSelectClause()) {
			ArticlePeer::addSelectColumns($criteria);
		}

		$criteria->clearOrderByColumns(); 		$criteria->setDbName(self::DATABASE_NAME); 
		if ($con === null) {
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}


    foreach (sfMixer::getCallables('BaseArticlePeer:doCount:doCount') as $callable)
    {
      call_user_func($callable, 'BaseArticlePeer', $criteria, $con);
    }


				$stmt = BasePeer::doCount($criteria, $con);

		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$count = (int) $row[0];
		} else {
			$count = 0; 		}
		$stmt->closeCursor();
		return $count;
	}
	
	public static function doSelectOne(Criteria $criteria, PropelPDO $con = null)
	{
		$critcopy = clone $criteria;
		$critcopy->setLimit(1);
		$objects = ArticlePeer::doSelect($critcopy, $con);
		if ($objects) {
			return $objects[0];
		}
		return null;
	}
	
	public static function doSelect(Criteria $criteria, PropelPDO $con = null)
	{
		return ArticlePeer::populateObjects(ArticlePeer::doSelectStmt($criteria, $con));
	}
	
	public static function doSelectStmt(Criteria $criteria, PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseArticlePeer:doSelectStmt:doSelectStmt') as $callable)
    {
      call_user_func($callable, 'BaseArticlePeer', $criteria, $con);
    }


		if ($con === null) {
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		if (!$criteria->hasSelectClause()) {
			$criteria = clone $criteria;
			ArticlePeer::addSelectColumns($criteria);
		}

				$criteria->setDbName(self::DATABASE_NAME);

				return BasePeer::doSelect($criteria, $con);
	}
	
	public static function addInstanceToPool(Article $obj, $key = null)
	{
		if (Propel::isInstancePoolingEnabled()) {
			if ($key === null) {
				$key = (string) $obj->getId();
			} 			self::$instances[$key] = $obj;
		}
	}

	
	public static function removeInstanceFromPool($value)
	{
		if (Propel::isInstancePoolingEnabled() && $value !== null) {
			if (is_object($value) && $value instanceof Article) {
				$key = (string) $value->getId();
			} elseif (is_scalar($value)) {
								$key = (string) $value;
			} else {
				$e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or Article object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value,true)));
				throw $e;
			}

			unset(self::$instances[$key]);
		}
	} 
	
	public static function getInstanceFromPool($key)
	{
		if (Propel::isInstancePoolingEnabled()) {
			if (isset(self::$instances[$key])) {
				return self::$instances[$key];
			}
		}
		return null; 	}
	
	
	public static function clearInstancePool()
	{
		self::$instances = array();
	}
	
	
	public static function getPrimaryKeyHashFromRow($row, $startcol = 0)
	{
				if ($row[$startcol + 0] === null) {
			return null;
		}
		return (string) $row[$startcol + 0];
	}

	
	public static function populateObjects(PDOStatement $stmt)
	{
		$results = array();
	
				$cls = ArticlePeer::getOMClass();
		$cls = substr('.'.$cls, strrpos('.'.$cls, '.') + 1);
				while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key = ArticlePeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj = ArticlePeer::getInstanceFromPool($key))) {
																$results[] = $obj;
			} else {
		
				$obj = new $cls();
				$obj->hydrate($row);
				$results[] = $obj;
				ArticlePeer::addInstanceToPool($obj, $key);
			} 		}
		$stmt->closeCursor();
		return $results;
	}

	
	public static function doCountJoinCategory(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
				$criteria = clone $criteria;

								$criteria->setPrimaryTableName(ArticlePeer::TABLE_NAME);

		if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->setDistinct();
		}

		if (!$criteria->hasSelectClause()) {
			ArticlePeer::addSelectColumns($criteria);
		}

		$criteria->clearOrderByColumns(); 
				$criteria->setDbName(self::DATABASE_NAME);

		if ($con === null) {
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		$criteria->addJoin(array(ArticlePeer::CATEGORY_ID,), array(CategoryPeer::ID,), $join_behavior);


    foreach (sfMixer::getCallables('BaseArticlePeer:doCount:doCount') as $callable)
    {
      call_user_func($callable, 'BaseArticlePeer', $criteria, $con);
    }


		$stmt = BasePeer::doCount($criteria, $con);

		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$count = (int) $row[0];
		} else {
			$count = 0; 		}
		$stmt->closeCursor();
		return $count;
	}


	
	public static function doCountJoinBook(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
				$criteria = clone $criteria;

								$criteria->setPrimaryTableName(ArticlePeer::TABLE_NAME);

		if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->setDistinct();
		}

		if (!$criteria->hasSelectClause()) {
			ArticlePeer::addSelectColumns($criteria);
		}

		$criteria->clearOrderByColumns(); 
				$criteria->setDbName(self::DATABASE_NAME);

		if ($con === null) {
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		$criteria->addJoin(array(ArticlePeer::BOOK_ID,), array(BookPeer::ID,), $join_behavior);


    foreach (sfMixer::getCallables('BaseArticlePeer:doCount:doCount') as $callable)
    {
      call_user_func($callable, 'BaseArticlePeer', $criteria, $con);
    }


		$stmt = BasePeer::doCount($criteria, $con);

		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$count = (int) $row[0];
		} else {
			$count = 0; 		}
		$stmt->closeCursor();
		return $count;
	}


	
	public static function doSelectJoinCategory(Criteria $c, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{

    foreach (sfMixer::getCallables('BaseArticlePeer:doSelectJoin:doSelectJoin') as $callable)
    {
      call_user_func($callable, 'BaseArticlePeer', $c, $con);
    }


		$c = clone $c;

				if ($c->getDbName() == Propel::getDefaultDB()) {
			$c->setDbName(self::DATABASE_NAME);
		}

		ArticlePeer::addSelectColumns($c);
		$startcol = (ArticlePeer::NUM_COLUMNS - ArticlePeer::NUM_LAZY_LOAD_COLUMNS);
		CategoryPeer::addSelectColumns($c);

		$c->addJoin(array(ArticlePeer::CATEGORY_ID,), array(CategoryPeer::ID,), $join_behavior);
		$stmt = BasePeer::doSelect($c, $con);
		$results = array();

		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key1 = ArticlePeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj1 = ArticlePeer::getInstanceFromPool($key1))) {
															} else {

				$omClass = ArticlePeer::getOMClass();

				$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
				$obj1 = new $cls();
				$obj1->hydrate($row);
				ArticlePeer::addInstanceToPool($obj1, $key1);
			} 
			$key2 = CategoryPeer::getPrimaryKeyHashFromRow($row, $startcol);
			if ($key2 !== null) {
				$obj2 = CategoryPeer::getInstanceFromPool($key2);
				if (!$obj2) {

					$omClass = CategoryPeer::getOMClass();

					$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
					$obj2 = new $cls();
					$obj2->hydrate($row, $startcol);
					CategoryPeer::addInstanceToPool($obj2, $key2);
				} 
								$obj2->addArticle($obj1);

			} 
			$results[] = $obj1;
		}
		$stmt->closeCursor();
		return $results;
	}


	
	public static function doSelectJoinBook(Criteria $c, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		$c = clone $c;

				if ($c->getDbName() == Propel::getDefaultDB()) {
			$c->setDbName(self::DATABASE_NAME);
		}

		ArticlePeer::addSelectColumns($c);
		$startcol = (ArticlePeer::NUM_COLUMNS - ArticlePeer::NUM_LAZY_LOAD_COLUMNS);
		BookPeer::addSelectColumns($c);

		$c->addJoin(array(ArticlePeer::BOOK_ID,), array(BookPeer::ID,), $join_behavior);
		$stmt = BasePeer::doSelect($c, $con);
		$results = array();

		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key1 = ArticlePeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj1 = ArticlePeer::getInstanceFromPool($key1))) {
															} else {

				$omClass = ArticlePeer::getOMClass();

				$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
				$obj1 = new $cls();
				$obj1->hydrate($row);
				ArticlePeer::addInstanceToPool($obj1, $key1);
			} 
			$key2 = BookPeer::getPrimaryKeyHashFromRow($row, $startcol);
			if ($key2 !== null) {
				$obj2 = BookPeer::getInstanceFromPool($key2);
				if (!$obj2) {

					$omClass = BookPeer::getOMClass();

					$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
					$obj2 = new $cls();
					$obj2->hydrate($row, $startcol);
					BookPeer::addInstanceToPool($obj2, $key2);
				} 
								$obj2->addArticle($obj1);

			} 
			$results[] = $obj1;
		}
		$stmt->closeCursor();
		return $results;
	}


	
	public static function doCountJoinAll(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
				$criteria = clone $criteria;

								$criteria->setPrimaryTableName(ArticlePeer::TABLE_NAME);

		if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->setDistinct();
		}

		if (!$criteria->hasSelectClause()) {
			ArticlePeer::addSelectColumns($criteria);
		}

		$criteria->clearOrderByColumns(); 
				$criteria->setDbName(self::DATABASE_NAME);

		if ($con === null) {
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		$criteria->addJoin(array(ArticlePeer::CATEGORY_ID,), array(CategoryPeer::ID,), $join_behavior);
		$criteria->addJoin(array(ArticlePeer::BOOK_ID,), array(BookPeer::ID,), $join_behavior);

    foreach (sfMixer::getCallables('BaseArticlePeer:doCount:doCount') as $callable)
    {
      call_user_func($callable, 'BaseArticlePeer', $criteria, $con);
    }


		$stmt = BasePeer::doCount($criteria, $con);

		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$count = (int) $row[0];
		} else {
			$count = 0; 		}
		$stmt->closeCursor();
		return $count;
	}

	
	public static function doSelectJoinAll(Criteria $c, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{

    foreach (sfMixer::getCallables('BaseArticlePeer:doSelectJoinAll:doSelectJoinAll') as $callable)
    {
      call_user_func($callable, 'BaseArticlePeer', $c, $con);
    }


		$c = clone $c;

				if ($c->getDbName() == Propel::getDefaultDB()) {
			$c->setDbName(self::DATABASE_NAME);
		}

		ArticlePeer::addSelectColumns($c);
		$startcol2 = (ArticlePeer::NUM_COLUMNS - ArticlePeer::NUM_LAZY_LOAD_COLUMNS);

		CategoryPeer::addSelectColumns($c);
		$startcol3 = $startcol2 + (CategoryPeer::NUM_COLUMNS - CategoryPeer::NUM_LAZY_LOAD_COLUMNS);

		BookPeer::addSelectColumns($c);
		$startcol4 = $startcol3 + (BookPeer::NUM_COLUMNS - BookPeer::NUM_LAZY_LOAD_COLUMNS);

		$c->addJoin(array(ArticlePeer::CATEGORY_ID,), array(CategoryPeer::ID,), $join_behavior);
		$c->addJoin(array(ArticlePeer::BOOK_ID,), array(BookPeer::ID,), $join_behavior);
		$stmt = BasePeer::doSelect($c, $con);
		$results = array();

		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key1 = ArticlePeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj1 = ArticlePeer::getInstanceFromPool($key1))) {
															} else {
				$omClass = ArticlePeer::getOMClass();

				$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
				$obj1 = new $cls();
				$obj1->hydrate($row);
				ArticlePeer::addInstanceToPool($obj1, $key1);
			} 
			
			$key2 = CategoryPeer::getPrimaryKeyHashFromRow($row, $startcol2);
			if ($key2 !== null) {
				$obj2 = CategoryPeer::getInstanceFromPool($key2);
				if (!$obj2) {

					$omClass = CategoryPeer::getOMClass();


					$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
					$obj2 = new $cls();
					$obj2->hydrate($row, $startcol2);
					CategoryPeer::addInstanceToPool($obj2, $key2);
				} 
								$obj2->addArticle($obj1);
			} 
			
			$key3 = BookPeer::getPrimaryKeyHashFromRow($row, $startcol3);
			if ($key3 !== null) {
				$obj3 = BookPeer::getInstanceFromPool($key3);
				if (!$obj3) {

					$omClass = BookPeer::getOMClass();


					$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
					$obj3 = new $cls();
					$obj3->hydrate($row, $startcol3);
					BookPeer::addInstanceToPool($obj3, $key3);
				} 
								$obj3->addArticle($obj1);
			} 
			$results[] = $obj1;
		}
		$stmt->closeCursor();
		return $results;
	}


	
	public static function doCountJoinAllExceptCategory(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
				$criteria = clone $criteria;

		if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->setDistinct();
		}

		if (!$criteria->hasSelectClause()) {
			ArticlePeer::addSelectColumns($criteria);
		}

		$criteria->clearOrderByColumns(); 
				$criteria->setDbName(self::DATABASE_NAME);

		if ($con === null) {
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}
	
				$criteria->addJoin(array(ArticlePeer::BOOK_ID,), array(BookPeer::ID,), $join_behavior);

    foreach (sfMixer::getCallables('BaseArticlePeer:doCount:doCount') as $callable)
    {
      call_user_func($callable, 'BaseArticlePeer', $criteria, $con);
    }


		$stmt = BasePeer::doCount($criteria, $con);

		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$count = (int) $row[0];
		} else {
			$count = 0; 		}
		$stmt->closeCursor();
		return $count;
	}


	
	public static function doCountJoinAllExceptBook(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
				$criteria = clone $criteria;

		if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->setDistinct();
		}

		if (!$criteria->hasSelectClause()) {
			ArticlePeer::addSelectColumns($criteria);
		}

		$criteria->clearOrderByColumns(); 
				$criteria->setDbName(self::DATABASE_NAME);

		if ($con === null) {
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}
	
				$criteria->addJoin(array(ArticlePeer::CATEGORY_ID,), array(CategoryPeer::ID,), $join_behavior);

    foreach (sfMixer::getCallables('BaseArticlePeer:doCount:doCount') as $callable)
    {
      call_user_func($callable, 'BaseArticlePeer', $criteria, $con);
    }


		$stmt = BasePeer::doCount($criteria, $con);

		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$count = (int) $row[0];
		} else {
			$count = 0; 		}
		$stmt->closeCursor();
		return $count;
	}


	
	public static function doSelectJoinAllExceptCategory(Criteria $c, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{

    foreach (sfMixer::getCallables('BaseArticlePeer:doSelectJoinAllExcept:doSelectJoinAllExcept') as $callable)
    {
      call_user_func($callable, 'BaseArticlePeer', $c, $con);
    }


		$c = clone $c;

								if ($c->getDbName() == Propel::getDefaultDB()) {
			$c->setDbName(self::DATABASE_NAME);
		}

		ArticlePeer::addSelectColumns($c);
		$startcol2 = (ArticlePeer::NUM_COLUMNS - ArticlePeer::NUM_LAZY_LOAD_COLUMNS);

		BookPeer::addSelectColumns($c);
		$startcol3 = $startcol2 + (BookPeer::NUM_COLUMNS - BookPeer::NUM_LAZY_LOAD_COLUMNS);

				$c->addJoin(array(ArticlePeer::BOOK_ID,), array(BookPeer::ID,), $join_behavior);

		$stmt = BasePeer::doSelect($c, $con);
		$results = array();

		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key1 = ArticlePeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj1 = ArticlePeer::getInstanceFromPool($key1))) {
															} else {
				$omClass = ArticlePeer::getOMClass();

				$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
				$obj1 = new $cls();
				$obj1->hydrate($row);
				ArticlePeer::addInstanceToPool($obj1, $key1);
			} 
				
				$key2 = BookPeer::getPrimaryKeyHashFromRow($row, $startcol2);
				if ($key2 !== null) {
					$obj2 = BookPeer::getInstanceFromPool($key2);
					if (!$obj2) {
	
						$omClass = BookPeer::getOMClass();


					$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
					$obj2 = new $cls();
					$obj2->hydrate($row, $startcol2);
					BookPeer::addInstanceToPool($obj2, $key2);
				} 
								$obj2->addArticle($obj1);

			} 
			$results[] = $obj1;
		}
		$stmt->closeCursor();
		return $results;
	}


	
	public static function doSelectJoinAllExceptBook(Criteria $c, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		$c = clone $c;

								if ($c->getDbName() == Propel::getDefaultDB()) {
			$c->setDbName(self::DATABASE_NAME);
		}

		ArticlePeer::addSelectColumns($c);
		$startcol2 = (ArticlePeer::NUM_COLUMNS - ArticlePeer::NUM_LAZY_LOAD_COLUMNS);

		CategoryPeer::addSelectColumns($c);
		$startcol3 = $startcol2 + (CategoryPeer::NUM_COLUMNS - CategoryPeer::NUM_LAZY_LOAD_COLUMNS);

				$c->addJoin(array(ArticlePeer::CATEGORY_ID,), array(CategoryPeer::ID,), $join_behavior);

		$stmt = BasePeer::doSelect($c, $con);
		$results = array();

		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key1 = ArticlePeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj1 = ArticlePeer::getInstanceFromPool($key1))) {
															} else {
				$omClass = ArticlePeer::getOMClass();

				$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
				$obj1 = new $cls();
				$obj1->hydrate($row);
				ArticlePeer::addInstanceToPool($obj1, $key1);
			} 
				
				$key2 = CategoryPeer::getPrimaryKeyHashFromRow($row, $startcol2);
				if ($key2 !== null) {
					$obj2 = CategoryPeer::getInstanceFromPool($key2);
					if (!$obj2) {
	
						$omClass = CategoryPeer::getOMClass();


					$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
					$obj2 = new $cls();
					$obj2->hydrate($row, $startcol2);
					CategoryPeer::addInstanceToPool($obj2, $key2);
				} 
								$obj2->addArticle($obj1);

			} 
			$results[] = $obj1;
		}
		$stmt->closeCursor();
		return $results;
	}


  static public function getUniqueColumnNames()
  {
    return array(array('title', 'category_id'));
  }
	
	public static function getTableMap()
	{
		return Propel::getDatabaseMap(self::DATABASE_NAME)->getTable(self::TABLE_NAME);
	}

	
	public static function getOMClass()
	{
		return ArticlePeer::CLASS_DEFAULT;
	}

	
	public static function doInsert($values, PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseArticlePeer:doInsert:pre') as $callable)
    {
      $ret = call_user_func($callable, 'BaseArticlePeer', $values, $con);
      if (false !== $ret)
      {
        return $ret;
      }
    }


		if ($con === null) {
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		if ($values instanceof Criteria) {
			$criteria = clone $values; 		} else {
			$criteria = $values->buildCriteria(); 		}

		if ($criteria->containsKey(ArticlePeer::ID) && $criteria->keyContainsValue(ArticlePeer::ID) ) {
			throw new PropelException('Cannot insert a value for auto-increment primary key ('.ArticlePeer::ID.')');
		}


				$criteria->setDbName(self::DATABASE_NAME);

		try {
									$con->beginTransaction();
			$pk = BasePeer::doInsert($criteria, $con);
			$con->commit();
		} catch(PropelException $e) {
			$con->rollBack();
			throw $e;
		}

		
    foreach (sfMixer::getCallables('BaseArticlePeer:doInsert:post') as $callable)
    {
      call_user_func($callable, 'BaseArticlePeer', $values, $con, $pk);
    }

    return $pk;
	}

	
	public static function doUpdate($values, PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseArticlePeer:doUpdate:pre') as $callable)
    {
      $ret = call_user_func($callable, 'BaseArticlePeer', $values, $con);
      if (false !== $ret)
      {
        return $ret;
      }
    }


		if ($con === null) {
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		$selectCriteria = new Criteria(self::DATABASE_NAME);

		if ($values instanceof Criteria) {
			$criteria = clone $values; 
			$comparison = $criteria->getComparison(ArticlePeer::ID);
			$selectCriteria->add(ArticlePeer::ID, $criteria->remove(ArticlePeer::ID), $comparison);

		} else { 			$criteria = $values->buildCriteria(); 			$selectCriteria = $values->buildPkeyCriteria(); 		}

				$criteria->setDbName(self::DATABASE_NAME);

		$ret = BasePeer::doUpdate($selectCriteria, $criteria, $con);
	

    foreach (sfMixer::getCallables('BaseArticlePeer:doUpdate:post') as $callable)
    {
      call_user_func($callable, 'BaseArticlePeer', $values, $con, $ret);
    }

    return $ret;
  }

	
	public static function doDeleteAll($con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		$affectedRows = 0; 		try {
									$con->beginTransaction();
			$affectedRows += BasePeer::doDeleteAll(ArticlePeer::TABLE_NAME, $con);
			$con->commit();
			return $affectedRows;
		} catch (PropelException $e) {
			$con->rollBack();
			throw $e;
		}
	}

	
	 public static function doDelete($values, PropelPDO $con = null)
	 {
		if ($con === null) {
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		if ($values instanceof Criteria) {
												ArticlePeer::clearInstancePool();

						$criteria = clone $values;
		} elseif ($values instanceof Article) {
						ArticlePeer::removeInstanceFromPool($values);
						$criteria = $values->buildPkeyCriteria();
		} else {
			


			$criteria = new Criteria(self::DATABASE_NAME);
			$criteria->add(ArticlePeer::ID, (array) $values, Criteria::IN);

			foreach ((array) $values as $singleval) {
								ArticlePeer::removeInstanceFromPool($singleval);
			}
		}

				$criteria->setDbName(self::DATABASE_NAME);

		$affectedRows = 0; 
		try {
									$con->beginTransaction();
			
			$affectedRows += BasePeer::doDelete($criteria, $con);

			$con->commit();
			return $affectedRows;
		} catch (PropelException $e) {
			$con->rollBack();
			throw $e;
		}
	}

	
	public static function doValidate(Article $obj, $cols = null)
	{
		$columns = array();

		if ($cols) {
			$dbMap = Propel::getDatabaseMap(ArticlePeer::DATABASE_NAME);
			$tableMap = $dbMap->getTable(ArticlePeer::TABLE_NAME);

			if (! is_array($cols)) {
				$cols = array($cols);
			}

			foreach ($cols as $colName) {
				if ($tableMap->containsColumn($colName)) {
					$get = 'get' . $tableMap->getColumn($colName)->getPhpName();
					$columns[$colName] = $obj->$get();
				}
			}
		} else {

		}

		$res =  BasePeer::doValidate(ArticlePeer::DATABASE_NAME, ArticlePeer::TABLE_NAME, $columns);
    if ($res !== true) {
        $request = sfContext::getInstance()->getRequest();
        foreach ($res as $failed) {
            $col = ArticlePeer::translateFieldname($failed->getColumn(), BasePeer::TYPE_COLNAME, BasePeer::TYPE_PHPNAME);
        }
    }

    return $res;
	}

	
	public static function retrieveByPK($pk, PropelPDO $con = null)
	{

		if (null !== ($obj = ArticlePeer::getInstanceFromPool((string) $pk))) {
			return $obj;
		}

		if ($con === null) {
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		$criteria = new Criteria(ArticlePeer::DATABASE_NAME);
		$criteria->add(ArticlePeer::ID, $pk);

		$v = ArticlePeer::doSelect($criteria, $con);

		return !empty($v) > 0 ? $v[0] : null;
	}

	
	public static function retrieveByPKs($pks, PropelPDO $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		$objs = null;
		if (empty($pks)) {
			$objs = array();
		} else {
			$criteria = new Criteria(ArticlePeer::DATABASE_NAME);
			$criteria->add(ArticlePeer::ID, $pks, Criteria::IN);
			$objs = ArticlePeer::doSelect($criteria, $con);
		}
		return $objs;
	}

} 

Propel::getDatabaseMap(BaseArticlePeer::DATABASE_NAME)->addTableBuilder(BaseArticlePeer::TABLE_NAME, BaseArticlePeer::getMapBuilder());

