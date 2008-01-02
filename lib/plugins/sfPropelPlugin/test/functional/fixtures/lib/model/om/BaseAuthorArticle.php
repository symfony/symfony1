<?php


abstract class BaseAuthorArticle extends BaseObject  implements Persistent {


	
	protected static $peer;


	
	protected $author_id;


	
	protected $article_id;


	
	protected $id;

	
	protected $aAuthor;

	
	protected $aArticle;

	
	protected $alreadyInSave = false;

	
	protected $alreadyInValidation = false;

	
	public function getAuthorId()
	{

		return $this->author_id;
	}

	
	public function getArticleId()
	{

		return $this->article_id;
	}

	
	public function getId()
	{

		return $this->id;
	}

	
	public function setAuthorId($v)
	{

						if ($v !== null && !is_int($v) && is_numeric($v)) {
			$v = (int) $v;
		}

		if ($this->author_id !== $v) {
			$this->author_id = $v;
			$this->modifiedColumns[] = AuthorArticlePeer::AUTHOR_ID;
		}

		if ($this->aAuthor !== null && $this->aAuthor->getId() !== $v) {
			$this->aAuthor = null;
		}

	} 
	
	public function setArticleId($v)
	{

						if ($v !== null && !is_int($v) && is_numeric($v)) {
			$v = (int) $v;
		}

		if ($this->article_id !== $v) {
			$this->article_id = $v;
			$this->modifiedColumns[] = AuthorArticlePeer::ARTICLE_ID;
		}

		if ($this->aArticle !== null && $this->aArticle->getId() !== $v) {
			$this->aArticle = null;
		}

	} 
	
	public function setId($v)
	{

						if ($v !== null && !is_int($v) && is_numeric($v)) {
			$v = (int) $v;
		}

		if ($this->id !== $v) {
			$this->id = $v;
			$this->modifiedColumns[] = AuthorArticlePeer::ID;
		}

	} 
	
	public function hydrate(ResultSet $rs, $startcol = 1)
	{
		try {

			$this->author_id = $rs->getInt($startcol + 0);

			$this->article_id = $rs->getInt($startcol + 1);

			$this->id = $rs->getInt($startcol + 2);

			$this->resetModified();

			$this->setNew(false);

						return $startcol + 3; 
		} catch (Exception $e) {
			throw new PropelException("Error populating AuthorArticle object", $e);
		}
	}

	
	public function delete($con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("This object has already been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(AuthorArticlePeer::DATABASE_NAME);
		}

		try {
			$con->begin();
			AuthorArticlePeer::doDelete($this, $con);
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
			$con = Propel::getConnection(AuthorArticlePeer::DATABASE_NAME);
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


												
			if ($this->aAuthor !== null) {
				if ($this->aAuthor->isModified()) {
					$affectedRows += $this->aAuthor->save($con);
				}
				$this->setAuthor($this->aAuthor);
			}

			if ($this->aArticle !== null) {
				if ($this->aArticle->isModified()) {
					$affectedRows += $this->aArticle->save($con);
				}
				$this->setArticle($this->aArticle);
			}


						if ($this->isModified()) {
				if ($this->isNew()) {
					$pk = AuthorArticlePeer::doInsert($this, $con);
					$affectedRows += 1; 										 										 
					$this->setId($pk);  
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
		return $this->getByPosition($pos);
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
			case 2:
				return $this->getId();
				break;
			default:
				return null;
				break;
		} 	}

	
	public function toArray($keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = AuthorArticlePeer::getFieldNames($keyType);
		$result = array(
			$keys[0] => $this->getAuthorId(),
			$keys[1] => $this->getArticleId(),
			$keys[2] => $this->getId(),
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
			case 2:
				$this->setId($value);
				break;
		} 	}

	
	public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = AuthorArticlePeer::getFieldNames($keyType);

		if (array_key_exists($keys[0], $arr)) $this->setAuthorId($arr[$keys[0]]);
		if (array_key_exists($keys[1], $arr)) $this->setArticleId($arr[$keys[1]]);
		if (array_key_exists($keys[2], $arr)) $this->setId($arr[$keys[2]]);
	}

	
	public function buildCriteria()
	{
		$criteria = new Criteria(AuthorArticlePeer::DATABASE_NAME);

		if ($this->isColumnModified(AuthorArticlePeer::AUTHOR_ID)) $criteria->add(AuthorArticlePeer::AUTHOR_ID, $this->author_id);
		if ($this->isColumnModified(AuthorArticlePeer::ARTICLE_ID)) $criteria->add(AuthorArticlePeer::ARTICLE_ID, $this->article_id);
		if ($this->isColumnModified(AuthorArticlePeer::ID)) $criteria->add(AuthorArticlePeer::ID, $this->id);

		return $criteria;
	}

	
	public function buildPkeyCriteria()
	{
		$criteria = new Criteria(AuthorArticlePeer::DATABASE_NAME);

		$criteria->add(AuthorArticlePeer::ID, $this->id);

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

		$copyObj->setAuthorId($this->author_id);

		$copyObj->setArticleId($this->article_id);


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
			self::$peer = new AuthorArticlePeer();
		}
		return self::$peer;
	}

	
	public function setAuthor($v)
	{


		if ($v === null) {
			$this->setAuthorId(NULL);
		} else {
			$this->setAuthorId($v->getId());
		}


		$this->aAuthor = $v;
	}


	
	public function getAuthor($con = null)
	{
		if ($this->aAuthor === null && ($this->author_id !== null)) {
						$this->aAuthor = AuthorPeer::retrieveByPK($this->author_id, $con);

			
		}
		return $this->aAuthor;
	}

	
	public function setArticle($v)
	{


		if ($v === null) {
			$this->setArticleId(NULL);
		} else {
			$this->setArticleId($v->getId());
		}


		$this->aArticle = $v;
	}


	
	public function getArticle($con = null)
	{
		if ($this->aArticle === null && ($this->article_id !== null)) {
						$this->aArticle = ArticlePeer::retrieveByPK($this->article_id, $con);

			
		}
		return $this->aArticle;
	}

} 