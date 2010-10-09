<?php


abstract class BaseAuthorArticle extends BaseObject  implements Persistent {


  const PEER = 'AuthorArticlePeer';

	
	protected static $peer;

	
	protected $author_id;

	
	protected $article_id;

	
	protected $aAuthor;

	
	protected $aArticle;

	
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

	
	public function getAuthorId()
	{
		return $this->author_id;
	}

	
	public function getArticleId()
	{
		return $this->article_id;
	}

	
	public function setAuthorId($v)
	{
		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->author_id !== $v) {
			$this->author_id = $v;
			$this->modifiedColumns[] = AuthorArticlePeer::AUTHOR_ID;
		}

		if ($this->aAuthor !== null && $this->aAuthor->getId() !== $v) {
			$this->aAuthor = null;
		}

		return $this;
	} 
	
	public function setArticleId($v)
	{
		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->article_id !== $v) {
			$this->article_id = $v;
			$this->modifiedColumns[] = AuthorArticlePeer::ARTICLE_ID;
		}

		if ($this->aArticle !== null && $this->aArticle->getId() !== $v) {
			$this->aArticle = null;
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

			$this->author_id = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
			$this->article_id = ($row[$startcol + 1] !== null) ? (int) $row[$startcol + 1] : null;
			$this->resetModified();

			$this->setNew(false);

			if ($rehydrate) {
				$this->ensureConsistency();
			}

						return $startcol + 2; 
		} catch (Exception $e) {
			throw new PropelException("Error populating AuthorArticle object", $e);
		}
	}

	
	public function ensureConsistency()
	{

		if ($this->aAuthor !== null && $this->author_id !== $this->aAuthor->getId()) {
			$this->aAuthor = null;
		}
		if ($this->aArticle !== null && $this->article_id !== $this->aArticle->getId()) {
			$this->aArticle = null;
		}
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
			$con = Propel::getConnection(AuthorArticlePeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

				
		$stmt = AuthorArticlePeer::doSelectStmt($this->buildPkeyCriteria(), $con);
		$row = $stmt->fetch(PDO::FETCH_NUM);
		$stmt->closeCursor();
		if (!$row) {
			throw new PropelException('Cannot find matching row in the database to reload object values.');
		}
		$this->hydrate($row, 0, true); 
		if ($deep) {  
			$this->aAuthor = null;
			$this->aArticle = null;
		} 	}

	
	public function delete(PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseAuthorArticle:delete:pre') as $callable)
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
			$con = Propel::getConnection(AuthorArticlePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		$con->beginTransaction();
		try {
			AuthorArticlePeer::doDelete($this, $con);
			$this->setDeleted(true);
			$con->commit();
		} catch (PropelException $e) {
			$con->rollBack();
			throw $e;
		}
	

    foreach (sfMixer::getCallables('BaseAuthorArticle:delete:post') as $callable)
    {
      call_user_func($callable, $this, $con);
    }

  }
	
	public function save(PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseAuthorArticle:save:pre') as $callable)
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
			$con = Propel::getConnection(AuthorArticlePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		$con->beginTransaction();
		try {
			$affectedRows = $this->doSave($con);
			$con->commit();
    foreach (sfMixer::getCallables('BaseAuthorArticle:save:post') as $callable)
    {
      call_user_func($callable, $this, $con, $affectedRows);
    }

			AuthorArticlePeer::addInstanceToPool($this);
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

												
			if ($this->aAuthor !== null) {
				if ($this->aAuthor->isModified() || $this->aAuthor->isNew()) {
					$affectedRows += $this->aAuthor->save($con);
				}
				$this->setAuthor($this->aAuthor);
			}

			if ($this->aArticle !== null) {
				if ($this->aArticle->isModified() || $this->aArticle->isNew()) {
					$affectedRows += $this->aArticle->save($con);
				}
				$this->setArticle($this->aArticle);
			}


						if ($this->isModified()) {
				if ($this->isNew()) {
					$pk = AuthorArticlePeer::doInsert($this, $con);
					$affectedRows += 1; 										 										 
					$this->setNew(false);
				} else {
					$affectedRows += AuthorArticlePeer::doUpdate($this, $con);
				}

				$this->resetModified(); 			}

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


												
			if ($this->aAuthor !== null) {
				if (!$this->aAuthor->validate($columns)) {
					$failureMap = array_merge($failureMap, $this->aAuthor->getValidationFailures());
				}
			}

			if ($this->aArticle !== null) {
				if (!$this->aArticle->validate($columns)) {
					$failureMap = array_merge($failureMap, $this->aArticle->getValidationFailures());
				}
			}


			if (($retval = AuthorArticlePeer::doValidate($this, $columns)) !== true) {
				$failureMap = array_merge($failureMap, $retval);
			}



			$this->alreadyInValidation = false;
		}

		return (!empty($failureMap) ? $failureMap : true);
	}

	
	public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = AuthorArticlePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		$field = $this->getByPosition($pos);
		return $field;
	}

	
	public function getByPosition($pos)
	{
		switch($pos) {
			case 0:
				return $this->getAuthorId();
				break;
			case 1:
				return $this->getArticleId();
				break;
			default:
				return null;
				break;
		} 	}

	
	public function toArray($keyType = BasePeer::TYPE_PHPNAME, $includeLazyLoadColumns = true)
	{
		$keys = AuthorArticlePeer::getFieldNames($keyType);
		$result = array(
			$keys[0] => $this->getAuthorId(),
			$keys[1] => $this->getArticleId(),
		);
		return $result;
	}

	
	public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = AuthorArticlePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		return $this->setByPosition($pos, $value);
	}

	
	public function setByPosition($pos, $value)
	{
		switch($pos) {
			case 0:
				$this->setAuthorId($value);
				break;
			case 1:
				$this->setArticleId($value);
				break;
		} 	}

	
	public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = AuthorArticlePeer::getFieldNames($keyType);

		if (array_key_exists($keys[0], $arr)) $this->setAuthorId($arr[$keys[0]]);
		if (array_key_exists($keys[1], $arr)) $this->setArticleId($arr[$keys[1]]);
	}

	
	public function buildCriteria()
	{
		$criteria = new Criteria(AuthorArticlePeer::DATABASE_NAME);

		if ($this->isColumnModified(AuthorArticlePeer::AUTHOR_ID)) $criteria->add(AuthorArticlePeer::AUTHOR_ID, $this->author_id);
		if ($this->isColumnModified(AuthorArticlePeer::ARTICLE_ID)) $criteria->add(AuthorArticlePeer::ARTICLE_ID, $this->article_id);

		return $criteria;
	}

	
	public function buildPkeyCriteria()
	{
		$criteria = new Criteria(AuthorArticlePeer::DATABASE_NAME);

		$criteria->add(AuthorArticlePeer::AUTHOR_ID, $this->author_id);
		$criteria->add(AuthorArticlePeer::ARTICLE_ID, $this->article_id);

		return $criteria;
	}

	
	public function getPrimaryKey()
	{
		$pks = array();

		$pks[0] = $this->getAuthorId();

		$pks[1] = $this->getArticleId();

		return $pks;
	}

	
	public function setPrimaryKey($keys)
	{

		$this->setAuthorId($keys[0]);

		$this->setArticleId($keys[1]);

	}

	
	public function copyInto($copyObj, $deepCopy = false)
	{

		$copyObj->setAuthorId($this->author_id);

		$copyObj->setArticleId($this->article_id);


		$copyObj->setNew(true);

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
			self::$peer = new AuthorArticlePeer();
		}
		return self::$peer;
	}

	
	public function setAuthor(Author $v = null)
	{
		if ($v === null) {
			$this->setAuthorId(NULL);
		} else {
			$this->setAuthorId($v->getId());
		}

		$this->aAuthor = $v;

						if ($v !== null) {
			$v->addAuthorArticle($this);
		}

		return $this;
	}


	
	public function getAuthor(PropelPDO $con = null)
	{
		if ($this->aAuthor === null && ($this->author_id !== null)) {
			$c = new Criteria(AuthorPeer::DATABASE_NAME);
			$c->add(AuthorPeer::ID, $this->author_id);
			$this->aAuthor = AuthorPeer::doSelectOne($c, $con);
			
		}
		return $this->aAuthor;
	}

	
	public function setArticle(Article $v = null)
	{
		if ($v === null) {
			$this->setArticleId(NULL);
		} else {
			$this->setArticleId($v->getId());
		}

		$this->aArticle = $v;

						if ($v !== null) {
			$v->addAuthorArticle($this);
		}

		return $this;
	}


	
	public function getArticle(PropelPDO $con = null)
	{
		if ($this->aArticle === null && ($this->article_id !== null)) {
			$c = new Criteria(ArticlePeer::DATABASE_NAME);
			$c->add(ArticlePeer::ID, $this->article_id);
			$this->aArticle = ArticlePeer::doSelectOne($c, $con);
			
		}
		return $this->aArticle;
	}

	
	public function clearAllReferences($deep = false)
	{
		if ($deep) {
		} 
			$this->aAuthor = null;
			$this->aArticle = null;
	}


  public function __call($method, $arguments)
  {
    if (!$callable = sfMixer::getCallable('BaseAuthorArticle:'.$method))
    {
      throw new sfException(sprintf('Call to undefined method BaseAuthorArticle::%s', $method));
    }

    array_unshift($arguments, $this);

    return call_user_func_array($callable, $arguments);
  }


} 