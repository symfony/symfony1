<?php


abstract class BaseProductI18nPeer {

	
	const DATABASE_NAME = 'propel';

	
	const TABLE_NAME = 'product_i18n';

	
	const CLASS_DEFAULT = 'lib.model.ProductI18n';

	
	const NUM_COLUMNS = 3;

	
	const NUM_LAZY_LOAD_COLUMNS = 0;

	
	const ID = 'product_i18n.ID';

	
	const CULTURE = 'product_i18n.CULTURE';

	
	const NAME = 'product_i18n.NAME';

	
	public static $instances = array();

	
	private static $mapBuilder = null;

	
	private static $fieldNames = array (
		BasePeer::TYPE_PHPNAME => array ('Id', 'Culture', 'Name', ),
		BasePeer::TYPE_STUDLYPHPNAME => array ('id', 'culture', 'name', ),
		BasePeer::TYPE_COLNAME => array (self::ID, self::CULTURE, self::NAME, ),
		BasePeer::TYPE_FIELDNAME => array ('id', 'culture', 'name', ),
		BasePeer::TYPE_NUM => array (0, 1, 2, )
	);

	
	private static $fieldKeys = array (
		BasePeer::TYPE_PHPNAME => array ('Id' => 0, 'Culture' => 1, 'Name' => 2, ),
		BasePeer::TYPE_STUDLYPHPNAME => array ('id' => 0, 'culture' => 1, 'name' => 2, ),
		BasePeer::TYPE_COLNAME => array (self::ID => 0, self::CULTURE => 1, self::NAME => 2, ),
		BasePeer::TYPE_FIELDNAME => array ('id' => 0, 'culture' => 1, 'name' => 2, ),
		BasePeer::TYPE_NUM => array (0, 1, 2, )
	);

	
	public static function getMapBuilder()
	{
		if (self::$mapBuilder === null) {
			self::$mapBuilder = new ProductI18nMapBuilder();
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
		return str_replace(ProductI18nPeer::TABLE_NAME.'.', $alias.'.', $column);
	}

	
	public static function addSelectColumns(Criteria $criteria)
	{

		$criteria->addSelectColumn(ProductI18nPeer::ID);

		$criteria->addSelectColumn(ProductI18nPeer::CULTURE);

		$criteria->addSelectColumn(ProductI18nPeer::NAME);

	}

	
	public static function doCount(Criteria $criteria, $distinct = false, PropelPDO $con = null)
	{
				$criteria = clone $criteria;

								$criteria->setPrimaryTableName(ProductI18nPeer::TABLE_NAME);

		if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->setDistinct();
		}

		if (!$criteria->hasSelectClause()) {
			ProductI18nPeer::addSelectColumns($criteria);
		}

		$criteria->clearOrderByColumns(); 		$criteria->setDbName(self::DATABASE_NAME); 
		if ($con === null) {
			$con = Propel::getConnection(ProductI18nPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}


    foreach (sfMixer::getCallables('BaseProductI18nPeer:doCount:doCount') as $callable)
    {
      call_user_func($callable, 'BaseProductI18nPeer', $criteria, $con);
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
		$objects = ProductI18nPeer::doSelect($critcopy, $con);
		if ($objects) {
			return $objects[0];
		}
		return null;
	}
	
	public static function doSelect(Criteria $criteria, PropelPDO $con = null)
	{
		return ProductI18nPeer::populateObjects(ProductI18nPeer::doSelectStmt($criteria, $con));
	}
	
	public static function doSelectStmt(Criteria $criteria, PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseProductI18nPeer:doSelectStmt:doSelectStmt') as $callable)
    {
      call_user_func($callable, 'BaseProductI18nPeer', $criteria, $con);
    }


		if ($con === null) {
			$con = Propel::getConnection(ProductI18nPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		if (!$criteria->hasSelectClause()) {
			$criteria = clone $criteria;
			ProductI18nPeer::addSelectColumns($criteria);
		}

				$criteria->setDbName(self::DATABASE_NAME);

				return BasePeer::doSelect($criteria, $con);
	}
	
	public static function addInstanceToPool(ProductI18n $obj, $key = null)
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
			if (is_object($value) && $value instanceof ProductI18n) {
				$key = serialize(array((string) $value->getId(), (string) $value->getCulture()));
			} elseif (is_array($value) && count($value) === 2) {
								$key = serialize(array((string) $value[0], (string) $value[1]));
			} else {
				$e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or ProductI18n object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value,true)));
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
	
				$cls = ProductI18nPeer::getOMClass();
		$cls = substr('.'.$cls, strrpos('.'.$cls, '.') + 1);
				while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key = ProductI18nPeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj = ProductI18nPeer::getInstanceFromPool($key))) {
																$results[] = $obj;
			} else {
		
				$obj = new $cls();
				$obj->hydrate($row);
				$results[] = $obj;
				ProductI18nPeer::addInstanceToPool($obj, $key);
			} 		}
		$stmt->closeCursor();
		return $results;
	}

	
	public static function doCountJoinProduct(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
				$criteria = clone $criteria;

								$criteria->setPrimaryTableName(ProductI18nPeer::TABLE_NAME);

		if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->setDistinct();
		}

		if (!$criteria->hasSelectClause()) {
			ProductI18nPeer::addSelectColumns($criteria);
		}

		$criteria->clearOrderByColumns(); 
				$criteria->setDbName(self::DATABASE_NAME);

		if ($con === null) {
			$con = Propel::getConnection(ProductI18nPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		$criteria->addJoin(array(ProductI18nPeer::ID,), array(ProductPeer::ID,), $join_behavior);


    foreach (sfMixer::getCallables('BaseProductI18nPeer:doCount:doCount') as $callable)
    {
      call_user_func($callable, 'BaseProductI18nPeer', $criteria, $con);
    }


		$stmt = BasePeer::doCount($criteria, $con);

		if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$count = (int) $row[0];
		} else {
			$count = 0; 		}
		$stmt->closeCursor();
		return $count;
	}


	
	public static function doSelectJoinProduct(Criteria $c, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{

    foreach (sfMixer::getCallables('BaseProductI18nPeer:doSelectJoin:doSelectJoin') as $callable)
    {
      call_user_func($callable, 'BaseProductI18nPeer', $c, $con);
    }


		$c = clone $c;

				if ($c->getDbName() == Propel::getDefaultDB()) {
			$c->setDbName(self::DATABASE_NAME);
		}

		ProductI18nPeer::addSelectColumns($c);
		$startcol = (ProductI18nPeer::NUM_COLUMNS - ProductI18nPeer::NUM_LAZY_LOAD_COLUMNS);
		ProductPeer::addSelectColumns($c);

		$c->addJoin(array(ProductI18nPeer::ID,), array(ProductPeer::ID,), $join_behavior);
		$stmt = BasePeer::doSelect($c, $con);
		$results = array();

		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key1 = ProductI18nPeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj1 = ProductI18nPeer::getInstanceFromPool($key1))) {
															} else {

				$omClass = ProductI18nPeer::getOMClass();

				$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
				$obj1 = new $cls();
				$obj1->hydrate($row);
				ProductI18nPeer::addInstanceToPool($obj1, $key1);
			} 
			$key2 = ProductPeer::getPrimaryKeyHashFromRow($row, $startcol);
			if ($key2 !== null) {
				$obj2 = ProductPeer::getInstanceFromPool($key2);
				if (!$obj2) {

					$omClass = ProductPeer::getOMClass();

					$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
					$obj2 = new $cls();
					$obj2->hydrate($row, $startcol);
					ProductPeer::addInstanceToPool($obj2, $key2);
				} 
								$obj2->addProductI18n($obj1);

			} 
			$results[] = $obj1;
		}
		$stmt->closeCursor();
		return $results;
	}


	
	public static function doCountJoinAll(Criteria $criteria, $distinct = false, PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
				$criteria = clone $criteria;

								$criteria->setPrimaryTableName(ProductI18nPeer::TABLE_NAME);

		if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
			$criteria->setDistinct();
		}

		if (!$criteria->hasSelectClause()) {
			ProductI18nPeer::addSelectColumns($criteria);
		}

		$criteria->clearOrderByColumns(); 
				$criteria->setDbName(self::DATABASE_NAME);

		if ($con === null) {
			$con = Propel::getConnection(ProductI18nPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		$criteria->addJoin(array(ProductI18nPeer::ID,), array(ProductPeer::ID,), $join_behavior);

    foreach (sfMixer::getCallables('BaseProductI18nPeer:doCount:doCount') as $callable)
    {
      call_user_func($callable, 'BaseProductI18nPeer', $criteria, $con);
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

    foreach (sfMixer::getCallables('BaseProductI18nPeer:doSelectJoinAll:doSelectJoinAll') as $callable)
    {
      call_user_func($callable, 'BaseProductI18nPeer', $c, $con);
    }


		$c = clone $c;

				if ($c->getDbName() == Propel::getDefaultDB()) {
			$c->setDbName(self::DATABASE_NAME);
		}

		ProductI18nPeer::addSelectColumns($c);
		$startcol2 = (ProductI18nPeer::NUM_COLUMNS - ProductI18nPeer::NUM_LAZY_LOAD_COLUMNS);

		ProductPeer::addSelectColumns($c);
		$startcol3 = $startcol2 + (ProductPeer::NUM_COLUMNS - ProductPeer::NUM_LAZY_LOAD_COLUMNS);

		$c->addJoin(array(ProductI18nPeer::ID,), array(ProductPeer::ID,), $join_behavior);
		$stmt = BasePeer::doSelect($c, $con);
		$results = array();

		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key1 = ProductI18nPeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj1 = ProductI18nPeer::getInstanceFromPool($key1))) {
															} else {
				$omClass = ProductI18nPeer::getOMClass();

				$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
				$obj1 = new $cls();
				$obj1->hydrate($row);
				ProductI18nPeer::addInstanceToPool($obj1, $key1);
			} 
			
			$key2 = ProductPeer::getPrimaryKeyHashFromRow($row, $startcol2);
			if ($key2 !== null) {
				$obj2 = ProductPeer::getInstanceFromPool($key2);
				if (!$obj2) {

					$omClass = ProductPeer::getOMClass();


					$cls = substr('.'.$omClass, strrpos('.'.$omClass, '.') + 1);
					$obj2 = new $cls();
					$obj2->hydrate($row, $startcol2);
					ProductPeer::addInstanceToPool($obj2, $key2);
				} 
								$obj2->addProductI18n($obj1);
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
		return ProductI18nPeer::CLASS_DEFAULT;
	}

	
	public static function doInsert($values, PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseProductI18nPeer:doInsert:pre') as $callable)
    {
      $ret = call_user_func($callable, 'BaseProductI18nPeer', $values, $con);
      if (false !== $ret)
      {
        return $ret;
      }
    }


		if ($con === null) {
			$con = Propel::getConnection(ProductI18nPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
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

		
    foreach (sfMixer::getCallables('BaseProductI18nPeer:doInsert:post') as $callable)
    {
      call_user_func($callable, 'BaseProductI18nPeer', $values, $con, $pk);
    }

    return $pk;
	}

	
	public static function doUpdate($values, PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseProductI18nPeer:doUpdate:pre') as $callable)
    {
      $ret = call_user_func($callable, 'BaseProductI18nPeer', $values, $con);
      if (false !== $ret)
      {
        return $ret;
      }
    }


		if ($con === null) {
			$con = Propel::getConnection(ProductI18nPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		$selectCriteria = new Criteria(self::DATABASE_NAME);

		if ($values instanceof Criteria) {
			$criteria = clone $values; 
			$comparison = $criteria->getComparison(ProductI18nPeer::ID);
			$selectCriteria->add(ProductI18nPeer::ID, $criteria->remove(ProductI18nPeer::ID), $comparison);

			$comparison = $criteria->getComparison(ProductI18nPeer::CULTURE);
			$selectCriteria->add(ProductI18nPeer::CULTURE, $criteria->remove(ProductI18nPeer::CULTURE), $comparison);

		} else { 			$criteria = $values->buildCriteria(); 			$selectCriteria = $values->buildPkeyCriteria(); 		}

				$criteria->setDbName(self::DATABASE_NAME);

		$ret = BasePeer::doUpdate($selectCriteria, $criteria, $con);
	

    foreach (sfMixer::getCallables('BaseProductI18nPeer:doUpdate:post') as $callable)
    {
      call_user_func($callable, 'BaseProductI18nPeer', $values, $con, $ret);
    }

    return $ret;
  }

	
	public static function doDeleteAll($con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(ProductI18nPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		$affectedRows = 0; 		try {
									$con->beginTransaction();
			$affectedRows += BasePeer::doDeleteAll(ProductI18nPeer::TABLE_NAME, $con);
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
			$con = Propel::getConnection(ProductI18nPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		if ($values instanceof Criteria) {
												ProductI18nPeer::clearInstancePool();

						$criteria = clone $values;
		} elseif ($values instanceof ProductI18n) {
						ProductI18nPeer::removeInstanceFromPool($values);
						$criteria = $values->buildPkeyCriteria();
		} else {
			


			$criteria = new Criteria(self::DATABASE_NAME);
												if (count($values) == count($values, COUNT_RECURSIVE)) {
								$values = array($values);
			}

			foreach ($values as $value) {

				$criterion = $criteria->getNewCriterion(ProductI18nPeer::ID, $value[0]);
				$criterion->addAnd($criteria->getNewCriterion(ProductI18nPeer::CULTURE, $value[1]));
				$criteria->addOr($criterion);

								ProductI18nPeer::removeInstanceFromPool($value);
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

	
	public static function doValidate(ProductI18n $obj, $cols = null)
	{
		$columns = array();

		if ($cols) {
			$dbMap = Propel::getDatabaseMap(ProductI18nPeer::DATABASE_NAME);
			$tableMap = $dbMap->getTable(ProductI18nPeer::TABLE_NAME);

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

		$res =  BasePeer::doValidate(ProductI18nPeer::DATABASE_NAME, ProductI18nPeer::TABLE_NAME, $columns);
    if ($res !== true) {
        $request = sfContext::getInstance()->getRequest();
        foreach ($res as $failed) {
            $col = ProductI18nPeer::translateFieldname($failed->getColumn(), BasePeer::TYPE_COLNAME, BasePeer::TYPE_PHPNAME);
        }
    }

    return $res;
	}

	
	public static function retrieveByPK($id, $culture, PropelPDO $con = null) {
		$key = serialize(array((string) $id, (string) $culture));
 		if (null !== ($obj = ProductI18nPeer::getInstanceFromPool($key))) {
 			return $obj;
		}

		if ($con === null) {
			$con = Propel::getConnection(ProductI18nPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}
		$criteria = new Criteria(ProductI18nPeer::DATABASE_NAME);
		$criteria->add(ProductI18nPeer::ID, $id);
		$criteria->add(ProductI18nPeer::CULTURE, $culture);
		$v = ProductI18nPeer::doSelect($criteria, $con);

		return !empty($v) ? $v[0] : null;
	}
} 

Propel::getDatabaseMap(BaseProductI18nPeer::DATABASE_NAME)->addTableBuilder(BaseProductI18nPeer::TABLE_NAME, BaseProductI18nPeer::getMapBuilder());

