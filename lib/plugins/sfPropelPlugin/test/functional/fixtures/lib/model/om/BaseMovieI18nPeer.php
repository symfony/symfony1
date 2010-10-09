<?php


abstract class BaseMovieI18nPeer {

	
	const DATABASE_NAME = 'propel';

	
	const TABLE_NAME = 'movie_i18n';

	
	const CLASS_DEFAULT = 'lib.model.MovieI18n';

	
	const NUM_COLUMNS = 3;

	
	const NUM_LAZY_LOAD_COLUMNS = 0;

	
	const ID = 'movie_i18n.ID';

	
	const CULTURE = 'movie_i18n.CULTURE';

	
	const TITLE = 'movie_i18n.TITLE';

	
	public static $instances = array();

	
	private static $mapBuilder = null;

	
	private static $fieldNames = array (
		BasePeer::TYPE_PHPNAME => array ('Id', 'Culture', 'Title', ),
		BasePeer::TYPE_STUDLYPHPNAME => array ('id', 'culture', 'title', ),
		BasePeer::TYPE_COLNAME => array (self::ID, self::CULTURE, self::TITLE, ),
		BasePeer::TYPE_FIELDNAME => array ('id', 'culture', 'title', ),
		BasePeer::TYPE_NUM => array (0, 1, 2, )
	);

	
	private static $fieldKeys = array (
		BasePeer::TYPE_PHPNAME => array ('Id' => 0, 'Culture' => 1, 'Title' => 2, ),
		BasePeer::TYPE_STUDLYPHPNAME => array ('id' => 0, 'culture' => 1, 'title' => 2, ),
		BasePeer::TYPE_COLNAME => array (self::ID => 0, self::CULTURE => 1, self::TITLE => 2, ),
		BasePeer::TYPE_FIELDNAME => array ('id' => 0, 'culture' => 1, 'title' => 2, ),
		BasePeer::TYPE_NUM => array (0, 1, 2, )
	);

	
	public static function getMapBuilder()
	{
		if (self::$mapBuilder === null) {
			self::$mapBuilder = new MovieI18nMapBuilder();
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
		return str_replace(MovieI18nPeer::TABLE_NAME.'.', $alias.'.', $column);
	}

	
	public static function addSelectColumns(Criteria $criteria)
	{

		$criteria->addSelectColumn(MovieI18nPeer::ID);

		$criteria->addSelectColumn(MovieI18nPeer::CULTURE);

		$criteria->addSelectColumn(MovieI18nPeer::TITLE);

	}

	
	public static function doCount(Criteria $criteria, $distinct = false, PropelPDO $con = null)
	{
				$criteria = clone $criteria;

								$criteria->setPrimaryTableName(MovieI18nPeer::TABLE_NAME);

		if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->setDistinct();
		}

		if (!$criteria->hasSelectClause()) {
			MovieI18nPeer::addSelectColumns($criteria);
		}

		$criteria->clearOrderByColumns(); 		$criteria->setDbName(self::DATABASE_NAME); 
		if ($con === null) {
			$con = Propel::getConnection(MovieI18nPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}


    foreach (sfMixer::getCallables('BaseMovieI18nPeer:doCount:doCount') as $callable)
    {
      call_user_func($callable, 'BaseMovieI18nPeer', $criteria, $con);
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
		$objects = MovieI18nPeer::doSelect($critcopy, $con);
		if ($objects) {
			return $objects[0];
		}
		return null;
	}
	
	public static function doSelect(Criteria $criteria, PropelPDO $con = null)
	{
		return MovieI18nPeer::populateObjects(MovieI18nPeer::doSelectStmt($criteria, $con));
	}
	
	public static function doSelectStmt(Criteria $criteria, PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseMovieI18nPeer:doSelectStmt:doSelectStmt') as $callable)
    {
      call_user_func($callable, 'BaseMovieI18nPeer', $criteria, $con);
    }


		if ($con === null) {
			$con = Propel::getConnection(MovieI18nPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		if (!$criteria->hasSelectClause()) {
			$criteria = clone $criteria;
			MovieI18nPeer::addSelectColumns($criteria);
		}

				$criteria->setDbName(self::DATABASE_NAME);

				return BasePeer::doSelect($criteria, $con);
	}
	
	public static function addInstanceToPool(MovieI18n $obj, $key = null)
	{
		if (Propel::isInstancePoolingEnabled()) {
			if ($key === null) {
				$key = serialize(array((string) $obj->getId(), (string) $obj->getCulture()));
			} 			self::$instances[$key] = $obj;
		}
	}

	
	public static function removeInstanceFromPool($value)
	{
		if (Propel::isInstancePoolingEnabled() && $value !== null) {
			if (is_object($value) && $value instanceof MovieI18n) {
				$key = serialize(array((string) $value->getId(), (string) $value->getCulture()));
			} elseif (is_array($value) && count($value) === 2) {
								$key = serialize(array((string) $value[0], (string) $value[1]));
			} else {
				$e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or MovieI18n object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value,true)));
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
				if ($row[$startcol + 0] === null && $row[$startcol + 1] === null) {
			return null;
		}
		return serialize(array((string) $row[$startcol + 0], (string) $row[$startcol + 1]));
	}

	
	public static function populateObjects(PDOStatement $stmt)
	{
		$results = array();
	
				$cls = MovieI18nPeer::getOMClass();
		$cls = substr('.'.$cls, strrpos('.'.$cls, '.') + 1);
				while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key = MovieI18nPeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj = MovieI18nPeer::getInstanceFromPool($key))) {
																$results[] = $obj;
			} else {
		
				$obj = new $cls();
				$obj->hydrate($row);
				$results[] = $obj;
				MovieI18nPeer::addInstanceToPool($obj, $key);
			} 		}
		$stmt->closeCursor();
		return $results;
	}

	
	public static function doCountJoinMovie(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
				$criteria = clone $criteria;

								$criteria->setPrimaryTableName(MovieI18nPeer::TABLE_NAME);

		if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->setDistinct();
		}

