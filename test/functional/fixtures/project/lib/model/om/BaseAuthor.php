<?php


abstract class BaseAuthor extends BaseObject  implements Persistent {


	
	const DATABASE_NAME = 'propel';

	
	protected static $peer;


	
	protected $id;


	
	protected $name;

	
	protected $collAuthorArticles;

	
	protected $lastAuthorArticleCriteria = null;

	
	protected $alreadyInSave = false;

	
	protected $alreadyInValidation = false;

	
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

		if ($this->id !== $v) {
			$this->id = $v;
			$this->modifiedColumns[] = AuthorPeer::ID;
		}

	} 
	
	public function setName($v)
	{

		if ($this->name !== $v) {
			$this->name = $v;
			$this->modifiedColumns[] = AuthorPeer::NAME;
		}

	} 
	
	public function hydrate(ResultSet $rs, $startcol = 1)
	{
		try {

			$this->id = $rs->getInt($startcol + 0);

			$this->name = $rs->getString($startcol + 1);

			$this->resetModified();

			$this->setNew(false);

						return $startcol + 2; 
		} catch (Exception $e) {
			throw new PropelException("Error populating Author object", $e);
		}
	}

	
	public function delete($con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("This object has already been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(AuthorPeer::DATABASE_NAME);
		}

		try {
			$con->begin();
			AuthorPeer::doDelete($this, $con);
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
			$con = Propel::getConnection(AuthorPeer::DATABASE_NAME);
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
					$pk = AuthorPeer::doInsert($this, $con);
					$affectedRows += 1; 										 										 
					$this->setId($pk);  
					$this->setNew(false);
				} else {
					$affectedRows += AuthorPeer::doUpdate($this, $con);
				}
				$this->resetModified(); 			}

			if ($this->collAuthorArticles !== null) {
				foreach($this->collAuthorArticles as $referrerFK) {
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
					foreach($this->collAuthorArticles as $referrerFK) {
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
		return $this->getByPosition($pos);
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

	
	public function toArray($keyType = BasePeer::TYPE_PHPNAME)
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

			foreach($this->getAuthorArticles() as $relObj) {
				$copyObj->addAuthorArticle($relObj->copy($deepCopy));
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

	
	public function initAuthorArticles()
	{
		if ($this->collAuthorArticles === null) {
			$this->collAuthorArticles = array();
		}
	}

	
	public function getAuthorArticles($criteria = null, $con = null)
	{
				include_once 'lib/model/om/BaseAuthorArticlePeer.php';
		if ($criteria === null) {
			$criteria = new Criteria();
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collAuthorArticles === null) {
			if ($this->isNew()) {
			   $this->collAuthorArticles = array();
			} else {

				$criteria->add(AuthorArticlePeer::AUTHOR_ID, $this->getId());

				AuthorArticlePeer::addSelectColumns($criteria);
				$this->collAuthorArticles = AuthorArticlePeer::doSelect($criteria, $con);
			}
		} else {
						if (!$this->isNew()) {
												

				$criteria->add(AuthorArticlePeer::AUTHOR_ID, $this->getId());

				AuthorArticlePeer::addSelectColumns($criteria);
				if (!isset($this->lastAuthorArticleCriteria) || !$this->lastAuthorArticleCriteria->equals($criteria)) {
					$this->collAuthorArticles = AuthorArticlePeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastAuthorArticleCriteria = $criteria;
		return $this->collAuthorArticles;
	}

	
	public function countAuthorArticles($criteria = null, $distinct = false, $con = null)
	{
				include_once 'lib/model/om/BaseAuthorArticlePeer.php';
		if ($criteria === null) {
			$criteria = new Criteria();
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		$criteria->add(AuthorArticlePeer::AUTHOR_ID, $this->getId());

		return AuthorArticlePeer::doCount($criteria, $distinct, $con);
	}

	
	public function addAuthorArticle(AuthorArticle $l)
	{
		$this->collAuthorArticles[] = $l;
		$l->setAuthor($this);
	}


	
	public function getAuthorArticlesJoinArticle($criteria = null, $con = null)
	{
				include_once 'lib/model/om/BaseAuthorArticlePeer.php';
		if ($criteria === null) {
			$criteria = new Criteria();
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collAuthorArticles === null) {
			if ($this->isNew()) {
				$this->collAuthorArticles = array();
			} else {

				$criteria->add(AuthorArticlePeer::AUTHOR_ID, $this->getId());

				$this->collAuthorArticles = AuthorArticlePeer::doSelectJoinArticle($criteria, $con);
			}
		} else {
									
			$criteria->add(AuthorArticlePeer::AUTHOR_ID, $this->getId());

			if (!isset($this->lastAuthorArticleCriteria) || !$this->lastAuthorArticleCriteria->equals($criteria)) {
				$this->collAuthorArticles = AuthorArticlePeer::doSelectJoinArticle($criteria, $con);
			}
		}
		$this->lastAuthorArticleCriteria = $criteria;

		return $this->collAuthorArticles;
	}

} 