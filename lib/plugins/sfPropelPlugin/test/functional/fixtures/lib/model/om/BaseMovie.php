<?php


abstract class BaseMovie extends BaseObject  implements Persistent {


  const PEER = 'MoviePeer';

	
	protected static $peer;

	
	protected $id;

	
	protected $director;

	
	protected $collMovieI18ns;

	
	private $lastMovieI18nCriteria = null;

	
	protected $alreadyInSave = false;

	
	protected $alreadyInValidation = false;

  
  protected $culture;

	
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

	
	public function getDirector()
	{
		return $this->director;
	}

	
	public function setId($v)
	{
		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->id !== $v) {
			$this->id = $v;
			$this->modifiedColumns[] = MoviePeer::ID;
		}

		return $this;
	} 
	
	public function setDirector($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->director !== $v) {
			$this->director = $v;
			$this->modifiedColumns[] = MoviePeer::DIRECTOR;
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
			$this->director = ($row[$startcol + 1] !== null) ? (string) $row[$startcol + 1] : null;
			$this->resetModified();

			$this->setNew(false);

			if ($rehydrate) {
				$this->ensureConsistency();
			}

						return $startcol + 2; 
		} catch (Exception $e) {
			throw new PropelException("Error populating Movie object", $e);
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
			$con = Propel::getConnection(MoviePeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

				
		$stmt = MoviePeer::doSelectStmt($this->buildPkeyCriteria(), $con);
		$row = $stmt->fetch(PDO::FETCH_NUM);
		$stmt->closeCursor();
		if (!$row) {
			throw new PropelException('Cannot find matching row in the database to reload object values.');
		}
		$this->hydrate($row, 0, true); 
		if ($deep) {  
			$this->collMovieI18ns = null;
			$this->lastMovieI18nCriteria = null;

		} 	}

	
	public function delete(PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseMovie:delete:pre') as $callable)
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
			$con = Propel::getConnection(MoviePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		$con->beginTransaction();
		try {
			MoviePeer::doDelete($this, $con);
			$this->setDeleted(true);
			$con->commit();
		} catch (PropelException $e) {
			$con->rollBack();
			throw $e;
		}
	

    foreach (sfMixer::getCallables('BaseMovie:delete:post') as $callable)
    {
      call_user_func($callable, $this, $con);
    }

  }
	
	public function save(PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseMovie:save:pre') as $callable)
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
			$con = Propel::getConnection(MoviePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		$con->beginTransaction();
		try {
			$affectedRows = $this->doSave($con);
			$con->commit();
    foreach (sfMixer::getCallables('BaseMovie:save:post') as $callable)
    {
      call_user_func($callable, $this, $con, $affectedRows);
    }

			MoviePeer::addInstanceToPool($this);
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
				$this->modifiedColumns[] = MoviePeer::ID;
			}

						if ($this->isModified()) {
				if ($this->isNew()) {
					$pk = MoviePeer::doInsert($this, $con);
					$affectedRows += 1; 										 										 
					$this->setId($pk);  
					$this->setNew(false);
				} else {
					$affectedRows += MoviePeer::doUpdate($this, $con);
				}

				$this->resetModified(); 			}

			if ($this->collMovieI18ns !== null) {
				foreach ($this->collMovieI18ns as $referrerFK) {
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


			if (($retval = MoviePeer::doValidate($this, $columns)) !== true) {
				$failureMap = array_merge($failureMap, $retval);
			}


				if ($this->collMovieI18ns !== null) {
					foreach ($this->collMovieI18ns as $referrerFK) {
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
		$pos = MoviePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
				return $this->getDirector();
				break;
			default:
				return null;
				break;
		} 	}

	
	public function toArray($keyType = BasePeer::TYPE_PHPNAME, $includeLazyLoadColumns = true)
	{
		$keys = MoviePeer::getFieldNames($keyType);
		$result = array(
			$keys[0] => $this->getId(),
			$keys[1] => $this->getDirector(),
		);
		return $result;
	}

	
	public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = MoviePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		return $this->setByPosition($pos, $value);
	}

	
	public function setByPosition($pos, $value)
	{
		switch($pos) {
			case 0:
				$this->setId($value);
				break;
			case 1:
				$this->setDirector($value);
				break;
		} 	}

	
	public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = MoviePeer::getFieldNames($keyType);

		if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
		if (array_key_exists($keys[1], $arr)) $this->setDirector($arr[$keys[1]]);
	}

	
	public function buildCriteria()
	{
		$criteria = new Criteria(MoviePeer::DATABASE_NAME);

		if ($this->isColumnModified(MoviePeer::ID)) $criteria->add(MoviePeer::ID, $this->id);
		if ($this->isColumnModified(MoviePeer::DIRECTOR)) $criteria->add(MoviePeer::DIRECTOR, $this->director);

		return $criteria;
	}

	
	public function buildPkeyCriteria()
	{
		$criteria = new Criteria(MoviePeer::DATABASE_NAME);

		$criteria->add(MoviePeer::ID, $this->id);

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

		$copyObj->setDirector($this->director);


		if ($deepCopy) {
									$copyObj->setNew(false);

			foreach ($this->getMovieI18ns() as $relObj) {
				if ($relObj !== $this) {  					$copyObj->addMovieI18n($relObj->copy($deepCopy));
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
			self::$peer = new MoviePeer();
		}
		return self::$peer;
	}

	
	public function clearMovieI18ns()
	{
		$this->collMovieI18ns = null; 	}

	
	public function initMovieI18ns()
	{
		$this->collMovieI18ns = array();
	}

	
	public function getMovieI18ns($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(MoviePeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collMovieI18ns === null) {
			if ($this->isNew()) {
			   $this->collMovieI18ns = array();
			} else {

				$criteria->add(MovieI18nPeer::ID, $this->id);

				MovieI18nPeer::addSelectColumns($criteria);
				$this->collMovieI18ns = MovieI18nPeer::doSelect($criteria, $con);
			}
		} else {
						if (!$this->isNew()) {
												

				$criteria->add(MovieI18nPeer::ID, $this->id);

				MovieI18nPeer::addSelectColumns($criteria);
				if (!isset($this->lastMovieI18nCriteria) || !$this->lastMovieI18nCriteria->equals($criteria)) {
					$this->collMovieI18ns = MovieI18nPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastMovieI18nCriteria = $criteria;
		return $this->collMovieI18ns;
	}

	
	public function countMovieI18ns(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(MoviePeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collMovieI18ns === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(MovieI18nPeer::ID, $this->id);

				$count = MovieI18nPeer::doCount($criteria, $con);
			}
		} else {
						if (!$this->isNew()) {
												

				$criteria->add(MovieI18nPeer::ID, $this->id);

				if (!isset($this->lastMovieI18nCriteria) || !$this->lastMovieI18nCriteria->equals($criteria)) {
					$count = MovieI18nPeer::doCount($criteria, $con);
				} else {
					$count = count($this->collMovieI18ns);
				}
			} else {
				$count = count($this->collMovieI18ns);
			}
		}
		return $count;
	}

	
	public function addMovieI18n(MovieI18n $l)
	{
		if ($this->collMovieI18ns === null) {
			$this->initMovieI18ns();
		}
		if (!in_array($l, $this->collMovieI18ns, true)) { 			array_push($this->collMovieI18ns, $l);
			$l->setMovie($this);
		}
	}

	
	public function clearAllReferences($deep = false)
	{
		if ($deep) {
			if ($this->collMovieI18ns) {
				foreach ((array) $this->collMovieI18ns as $o) {
					$o->clearAllReferences($deep);
				}
			}
		} 
		$this->collMovieI18ns = null;
	}


  
  public function getCulture()
  {
    return $this->culture;
  }

  
  public function setCulture($culture)
  {
    $this->culture = $culture;
  }

  public function getTitle($culture = null)
  {
    return $this->getCurrentMovieI18n($culture)->getTitle();
  }

  public function setTitle($value, $culture = null)
  {
    $this->getCurrentMovieI18n($culture)->setTitle($value);
  }

  protected $current_i18n = array();

  public function getCurrentMovieI18n($culture = null)
  {
    if (is_null($culture))
    {
      $culture = is_null($this->culture) ? sfPropel::getDefaultCulture() : $this->culture;
    }

    if (!isset($this->current_i18n[$culture]))
    {
      $obj = MovieI18nPeer::retrieveByPK($this->getId(), $culture);
      if ($obj)
      {
        $this->setMovieI18nForCulture($obj, $culture);
      }
      else
      {
        $this->setMovieI18nForCulture(new MovieI18n(), $culture);
        $this->current_i18n[$culture]->setCulture($culture);
      }
    }

    return $this->current_i18n[$culture];
  }

  public function setMovieI18nForCulture($object, $culture)
  {
    $this->current_i18n[$culture] = $object;
    $this->addMovieI18n($object);
  }


  public function __call($method, $arguments)
  {
    if (!$callable = sfMixer::getCallable('BaseMovie:'.$method))
    {
      throw new sfException(sprintf('Call to undefined method BaseMovie::%s', $method));
    }

    array_unshift($arguments, $this);

    return call_user_func_array($callable, $arguments);
  }


} 