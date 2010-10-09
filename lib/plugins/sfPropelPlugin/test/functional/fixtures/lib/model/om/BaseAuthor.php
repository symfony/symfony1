<?php


abstract class BaseAuthor extends BaseObject  implements Persistent {


  const PEER = 'AuthorPeer';

	
	protected static $peer;

	
	protected $id;

	
	protected $name;

	
	protected $collAuthorArticles;

	
	private $lastAuthorArticleCriteria = null;

	
	protected $alreadyInSave = false;

	
	protected $alreadyInValidation = false;

	
	public function __construct()
	{
		parent::__construct();
		$this->applyDefaultValues();
	}

	
	public function applyDefaultValues()
	{
	}

	
	public function getId()
	{
		return $this->id;
	}

	
	public function getName()
	{
		return $this->name;
	}

	
	public function setId($v)
	{
		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->id !== $v) {
			$this->id = $v;
			$this->modifiedColumns[] = AuthorPeer::ID;
		}

		return $this;
	} 
	
	public function setName($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->name !== $v) {
			$this->name = $v;
			$this->modifiedColumns[] = AuthorPeer::NAME;
		}

		return $this;
	} 
	
	public function hasOnlyDefaultValues()
	{
						if (array_diff($this->modifiedColumns, array())) {
				return false;
			}

				return true;
	} 
	
	public function hydrate($row, $startcol = 0, $rehydrate = false)
	{
		try {

			$this->id = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
			$this->name = ($row[$startcol + 1] !== null) ? (string) $row[$startcol + 1] : null;
			$this->resetModified();

			$this->setNew(false);

			if ($rehydrate) {
				$this->ensureConsistency();
			}

						return $startcol + 2; 
		} catch (Exception $e) {
			throw new PropelException("Error populating Author object", $e);
		}
	}

	
	public function ensureConsistency()
	{

	} 
	
	public function reload($deep = false, PropelPDO $con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("Cannot reload a deleted object.");
		}

		if ($this->isNew()) {
			throw new PropelException("Cannot reload an unsaved object.");
		}

		if ($con === null) {
			$con = Propel::getConnection(AuthorPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

				
		$stmt = AuthorPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
		$row = $stmt->fetch(PDO::FETCH_NUM);
		$stmt->closeCursor();
		if (!$row) {
			throw new PropelException('Cannot find matching row in the database to reload object values.');
		}
		$this->hydrate($row, 0, true); 
		if ($deep) {  
			$this->collAuthorArticles = null;
			$this->lastAuthorArticleCriteria = null;

		} 	}

	
	public function delete(PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseAuthor:delete:pre') as $callable)
    {
      $ret = call_user_func($callable, $this, $con);
      if ($ret)
      {
        return;
      }
    }


		if ($this->isDeleted()) {
			throw new PropelException("This object has already been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(AuthorPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		$con->beginTransaction();
		try {
			AuthorPeer::doDelete($this, $con);
			$this->setDeleted(true);
			$con->commit();
		} catch (PropelException $e) {
			$con->rollBack();
			throw $e;
		}
	

    foreach (sfMixer::getCallables('BaseAuthor:delete:post') as $callable)
    {
      call_user_func($callable, $this, $con);
    }

  }
	
	public function save(PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseAuthor:save:pre') as $callable)
    {
      $affectedRows = call_user_func($callable, $this, $con);
      if (is_int($affectedRows))
      {
        return $affectedRows;
      }
    }


		if ($this->isDeleted()) {
			throw new PropelException("You cannot save an object that has been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(AuthorPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		$con->beginTransaction();
		try {
			$affectedRows = $this->doSave($con);
			$con->commit();
    foreach (sfMixer::getCallables('BaseAuthor:save:post') as $callable)
    {
      call_user_func($callable, $this, $con, $affectedRows);
    }

			AuthorPeer::addInstanceToPool($this);
			return $affectedRows;
		} catch (PropelException $e) {
			$con->rollBack();
			throw $e;
		}
	}

	
	protected function doSave(PropelPDO $con)
	{
		$affectedRows = 0; 		if (!$this->alreadyInSave) {
			$this->alreadyInSave = true;

			if ($this->isNew() ) {
				$this->modifiedColumns[] = AuthorPeer::ID;
			}

						if ($this->isModified()) {
				if ($this->isNew()) {
					$pk = AuthorPeer::doInsert($this, $con);
					$affectedRows += 1; 										 										 
					$this->setId($pk);  
					$this->setNew(false);
				} else {
					$affectedRows += AuthorPeer::doUpdate($this, $con);
				}

				$this->resetModified(); 			}

			if ($this->collAuthorArticles !== null) {
				foreach ($this->collAuthorArticles as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			$this->alreadyInSave = false;

		}
		return $affectedRows;
	} 
	
	protected $validationFailures = array();

	
	public function getValidationFailures()
	{
		return $this->validationFailures;
	}

	
	public function validate($columns = null)
	{
		$res = $this->doValidate($columns);
		if ($res === true) {
			$this->validationFailures = array();
			return true;
		} else {
			$this->validationFailures = $res;
			return false;
		}
	}

	
	protected function doValidate($columns = null)
	{
		if (!$this->alreadyInValidation) {
			$this->alreadyInValidation = true;
			$retval = null;

			$failureMap = array();


			if (($retval = AuthorPeer::doValidate($this, $columns)) !== true) {
				$failureMap = array_merge($failureMap, $retval);
			}


				if ($this->collAuthorArticles !== null) {
					foreach ($this->collAuthorArticles as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}


			$this->alreadyInValidation = false;
		}

		return (!empty($failureMap) ? $failureMap : true);
	}

	
	public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = AuthorPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		$field = $this->getByPosition($pos);
		return $field;
	}

	
	public function getByPosition($pos)
	{
		switch($pos) {
			case 0:
				return $this->getId();
				break;
			case 1:
				return $this->getName();
				break;
			default:
				return null;
				break;
		} 	}

	
	public function toArray($keyType = BasePeer::TYPE_PHPNAME, $includeLazyLoadColumns = true)
	{
		$keys = AuthorPeer::getFieldNames($keyType);
		$result = array(
			$keys[0] => $this->getId(),
			$keys[1] => $this->getName(),
		);
		return $result;
	}

	
	public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = AuthorPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		return $this->setByPosition($pos, $value);
	}

	
	public function setByPosition($pos, $value)
	{
		switch($pos) {
			case 0:
				$this->setId($value);
				break;
			case 1:
				$this->setName($value);
				break;
		} 	}

	
	public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = AuthorPeer::getFieldNames($keyType);

		if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
		if (array_key_exists($keys[1], $arr)) $this->setName($arr[$keys[1]]);
	}

	
	public function buildCriteria()
	{
		$criteria = new Criteria(AuthorPeer::DATABASE_NAME);

		if ($this->isColumnModified(AuthorPeer::ID)) $criteria->add(AuthorPeer::ID, $this->id);
		if ($this->isColumnModified(AuthorPeer::NAME)) $criteria->add(AuthorPeer::NAME, $this->name);

		return $criteria;
	}

	
	public function buildPkeyCriteria()
	{
		$criteria = new Criteria(AuthorPeer::DATABASE_NAME);

		$criteria->add(AuthorPeer::ID, $this->id);

		return $criteria;
	}

	
	public function getPrimaryKey()
	{
		return $this->getId();
	}

	
	public function setPrimaryKey($key)
	{
		$this->setId($key);
	}

	
	public function copyInto($copyObj, $deepCopy = false)
	{

		$copyObj->setName($this->name);


		if ($deepCopy) {
									$copyObj->setNew(false);

			foreach ($this->getAuthorArticles() as $relObj) {
				if ($relObj !== $this) {  					$copyObj->addAuthorArticle($relObj->copy($deepCopy));
				}
			}

		} 

		$copyObj->setNew(true);

		$copyObj->setId(NULL); 
	}

	
	public function copy($deepCopy = false)
	{
				$clazz = get_class($this);
		$copyObj = new $clazz();
		$this->copyInto($copyObj, $deepCopy);
		return $copyObj;
	}

	
	public function getPeer()
	{
		if (self::$peer === null) {
			self::$peer = new AuthorPeer();
		}
		return self::$peer;
	}

	
	public function clearAuthorArticles()
	{
		$this->collAuthorArticles = null; 	}

	
	public function initAuthorArticles()
	{
		$this->collAuthorArticles = array();
	}

	
	public function getAuthorArticles($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(AuthorPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collAuthorArticles === null) {
			if ($this->isNew()) {
			   $this->collAuthorArticles = array();
			} else {

				$criteria->add(AuthorArticlePeer::AUTHOR_ID, $this->id);

				AuthorArticlePeer::addSelectColumns($criteria);
				$this->collAuthorArticles = AuthorArticlePeer::doSelect($criteria, $con);
			}
		} else {
						if (!$this->isNew()) {
												

				$criteria->add(AuthorArticlePeer::AUTHOR_ID, $this->id);

				AuthorArticlePeer::addSelectColumns($criteria);
				if (!isset($this->lastAuthorArticleCriteria) || !$this->lastAuthorArticleCriteria->equals($criteria)) {
					$this->collAuthorArticles = AuthorArticlePeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastAuthorArticleCriteria = $criteria;
		return $this->collAuthorArticles;
	}

	
	public function countAuthorArticles(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(AuthorPeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collAuthorArticles === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(AuthorArticlePeer::AUTHOR_ID, $this->id);

				$count = AuthorArticlePeer::doCount($criteria, $con);
			}
		} else {
						if (!$this->isNew()) {
												

				$criteria->add(AuthorArticlePeer::AUTHOR_ID, $this->id);

				if (!isset($this->lastAuthorArticleCriteria) || !$this->lastAuthorArticleCriteria->equals($criteria)) {
					$count = AuthorArticlePeer::doCount($criteria, $con);
				} else {
					$count = count($this->collAuthorArticles);
				}
			} else {
				$count = count($this->collAuthorArticles);
			}
		}
		return $count;
	}

	
	public function addAuthorArticle(AuthorArticle $l)
	{
		if ($this->collAuthorArticles === null) {
			$this->initAuthorArticles();
		}
		if (!in_array($l, $this->collAuthorArticles, true)) { 			array_push($this->collAuthorArticles, $l);
			$l->setAuthor($this);
		}
	}


	
	public function getAuthorArticlesJoinArticle($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		if ($criteria === null) {
			$criteria = new Criteria(AuthorPeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collAuthorArticles === null) {
			if ($this->isNew()) {
				$this->collAuthorArticles = array();
			} else {

				$criteria->add(AuthorArticlePeer::AUTHOR_ID, $this->id);

				$this->collAuthorArticles = AuthorArticlePeer::doSelectJoinArticle($criteria, $con, $join_behavior);
			}
		} else {
									
			$criteria->add(AuthorArticlePeer::AUTHOR_ID, $this->id);

			if (!isset($this->lastAuthorArticleCriteria) || !$this->lastAuthorArticleCriteria->equals($criteria)) {
				$this->collAuthorArticles = AuthorArticlePeer::doSelectJoinArticle($criteria, $con, $join_behavior);
			}
		}
		$this->lastAuthorArticleCriteria = $criteria;

		return $this->collAuthorArticles;
	}

	
	public function clearAllReferences($deep = false)
	{
		if ($deep) {
			if ($this->collAuthorArticles) {
				foreach ((array) $this->collAuthorArticles as $o) {
					$o->clearAllReferences($deep);
				}
			}
		} 
		$this->collAuthorArticles = null;
	}


  public function __call($method, $arguments)
  {
    if (!$callable = sfMixer::getCallable('BaseAuthor:'.$method))
    {
      throw new sfException(sprintf('Call to undefined method BaseAuthor::%s', $method));
    }

    array_unshift($arguments, $this);

    return call_user_func_array($callable, $arguments);
  }


} 