		if (!$criteria->hasSelectClause()) {
			MovieI18nPeer::addSelectColumns($criteria);
		}

		$criteria->clearOrderByColumns(); 
				$criteria->setDbName(self::DATABASE_NAME);

		if ($con === null) {
			$con = Propel::getConnection(MovieI18nPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		$criteria->addJoin(array(MovieI18nPeer::ID,), array(MoviePeer::ID,), $join_behavior);


    foreach (sfMixer::getCallables('BaseMovieI18nPeer:doCount:doCount') as $callable)
    {
      call_user_func($callable, 'BaseMovieI18nPeer', $criteria, $con);
    }


		$stmt = BasePeer::doCount($criteria, $con);

		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$count = (int) $row[0];
		} else {
			$count = 0; 		}
		$stmt->closeCursor();
		return $count;
	}


	
	public static function doSelectJoinMovie(Criteria $c, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{

    foreach (sfMixer::getCallables('BaseMovieI18nPeer:doSelectJoin:doSelectJoin') as $callable)
    {
      call_user_func($callable, 'BaseMovieI18nPeer', $c, $con);
    }


		$c = clone $c;

				if ($c->getDbName() == Propel::getDefaultDB()) {
			$c->setDbName(self::DATABASE_NAME);
		}

		MovieI18nPeer::addSelectColumns($c);
		$startcol = (MovieI18nPeer::NUM_COLUMNS - MovieI18nPeer::NUM_LAZY_LOAD_COLUMNS);
		MoviePeer::addSelectColumns($c);

		$c->addJoin(array(MovieI18nPeer::ID,), array(MoviePeer::ID,), $join_behavior);
		$stmt = BasePeer::doSelect($c, $con);
		$results = array();

		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key1 = MovieI18nPeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj1 = MovieI18nPeer::getInstanceFromPool($key1))) {
															} else {

				$omClass = MovieI18nPeer::getOMClass();

				$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
				$obj1 = new $cls();
				$obj1->hydrate($row);
				MovieI18nPeer::addInstanceToPool($obj1, $key1);
			} 
			$key2 = MoviePeer::getPrimaryKeyHashFromRow($row, $startcol);
			if ($key2 !== null) {
				$obj2 = MoviePeer::getInstanceFromPool($key2);
				if (!$obj2) {

					$omClass = MoviePeer::getOMClass();

					$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
					$obj2 = new $cls();
					$obj2->hydrate($row, $startcol);
					MoviePeer::addInstanceToPool($obj2, $key2);
				} 
								$obj2->addMovieI18n($obj1);

			} 
			$results[] = $obj1;
		}
		$stmt->closeCursor();
		return $results;
	}


	
	public static function doCountJoinAll(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
				$criteria = clone $criteria;

								$criteria->setPrimaryTableName(MovieI18nPeer::TABLE_NAME);

		if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->setDistinct();
		}

		if (!$criteria->hasSelectClause()) {
			MovieI18nPeer::addSelectColumns($criteria);
		}

		$criteria->clearOrderByColumns(); 
				$criteria->setDbName(self::DATABASE_NAME);

		if ($con === null) {
			$con = Propel::getConnection(MovieI18nPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		$criteria->addJoin(array(MovieI18nPeer::ID,), array(MoviePeer::ID,), $join_behavior);

    foreach (sfMixer::getCallables('BaseMovieI18nPeer:doCount:doCount') as $callable)
    {
      call_user_func($callable, 'BaseMovieI18nPeer', $criteria, $con);
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

    foreach (sfMixer::getCallables('BaseMovieI18nPeer:doSelectJoinAll:doSelectJoinAll') as $callable)
    {
      call_user_func($callable, 'BaseMovieI18nPeer', $c, $con);
    }


		$c = clone $c;

				if ($c->getDbName() == Propel::getDefaultDB()) {
			$c->setDbName(self::DATABASE_NAME);
		}

		MovieI18nPeer::addSelectColumns($c);
		$startcol2 = (MovieI18nPeer::NUM_COLUMNS - MovieI18nPeer::NUM_LAZY_LOAD_COLUMNS);

		MoviePeer::addSelectColumns($c);
		$startcol3 = $startcol2 + (MoviePeer::NUM_COLUMNS - MoviePeer::NUM_LAZY_LOAD_COLUMNS);

		$c->addJoin(array(MovieI18nPeer::ID,), array(MoviePeer::ID,), $join_behavior);
		$stmt = BasePeer::doSelect($c, $con);
		$results = array();

		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key1 = MovieI18nPeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj1 = MovieI18nPeer::getInstanceFromPool($key1))) {
															} else {
				$omClass = MovieI18nPeer::getOMClass();

				$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
				$obj1 = new $cls();
				$obj1->hydrate($row);
				MovieI18nPeer::addInstanceToPool($obj1, $key1);
			} 
			
			$key2 = MoviePeer::getPrimaryKeyHashFromRow($row, $startcol2);
			if ($key2 !== null) {
				$obj2 = MoviePeer::getInstanceFromPool($key2);
				if (!$obj2) {

					$omClass = MoviePeer::getOMClass();


					$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
					$obj2 = new $cls();
					$obj2->hydrate($row, $startcol2);
					MoviePeer::addInstanceToPool($obj2, $key2);
				} 
								$obj2->addMovieI18n($obj1);
			} 
			$results[] = $obj1;
		}
		$stmt->closeCursor();
		return $results;
	}


  static public function getUniqueColumnNames()
  {
    return array();
  }
	
	public static function getTableMap()
	{
		return Propel::getDatabaseMap(self::DATABASE_NAME)->getTable(self::TABLE_NAME);
	}

	
	public static function getOMClass()
	{
		return MovieI18nPeer::CLASS_DEFAULT;
	}

	
	public static function doInsert($values, PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseMovieI18nPeer:doInsert:pre') as $callable)
    {
      $ret = call_user_func($callable, 'BaseMovieI18nPeer', $values, $con);
      if (false !== $ret)
      {
        return $ret;
      }
    }


		if ($con === null) {
			$con = Propel::getConnection(MovieI18nPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		if ($values instanceof Criteria) {
			$criteria = clone $values; 		} else {
			$criteria = $values->buildCriteria(); 		}


				$criteria->setDbName(self::DATABASE_NAME);

		try {
									$con->beginTransaction();
			$pk = BasePeer::doInsert($criteria, $con);
			$con->commit();
		} catch(PropelException $e) {
			$con->rollBack();
			throw $e;
		}

		
    foreach (sfMixer::getCallables('BaseMovieI18nPeer:doInsert:post') as $callable)
    {
      call_user_func($callable, 'BaseMovieI18nPeer', $values, $con, $pk);
    }

    return $pk;
	}

	
	public static function doUpdate($values, PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseMovieI18nPeer:doUpdate:pre') as $callable)
    {
      $ret = call_user_func($callable, 'BaseMovieI18nPeer', $values, $con);
      if (false !== $ret)
      {
        return $ret;
      }
    }


		if ($con === null) {
			$con = Propel::getConnection(MovieI18nPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		$selectCriteria = new Criteria(self::DATABASE_NAME);

		if ($values instanceof Criteria) {
			$criteria = clone $values; 
			$comparison = $criteria->getComparison(MovieI18nPeer::ID);
			$selectCriteria->add(MovieI18nPeer::ID, $criteria->remove(MovieI18nPeer::ID), $comparison);

			$comparison = $criteria->getComparison(MovieI18nPeer::CULTURE);
			$selectCriteria->add(MovieI18nPeer::CULTURE, $criteria->remove(MovieI18nPeer::CULTURE), $comparison);

		} else { 			$criteria = $values->buildCriteria(); 			$selectCriteria = $values->buildPkeyCriteria(); 		}

				$criteria->setDbName(self::DATABASE_NAME);

		$ret = BasePeer::doUpdate($selectCriteria, $criteria, $con);
	

    foreach (sfMixer::getCallables('BaseMovieI18nPeer:doUpdate:post') as $callable)
    {
      call_user_func($callable, 'BaseMovieI18nPeer', $values, $con, $ret);
    }

    return $ret;
  }

	
	public static function doDeleteAll($con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(MovieI18nPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		$affectedRows = 0; 		try {
									$con->beginTransaction();
			$affectedRows += BasePeer::doDeleteAll(MovieI18nPeer::TABLE_NAME, $con);
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
			$con = Propel::getConnection(MovieI18nPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		if ($values instanceof Criteria) {
												MovieI18nPeer::clearInstancePool();

						$criteria = clone $values;
		} elseif ($values instanceof MovieI18n) {
						MovieI18nPeer::removeInstanceFromPool($values);
						$criteria = $values->buildPkeyCriteria();
		} else {
			


			$criteria = new Criteria(self::DATABASE_NAME);
												if (count($values) == count($values, COUNT_RECURSIVE)) {
								$values = array($values);
			}

			foreach ($values as $value) {

				$criterion = $criteria->getNewCriterion(MovieI18nPeer::ID, $value[0]);
				$criterion->addAnd($criteria->getNewCriterion(MovieI18nPeer::CULTURE, $value[1]));
				$criteria->addOr($criterion);

								MovieI18nPeer::removeInstanceFromPool($value);
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

	
	public static function doValidate(MovieI18n $obj, $cols = null)
	{
		$columns = array();

		if ($cols) {
			$dbMap = Propel::getDatabaseMap(MovieI18nPeer::DATABASE_NAME);
			$tableMap = $dbMap->getTable(MovieI18nPeer::TABLE_NAME);

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

		$res =  BasePeer::doValidate(MovieI18nPeer::DATABASE_NAME, MovieI18nPeer::TABLE_NAME, $columns);
    if ($res !== true) {
        $request = sfContext::getInstance()->getRequest();
        foreach ($res as $failed) {
            $col = MovieI18nPeer::translateFieldname($failed->getColumn(), BasePeer::TYPE_COLNAME, BasePeer::TYPE_PHPNAME);
        }
    }

    return $res;
	}

	
	public static function retrieveByPK($id, $culture, PropelPDO $con = null) {
		$key = serialize(array((string) $id, (string) $culture));
 		if (null !== ($obj = MovieI18nPeer::getInstanceFromPool($key))) {
 			return $obj;
		}

		if ($con === null) {
			$con = Propel::getConnection(MovieI18nPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}
		$criteria = new Criteria(MovieI18nPeer::DATABASE_NAME);
		$criteria->add(MovieI18nPeer::ID, $id);
		$criteria->add(MovieI18nPeer::CULTURE, $culture);
		$v = MovieI18nPeer::doSelect($criteria, $con);

		return !empty($v) ? $v[0] : null;
	}
} 

Propel::getDatabaseMap(BaseMovieI18nPeer::DATABASE_NAME)->addTableBuilder(BaseMovieI18nPeer::TABLE_NAME, BaseMovieI18nPeer::getMapBuilder());

