<?php


abstract class BaseMoviePeer {

	
	const DATABASE_NAME = 'propel';

	
	const TABLE_NAME = 'movie';

	
	const CLASS_DEFAULT = 'lib.model.Movie';

	
	const NUM_COLUMNS = 2;

	
	const NUM_LAZY_LOAD_COLUMNS = 0;

	
	const ID = 'movie.ID';

	
	const DIRECTOR = 'movie.DIRECTOR';

	
	public static $instances = array();

	
	private static $mapBuilder = null;

	
	private static $fieldNames = array (
		BasePeer::TYPE_PHPNAME => array ('Id', 'Director', ),
		BasePeer::TYPE_STUDLYPHPNAME => array ('id', 'director', ),
		BasePeer::TYPE_COLNAME => array (self::ID, self::DIRECTOR, ),
		BasePeer::TYPE_FIELDNAME => array ('id', 'director', ),
		BasePeer::TYPE_NUM => array (0, 1, )
	);

	
	private static $fieldKeys = array (
		BasePeer::TYPE_PHPNAME => array ('Id' => 0, 'Director' => 1, ),
		BasePeer::TYPE_STUDLYPHPNAME => array ('id' => 0, 'director' => 1, ),
		BasePeer::TYPE_COLNAME => array (self::ID => 0, self::DIRECTOR => 1, ),
		BasePeer::TYPE_FIELDNAME => array ('id' => 0, 'director' => 1, ),
		BasePeer::TYPE_NUM => array (0, 1, )
	);

	
	public static function getMapBuilder()
	{
		if (self::$mapBuilder === null) {
			self::$mapBuilder = new MovieMapBuilder();
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
		return str_replace(MoviePeer::TABLE_NAME.'.', $alias.'.', $column);
	}

	
	public static function addSelectColumns(Criteria $criteria)
	{

		$criteria->addSelectColumn(MoviePeer::ID);

		$criteria->addSelectColumn(MoviePeer::DIRECTOR);

	}

	
	public static function doCount(Criteria $criteria, $distinct = false, PropelPDO $con = null)
	{
				$criteria = clone $criteria;

								$criteria->setPrimaryTableName(MoviePeer::TABLE_NAME);

		if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->setDistinct();
		}

		if (!$criteria->hasSelectClause()) {
			MoviePeer::addSelectColumns($criteria);
		}

		$criteria->clearOrderByColumns(); 		$criteria->setDbName(self::DATABASE_NAME); 
		if ($con === null) {
			$con = Propel::getConnection(MoviePeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}


    foreach (sfMixer::getCallables('BaseMoviePeer:doCount:doCount') as $callable)
    {
      call_user_func($callable, 'BaseMoviePeer', $criteria, $con);
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
		$objects = MoviePeer::doSelect($critcopy, $con);
		if ($objects) {
			return $objects[0];
		}
		return null;
	}
	
	public static function doSelect(Criteria $criteria, PropelPDO $con = null)
	{
		return MoviePeer::populateObjects(MoviePeer::doSelectStmt($criteria, $con));
	}
	
	public static function doSelectStmt(Criteria $criteria, PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseMoviePeer:doSelectStmt:doSelectStmt') as $callable)
    {
      call_user_func($callable, 'BaseMoviePeer', $criteria, $con);
    }


		if ($con === null) {
			$con = Propel::getConnection(MoviePeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		if (!$criteria->hasSelectClause()) {
			$criteria = clone $criteria;
			MoviePeer::addSelectColumns($criteria);
		}

				$criteria->setDbName(self::DATABASE_NAME);

				return BasePeer::doSelect($criteria, $con);
	}
	
	public static function addInstanceToPool(Movie $obj, $key = null)
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
			if (is_object($value) && $value instanceof Movie) {
				$key = (string) $value->getId();
			} elseif (is_scalar($value)) {
								$key = (string) $value;
			} else {
				$e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or Movie object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value,true)));
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
	
				$cls = MoviePeer::getOMClass();
		$cls = substr('.'.$cls, strrpos('.'.$cls, '.') + 1);
				while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key = MoviePeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj = MoviePeer::getInstanceFromPool($key))) {
																$results[] = $obj;
			} else {
		
				$obj = new $cls();
				$obj->hydrate($row);
				$results[] = $obj;
				MoviePeer::addInstanceToPool($obj, $key);
			} 		}
		$stmt->closeCursor();
		return $results;
	}

  
  public static function doSelectWithI18n(Criteria $c, $culture = null, PropelPDO $con = null)
  {
        $c = clone $c;
    if ($culture === null)
    {
      $culture = sfPropel::getDefaultCulture();
    }


    foreach (sfMixer::getCallables('BaseMoviePeer:doSelectJoin:doSelectJoin') as $callable)
    {
      call_user_func($callable, 'BaseMoviePeer', $c, $con);
    }


        if ($c->getDbName() == Propel::getDefaultDB())
    {
      $c->setDbName(self::DATABASE_NAME);
    }

    MoviePeer::addSelectColumns($c);
    $startcol = (MoviePeer::NUM_COLUMNS - MoviePeer::NUM_LAZY_LOAD_COLUMNS);

    MovieI18nPeer::addSelectColumns($c);

    $c->addJoin(MoviePeer::ID, MovieI18nPeer::ID);
    $c->add(MovieI18nPeer::CULTURE, $culture);

    $stmt = BasePeer::doSelect($c, $con);
    $results = array();

    while($row = $stmt->fetch(PDO::FETCH_NUM)) {

      $omClass = MoviePeer::getOMClass();

      $cls = Propel::importClass($omClass);
      $obj1 = new $cls();
      $obj1->hydrate($row);
      $obj1->setCulture($culture);

      $omClass = MovieI18nPeer::getOMClass();

      $cls = Propel::importClass($omClass);
      $obj2 = new $cls();
      $obj2->hydrate($row, $startcol);

      $obj1->setMovieI18nForCulture($obj2, $culture);
      $obj2->setMovie($obj1);

      $results[] = $obj1;
    }
    return $results;
  }


  
  public static function getI18nModel()
  {
    return 'MovieI18n';
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
		return MoviePeer::CLASS_DEFAULT;
	}

	
	public static function doInsert($values, PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseMoviePeer:doInsert:pre') as $callable)
    {
      $ret = call_user_func($callable, 'BaseMoviePeer', $values, $con);
      if (false !== $ret)
      {
        return $ret;
      }
    }


		if ($con === null) {
			$con = Propel::getConnection(MoviePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		if ($values instanceof Criteria) {
			$criteria = clone $values; 		} else {
			$criteria = $values->buildCriteria(); 		}

		if ($criteria->containsKey(MoviePeer::ID) && $criteria->keyContainsValue(MoviePeer::ID) ) {
			throw new PropelException('Cannot insert a value for auto-increment primary key ('.MoviePeer::ID.')');
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

		
    foreach (sfMixer::getCallables('BaseMoviePeer:doInsert:post') as $callable)
    {
      call_user_func($callable, 'BaseMoviePeer', $values, $con, $pk);
    }

    return $pk;
	}

	
	public static function doUpdate($values, PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseMoviePeer:doUpdate:pre') as $callable)
    {
      $ret = call_user_func($callable, 'BaseMoviePeer', $values, $con);
      if (false !== $ret)
      {
        return $ret;
      }
    }


		if ($con === null) {
			$con = Propel::getConnection(MoviePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		$selectCriteria = new Criteria(self::DATABASE_NAME);

		if ($values instanceof Criteria) {
			$criteria = clone $values; 
			$comparison = $criteria->getComparison(MoviePeer::ID);
			$selectCriteria->add(MoviePeer::ID, $criteria->remove(MoviePeer::ID), $comparison);

		} else { 			$criteria = $values->buildCriteria(); 			$selectCriteria = $values->buildPkeyCriteria(); 		}

				$criteria->setDbName(self::DATABASE_NAME);

		$ret = BasePeer::doUpdate($selectCriteria, $criteria, $con);
	

    foreach (sfMixer::getCallables('BaseMoviePeer:doUpdate:post') as $callable)
    {
      call_user_func($callable, 'BaseMoviePeer', $values, $con, $ret);
    }

    return $ret;
  }

	
	public static function doDeleteAll($con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(MoviePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		$affectedRows = 0; 		try {
									$con->beginTransaction();
			$affectedRows += MoviePeer::doOnDeleteCascade(new Criteria(MoviePeer::DATABASE_NAME), $con);
			$affectedRows += BasePeer::doDeleteAll(MoviePeer::TABLE_NAME, $con);
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
			$con = Propel::getConnection(MoviePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		if ($values instanceof Criteria) {
												MoviePeer::clearInstancePool();

						$criteria = clone $values;
		} elseif ($values instanceof Movie) {
						MoviePeer::removeInstanceFromPool($values);
						$criteria = $values->buildPkeyCriteria();
		} else {
			


			$criteria = new Criteria(self::DATABASE_NAME);
			$criteria->add(MoviePeer::ID, (array) $values, Criteria::IN);

			foreach ((array) $values as $singleval) {
								MoviePeer::removeInstanceFromPool($singleval);
			}
		}

				$criteria->setDbName(self::DATABASE_NAME);

		$affectedRows = 0; 
		try {
									$con->beginTransaction();
			$affectedRows += MoviePeer::doOnDeleteCascade($criteria, $con);
			
																if ($values instanceof Criteria) {
					MoviePeer::clearInstancePool();
				} else { 					MoviePeer::removeInstanceFromPool($values);
				}
			
			$affectedRows += BasePeer::doDelete($criteria, $con);

						MovieI18nPeer::clearInstancePool();

			$con->commit();
			return $affectedRows;
		} catch (PropelException $e) {
			$con->rollBack();
			throw $e;
		}
	}

	
	protected static function doOnDeleteCascade(Criteria $criteria, PropelPDO $con)
	{
				$affectedRows = 0;

				$objects = MoviePeer::doSelect($criteria, $con);
		foreach ($objects as $obj) {


						$c = new Criteria(MovieI18nPeer::DATABASE_NAME);
			
			$c->add(MovieI18nPeer::ID, $obj->getId());
			$affectedRows += MovieI18nPeer::doDelete($c, $con);
		}
		return $affectedRows;
	}

	
	public static function doValidate(Movie $obj, $cols = null)
	{
		$columns = array();

		if ($cols) {
			$dbMap = Propel::getDatabaseMap(MoviePeer::DATABASE_NAME);
			$tableMap = $dbMap->getTable(MoviePeer::TABLE_NAME);

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

		$res =  BasePeer::doValidate(MoviePeer::DATABASE_NAME, MoviePeer::TABLE_NAME, $columns);
    if ($res !== true) {
        $request = sfContext::getInstance()->getRequest();
        foreach ($res as $failed) {
            $col = MoviePeer::translateFieldname($failed->getColumn(), BasePeer::TYPE_COLNAME, BasePeer::TYPE_PHPNAME);
        }
    }

    return $res;
	}

	
	public static function retrieveByPK($pk, PropelPDO $con = null)
	{

		if (null !== ($obj = MoviePeer::getInstanceFromPool((string) $pk))) {
			return $obj;
		}

		if ($con === null) {
			$con = Propel::getConnection(MoviePeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		$criteria = new Criteria(MoviePeer::DATABASE_NAME);
		$criteria->add(MoviePeer::ID, $pk);

		$v = MoviePeer::doSelect($criteria, $con);

		return !empty($v) > 0 ? $v[0] : null;
	}

	
	public static function retrieveByPKs($pks, PropelPDO $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(MoviePeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		$objs = null;
		if (empty($pks)) {
			$objs = array();
		} else {
			$criteria = new Criteria(MoviePeer::DATABASE_NAME);
			$criteria->add(MoviePeer::ID, $pks, Criteria::IN);
			$objs = MoviePeer::doSelect($criteria, $con);
		}
		return $objs;
	}

} 

Propel::getDatabaseMap(BaseMoviePeer::DATABASE_NAME)->addTableBuilder(BaseMoviePeer::TABLE_NAME, BaseMoviePeer::getMapBuilder());

