<?php


abstract class BaseArticle extends BaseObject  implements Persistent {


	
	protected static $peer;


	
	protected $id;


	
	protected $title;


	
	protected $body;


	
	protected $online;


	
	protected $category_id;


	
	protected $created_at;


	
	protected $end_date;


	
	protected $book_id;

	
	protected $aCategory;

	
	protected $aBook;

	
	protected $collAuthorArticles;

	
	protected $lastAuthorArticleCriteria = null;

	
	protected $alreadyInSave = false;

	
	protected $alreadyInValidation = false;

	
	public function getId()
	{

		return $this->id;
	}

	
	public function getTitle()
	{

		return $this->title;
	}

	
	public function getBody()
	{

		return $this->body;
	}

	
	public function getOnline()
	{

		return $this->online;
	}

	
	public function getCategoryId()
	{

		return $this->category_id;
	}

	
	public function getCreatedAt($format = 'Y-m-d H:i:s')
	{

		if ($this->created_at === null || $this->created_at === '') {
			return null;
		} elseif (!is_int($this->created_at)) {
						$ts = strtotime($this->created_at);
			if ($ts === -1 || $ts === false) { 				throw new PropelException("Unable to parse value of [created_at] as date/time value: " . var_export($this->created_at, true));
			}
		} else {
			$ts = $this->created_at;
		}
		if ($format === null) {
			return $ts;
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $ts);
		} else {
			return date($format, $ts);
		}
	}

	
	public function getEndDate($format = 'Y-m-d H:i:s')
	{

		if ($this->end_date === null || $this->end_date === '') {
			return null;
		} elseif (!is_int($this->end_date)) {
						$ts = strtotime($this->end_date);
			if ($ts === -1 || $ts === false) { 				throw new PropelException("Unable to parse value of [end_date] as date/time value: " . var_export($this->end_date, true));
			}
		} else {
			$ts = $this->end_date;
		}
		if ($format === null) {
			return $ts;
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $ts);
		} else {
			return date($format, $ts);
		}
	}

	
	public function getBookId()
	{

		return $this->book_id;
	}

	
	public function setId($v)
	{

						if ($v !== null && !is_int($v) && is_numeric($v)) {
			$v = (int) $v;
		}

		if ($this->id !== $v) {
			$this->id = $v;
			$this->modifiedColumns[] = ArticlePeer::ID;
		}

	} 
	
	public function setTitle($v)
	{

						if ($v !== null && !is_string($v)) {
			$v = (string) $v; 
		}

		if ($this->title !== $v) {
			$this->title = $v;
			$this->modifiedColumns[] = ArticlePeer::TITLE;
		}

	} 
	
	public function setBody($v)
	{

						if ($v !== null && !is_string($v)) {
			$v = (string) $v; 
		}

		if ($this->body !== $v) {
			$this->body = $v;
			$this->modifiedColumns[] = ArticlePeer::BODY;
		}

	} 
	
	public function setOnline($v)
	{

		if ($this->online !== $v) {
			$this->online = $v;
			$this->modifiedColumns[] = ArticlePeer::ONLINE;
		}

	} 
	
	public function setCategoryId($v)
	{

						if ($v !== null && !is_int($v) && is_numeric($v)) {
			$v = (int) $v;
		}

		if ($this->category_id !== $v) {
			$this->category_id = $v;
			$this->modifiedColumns[] = ArticlePeer::CATEGORY_ID;
		}

		if ($this->aCategory !== null && $this->aCategory->getId() !== $v) {
			$this->aCategory = null;
		}

	} 
	
	public function setCreatedAt($v)
	{

		if ($v !== null && !is_int($v)) {
			$ts = strtotime($v);
			if ($ts === -1 || $ts === false) { 				throw new PropelException("Unable to parse date/time value for [created_at] from input: " . var_export($v, true));
			}
		} else {
			$ts = $v;
		}
		if ($this->created_at !== $ts) {
			$this->created_at = $ts;
			$this->modifiedColumns[] = ArticlePeer::CREATED_AT;
		}

	} 
	
	public function setEndDate($v)
	{

		if ($v !== null && !is_int($v)) {
			$ts = strtotime($v);
			if ($ts === -1 || $ts === false) { 				throw new PropelException("Unable to parse date/time value for [end_date] from input: " . var_export($v, true));
			}
		} else {
			$ts = $v;
		}
		if ($this->end_date !== $ts) {
			$this->end_date = $ts;
			$this->modifiedColumns[] = ArticlePeer::END_DATE;
		}

	} 
	
	public function setBookId($v)
	{

						if ($v !== null && !is_int($v) && is_numeric($v)) {
			$v = (int) $v;
		}

		if ($this->book_id !== $v) {
			$this->book_id = $v;
			$this->modifiedColumns[] = ArticlePeer::BOOK_ID;
		}

		if ($this->aBook !== null && $this->aBook->getId() !== $v) {
			$this->aBook = null;
		}

	} 
	
	public function hydrate(ResultSet $rs, $startcol = 1)
	{
		try {

			$this->id = $rs->getInt($startcol + 0);

			$this->title = $rs->getString($startcol + 1);

			$this->body = $rs->getString($startcol + 2);

			$this->online = $rs->getBoolean($startcol + 3);

			$this->category_id = $rs->getInt($startcol + 4);

			$this->created_at = $rs->getTimestamp($startcol + 5, null);

			$this->end_date = $rs->getTimestamp($startcol + 6, null);

			$this->book_id = $rs->getInt($startcol + 7);

			$this->resetModified();

			$this->setNew(false);

						return $startcol + 8; 
		} catch (Exception $e) {
			throw new PropelException("Error populating Article object", $e);
		}
	}

	
	public function delete($con = null)
	{
		if ($this->isDeleted()) {
			throw new PropelException("This object has already been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME);
		}

		try {
			$con->begin();
			ArticlePeer::doDelete($this, $con);
			$this->setDeleted(true);
			$con->commit();
		} catch (PropelException $e) {
			$con->rollback();
			throw $e;
		}
	}

	
	public function save($con = null)
	{
    if ($this->isNew() && !$this->isColumnModified(ArticlePeer::CREATED_AT))
    {
      $this->setCreatedAt(time());
    }

		if ($this->isDeleted()) {
			throw new PropelException("You cannot save an object that has been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME);
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


												
			if ($this->aCategory !== null) {
				if ($this->aCategory->isModified()) {
					$affectedRows += $this->aCategory->save($con);
				}
				$this->setCategory($this->aCategory);
			}

			if ($this->aBook !== null) {
				if ($this->aBook->isModified()) {
					$affectedRows += $this->aBook->save($con);
				}
				$this->setBook($this->aBook);
			}


						if ($this->isModified()) {
				if ($this->isNew()) {
					$pk = ArticlePeer::doInsert($this, $con);
					$affectedRows += 1; 										 										 
					$this->setId($pk);  
					$this->setNew(false);
				} else {
					$affectedRows += ArticlePeer::doUpdate($this, $con);
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


												
			if ($this->aCategory !== null) {
				if (!$this->aCategory->validate($columns)) {
					$failureMap = array_merge($failureMap, $this->aCategory->getValidationFailures());
				}
			}

			if ($this->aBook !== null) {
				if (!$this->aBook->validate($columns)) {
					$failureMap = array_merge($failureMap, $this->aBook->getValidationFailures());
				}
			}


			if (($retval = ArticlePeer::doValidate($this, $columns)) !== true) {
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
		$pos = ArticlePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		return $this->getByPosition($pos);
	}

	
	public function getByPosition($pos)
	{
		switch($pos) {
			case 0:
				return $this->getId();
				break;
			case 1:
				return $this->getTitle();
				break;
			case 2:
				return $this->getBody();
				break;
			case 3:
				return $this->getOnline();
				break;
			case 4:
				return $this->getCategoryId();
				break;
			case 5:
				return $this->getCreatedAt();
				break;
			case 6:
				return $this->getEndDate();
				break;
			case 7:
				return $this->getBookId();
				break;
			default:
				return null;
				break;
		} 	}

	
	public function toArray($keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = ArticlePeer::getFieldNames($keyType);
		$result = array(
			$keys[0] => $this->getId(),
			$keys[1] => $this->getTitle(),
			$keys[2] => $this->getBody(),
			$keys[3] => $this->getOnline(),
			$keys[4] => $this->getCategoryId(),
			$keys[5] => $this->getCreatedAt(),
			$keys[6] => $this->getEndDate(),
			$keys[7] => $this->getBookId(),
		);
		return $result;
	}

	
	public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
	{
		$pos = ArticlePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
		return $this->setByPosition($pos, $value);
	}

	
	public function setByPosition($pos, $value)
	{
		switch($pos) {
			case 0:
				$this->setId($value);
				break;
			case 1:
				$this->setTitle($value);
				break;
			case 2:
				$this->setBody($value);
				break;
			case 3:
				$this->setOnline($value);
				break;
			case 4:
				$this->setCategoryId($value);
				break;
			case 5:
				$this->setCreatedAt($value);
				break;
			case 6:
				$this->setEndDate($value);
				break;
			case 7:
				$this->setBookId($value);
				break;
		} 	}

	
	public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
	{
		$keys = ArticlePeer::getFieldNames($keyType);

		if (array_key_exists($keys[0], $arr)) $this->setId($arr[$keys[0]]);
		if (array_key_exists($keys[1], $arr)) $this->setTitle($arr[$keys[1]]);
		if (array_key_exists($keys[2], $arr)) $this->setBody($arr[$keys[2]]);
		if (array_key_exists($keys[3], $arr)) $this->setOnline($arr[$keys[3]]);
		if (array_key_exists($keys[4], $arr)) $this->setCategoryId($arr[$keys[4]]);
		if (array_key_exists($keys[5], $arr)) $this->setCreatedAt($arr[$keys[5]]);
		if (array_key_exists($keys[6], $arr)) $this->setEndDate($arr[$keys[6]]);
		if (array_key_exists($keys[7], $arr)) $this->setBookId($arr[$keys[7]]);
	}

	
	public function buildCriteria()
	{
		$criteria = new Criteria(ArticlePeer::DATABASE_NAME);

		if ($this->isColumnModified(ArticlePeer::ID)) $criteria->add(ArticlePeer::ID, $this->id);
		if ($this->isColumnModified(ArticlePeer::TITLE)) $criteria->add(ArticlePeer::TITLE, $this->title);
		if ($this->isColumnModified(ArticlePeer::BODY)) $criteria->add(ArticlePeer::BODY, $this->body);
		if ($this->isColumnModified(ArticlePeer::ONLINE)) $criteria->add(ArticlePeer::ONLINE, $this->online);
		if ($this->isColumnModified(ArticlePeer::CATEGORY_ID)) $criteria->add(ArticlePeer::CATEGORY_ID, $this->category_id);
		if ($this->isColumnModified(ArticlePeer::CREATED_AT)) $criteria->add(ArticlePeer::CREATED_AT, $this->created_at);
		if ($this->isColumnModified(ArticlePeer::END_DATE)) $criteria->add(ArticlePeer::END_DATE, $this->end_date);
		if ($this->isColumnModified(ArticlePeer::BOOK_ID)) $criteria->add(ArticlePeer::BOOK_ID, $this->book_id);

		return $criteria;
	}

	
	public function buildPkeyCriteria()
	{
		$criteria = new Criteria(ArticlePeer::DATABASE_NAME);

		$criteria->add(ArticlePeer::ID, $this->id);

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

		$copyObj->setTitle($this->title);

		$copyObj->setBody($this->body);

		$copyObj->setOnline($this->online);

		$copyObj->setCategoryId($this->category_id);

		$copyObj->setCreatedAt($this->created_at);

		$copyObj->setEndDate($this->end_date);

		$copyObj->setBookId($this->book_id);


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
			self::$peer = new ArticlePeer();
		}
		return self::$peer;
	}

	
	public function setCategory($v)
	{


		if ($v === null) {
			$this->setCategoryId(NULL);
		} else {
			$this->setCategoryId($v->getId());
		}


		$this->aCategory = $v;
	}


	
	public function getCategory($con = null)
	{
				include_once 'lib/model/om/BaseCategoryPeer.php';

		if ($this->aCategory === null && ($this->category_id !== null)) {

			$this->aCategory = CategoryPeer::retrieveByPK($this->category_id, $con);

			
		}
		return $this->aCategory;
	}

	
	public function setBook($v)
	{


		if ($v === null) {
			$this->setBookId(NULL);
		} else {
			$this->setBookId($v->getId());
		}


		$this->aBook = $v;
	}


	
	public function getBook($con = null)
	{
				include_once 'lib/model/om/BaseBookPeer.php';

		if ($this->aBook === null && ($this->book_id !== null)) {

			$this->aBook = BookPeer::retrieveByPK($this->book_id, $con);

			
		}
		return $this->aBook;
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

				$criteria->add(AuthorArticlePeer::ARTICLE_ID, $this->getId());

				AuthorArticlePeer::addSelectColumns($criteria);
				$this->collAuthorArticles = AuthorArticlePeer::doSelect($criteria, $con);
			}
		} else {
						if (!$this->isNew()) {
												

				$criteria->add(AuthorArticlePeer::ARTICLE_ID, $this->getId());

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

		$criteria->add(AuthorArticlePeer::ARTICLE_ID, $this->getId());

		return AuthorArticlePeer::doCount($criteria, $distinct, $con);
	}

	
	public function addAuthorArticle(AuthorArticle $l)
	{
		$this->collAuthorArticles[] = $l;
		$l->setArticle($this);
	}


	
	public function getAuthorArticlesJoinAuthor($criteria = null, $con = null)
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

				$criteria->add(AuthorArticlePeer::ARTICLE_ID, $this->getId());

				$this->collAuthorArticles = AuthorArticlePeer::doSelectJoinAuthor($criteria, $con);
			}
		} else {
									
			$criteria->add(AuthorArticlePeer::ARTICLE_ID, $this->getId());

			if (!isset($this->lastAuthorArticleCriteria) || !$this->lastAuthorArticleCriteria->equals($criteria)) {
				$this->collAuthorArticles = AuthorArticlePeer::doSelectJoinAuthor($criteria, $con);
			}
		}
		$this->lastAuthorArticleCriteria = $criteria;

		return $this->collAuthorArticles;
	}

} 