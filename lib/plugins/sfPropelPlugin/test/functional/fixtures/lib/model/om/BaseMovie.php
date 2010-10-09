<?php


abstract class BaseMovie extends BaseObject  implements Persistent {


	
	protected static $peer;


	
	protected $id;


	
	protected $director;

	
	protected $collMovieI18ns;

	
	protected $lastMovieI18nCriteria = null;

	
	protected $alreadyInSave = false;

	
	protected $alreadyInValidation = false;

  
  protected $culture;

	
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

						if ($v !== null && !is_int($v) && is_numeric($v)) {
			$v = (int) $v;
		}

		if ($this->id !== $v) {
			$this->id = $v;
			$this->modifiedColumns[] = MoviePeer::ID;
		}

	} 
	
	public function setDirector($v)
	{

						if ($v !== null && !is_string($v)) {
			$v = (string) $v; 
		}

		if ($this->director !== $v) {
			$this->director = $v;
			$this->modifiedColumns[] = MoviePeer::DIRECTOR;
		}

	} 
	
	public function hydrate(ResultSet $rs, $startcol = 1)
	{
		try {

			$this->id = $rs->getInt($startcol + 0);

			$this->director = $rs->getString($startcol + 1);

			$this->resetModified();

			$this->setNew(false);

						return $startcol + 2; 
		} catch (Exception $e) {
			throw new PropelException("Error populating Movie object", $e);
		}
	}

	
	public function delete($con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("This object has already been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(MoviePeer::DATABASE_NAME);
		}

		try {
			$con->begin();
			MoviePeer::doDelete($this, $con);
			$this->setDeleted(true);
			$con->commit();
		} catch (PropelException $e) {
			$con->rollback();
			throw $e;
		}
	}

	
	public function save($con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("You cannot save an object that has been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(MoviePeer::DATABASE_NAME);
		}

		try {
			$con->begin();
			$affectedRows = $this->doSave($con);
			$con->commit();
			return $affectedRows;
		} catch (PropelException $e) {
			$con->rollback();
			throw $e;
		}
	}

	
	protected function doSave($con)
	{
		$affectedRows = 0; 		if (!$this->alreadyInSave) {
			$this->alreadyInSave = true;


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
				foreach($this->collMovieI18ns as $referrerFK) {
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
					foreach($this->collMovieI18ns as $referrerFK) {
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
		return $this->getByPosition($pos);
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

	
	public function toArray($keyType = BasePeer::TYPE_PHPNAME)
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

			foreach($this->getMovieI18ns() as $relObj) {
				$copyObj->addMovieI18n($relObj->copy($deepCopy));
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

	
	public function initMovieI18ns()
	{
		if ($this->collMovieI18ns === null) {
			$this->collMovieI18ns = array();
		}
	}

	
	public function getMovieI18ns($criteria = null, $con = null)
	{
				if ($criteria === null) {
			$criteria = new Criteria();
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collMovieI18ns === null) {
			if ($this->isNew()) {
			   $this->collMovieI18ns = array();
			} else {

				$criteria->add(MovieI18nPeer::ID, $this->getId());

				MovieI18nPeer::addSelectColumns($criteria);
				$this->collMovieI18ns = MovieI18nPeer::doSelect($criteria, $con);
			}
		} else {
						if (!$this->isNew()) {
												

				$criteria->add(MovieI18nPeer::ID, $this->getId());

				MovieI18nPeer::addSelectColumns($criteria);
				if (!isset($this->lastMovieI18nCriteria) || !$this->lastMovieI18nCriteria->equals($criteria)) {
					$this->collMovieI18ns = MovieI18nPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastMovieI18nCriteria = $criteria;
		return $this->collMovieI18ns;
	}

	
	public function countMovieI18ns($criteria = null, $distinct = false, $con = null)
	{
				if ($criteria === null) {
			$criteria = new Criteria();
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		$criteria->add(MovieI18nPeer::ID, $this->getId());

		return MovieI18nPeer::doCount($criteria, $distinct, $con);
	}

	
	public function addMovieI18n(MovieI18n $l)
	{
		$this->collMovieI18ns[] = $l;
		$l->setMovie($this);
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

} 