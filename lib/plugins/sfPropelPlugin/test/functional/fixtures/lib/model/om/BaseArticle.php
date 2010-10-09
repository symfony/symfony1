<?php


abstract class BaseArticle extends BaseObject  implements Persistent {


  const PEER = 'ArticlePeer';

	
	protected static $peer;

	
	protected $id;

	
	protected $title;

	
	protected $body;

	
	protected $online;

	
	protected $excerpt;

	
	protected $category_id;

	
	protected $created_at;

	
	protected $end_date;

	
	protected $book_id;

	
	protected $aCategory;

	
	protected $aBook;

	
	protected $collAuthorArticles;

	
	private $lastAuthorArticleCriteria = null;

	
	protected $collAttachments;

	
	private $lastAttachmentCriteria = null;

	
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

	
	public function getExcerpt()
	{
		return $this->excerpt;
	}

	
	public function getCategoryId()
	{
		return $this->category_id;
	}

	
	public function getCreatedAt($format = 'Y-m-d H:i:s')
	{
		if ($this->created_at === null) {
			return null;
		}



		try {
			$dt = new DateTime($this->created_at);
		} catch (Exception $x) {
			throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->created_at, true), $x);
		}

		if ($format === null) {
						return $dt;
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $dt->format('U'));
		} else {
			return $dt->format($format);
		}
	}

	
	public function getEndDate($format = 'Y-m-d H:i:s')
	{
		if ($this->end_date === null) {
			return null;
		}



		try {
			$dt = new DateTime($this->end_date);
		} catch (Exception $x) {
			throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->end_date, true), $x);
		}

		if ($format === null) {
						return $dt;
		} elseif (strpos($format, '%') !== false) {
			return strftime($format, $dt->format('U'));
		} else {
			return $dt->format($format);
		}
	}

	
	public function getBookId()
	{
		return $this->book_id;
	}

	
	public function setId($v)
	{
		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->id !== $v) {
			$this->id = $v;
			$this->modifiedColumns[] = ArticlePeer::ID;
		}

		return $this;
	} 
	
	public function setTitle($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->title !== $v) {
			$this->title = $v;
			$this->modifiedColumns[] = ArticlePeer::TITLE;
		}

		return $this;
	} 
	
	public function setBody($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->body !== $v) {
			$this->body = $v;
			$this->modifiedColumns[] = ArticlePeer::BODY;
		}

		return $this;
	} 
	
	public function setOnline($v)
	{
		if ($v !== null) {
			$v = (boolean) $v;
		}

		if ($this->online !== $v) {
			$this->online = $v;
			$this->modifiedColumns[] = ArticlePeer::ONLINE;
		}

		return $this;
	} 
	
	public function setExcerpt($v)
	{
		if ($v !== null) {
			$v = (string) $v;
		}

		if ($this->excerpt !== $v) {
			$this->excerpt = $v;
			$this->modifiedColumns[] = ArticlePeer::EXCERPT;
		}

		return $this;
	} 
	
	public function setCategoryId($v)
	{
		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->category_id !== $v) {
			$this->category_id = $v;
			$this->modifiedColumns[] = ArticlePeer::CATEGORY_ID;
		}

		if ($this->aCategory !== null && $this->aCategory->getId() !== $v) {
			$this->aCategory = null;
		}

		return $this;
	} 
	
	public function setCreatedAt($v)
	{
						if ($v === null || $v === '') {
			$dt = null;
		} elseif ($v instanceof DateTime) {
			$dt = $v;
		} else {
									try {
				if (is_numeric($v)) { 					$dt = new DateTime('@'.$v, new DateTimeZone('UTC'));
															$dt->setTimeZone(new DateTimeZone(date_default_timezone_get()));
				} else {
					$dt = new DateTime($v);
				}
			} catch (Exception $x) {
				throw new PropelException('Error parsing date/time value: ' . var_export($v, true), $x);
			}
		}

		if ( $this->created_at !== null || $dt !== null ) {
			
			$currNorm = ($this->created_at !== null && $tmpDt = new DateTime($this->created_at)) ? $tmpDt->format('Y-m-d\\TH:i:sO') : null;
			$newNorm = ($dt !== null) ? $dt->format('Y-m-d\\TH:i:sO') : null;

			if ( ($currNorm !== $newNorm) 					)
			{
				$this->created_at = ($dt ? $dt->format('Y-m-d\\TH:i:sO') : null);
				$this->modifiedColumns[] = ArticlePeer::CREATED_AT;
			}
		} 
		return $this;
	} 
	
	public function setEndDate($v)
	{
						if ($v === null || $v === '') {
			$dt = null;
		} elseif ($v instanceof DateTime) {
			$dt = $v;
		} else {
									try {
				if (is_numeric($v)) { 					$dt = new DateTime('@'.$v, new DateTimeZone('UTC'));
															$dt->setTimeZone(new DateTimeZone(date_default_timezone_get()));
				} else {
					$dt = new DateTime($v);
				}
			} catch (Exception $x) {
				throw new PropelException('Error parsing date/time value: ' . var_export($v, true), $x);
			}
		}

		if ( $this->end_date !== null || $dt !== null ) {
			
			$currNorm = ($this->end_date !== null && $tmpDt = new DateTime($this->end_date)) ? $tmpDt->format('Y-m-d\\TH:i:sO') : null;
			$newNorm = ($dt !== null) ? $dt->format('Y-m-d\\TH:i:sO') : null;

			if ( ($currNorm !== $newNorm) 					)
			{
				$this->end_date = ($dt ? $dt->format('Y-m-d\\TH:i:sO') : null);
				$this->modifiedColumns[] = ArticlePeer::END_DATE;
			}
		} 
		return $this;
	} 
	
	public function setBookId($v)
	{
		if ($v !== null) {
			$v = (int) $v;
		}

		if ($this->book_id !== $v) {
			$this->book_id = $v;
			$this->modifiedColumns[] = ArticlePeer::BOOK_ID;
		}

		if ($this->aBook !== null && $this->aBook->getId() !== $v) {
			$this->aBook = null;
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
			$this->title = ($row[$startcol + 1] !== null) ? (string) $row[$startcol + 1] : null;
			$this->body = ($row[$startcol + 2] !== null) ? (string) $row[$startcol + 2] : null;
			$this->online = ($row[$startcol + 3] !== null) ? (boolean) $row[$startcol + 3] : null;
			$this->excerpt = ($row[$startcol + 4] !== null) ? (string) $row[$startcol + 4] : null;
			$this->category_id = ($row[$startcol + 5] !== null) ? (int) $row[$startcol + 5] : null;
			$this->created_at = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
			$this->end_date = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
			$this->book_id = ($row[$startcol + 8] !== null) ? (int) $row[$startcol + 8] : null;
			$this->resetModified();

			$this->setNew(false);

			if ($rehydrate) {
				$this->ensureConsistency();
			}

						return $startcol + 9; 
		} catch (Exception $e) {
			throw new PropelException("Error populating Article object", $e);
		}
	}

	
	public function ensureConsistency()
	{

		if ($this->aCategory !== null && $this->category_id !== $this->aCategory->getId()) {
			$this->aCategory = null;
		}
		if ($this->aBook !== null && $this->book_id !== $this->aBook->getId()) {
			$this->aBook = null;
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
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

				
		$stmt = ArticlePeer::doSelectStmt($this->buildPkeyCriteria(), $con);
		$row = $stmt->fetch(PDO::FETCH_NUM);
		$stmt->closeCursor();
		if (!$row) {
			throw new PropelException('Cannot find matching row in the database to reload object values.');
		}
		$this->hydrate($row, 0, true); 
		if ($deep) {  
			$this->aCategory = null;
			$this->aBook = null;
			$this->collAuthorArticles = null;
			$this->lastAuthorArticleCriteria = null;

			$this->collAttachments = null;
			$this->lastAttachmentCriteria = null;

		} 	}

	
	public function delete(PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseArticle:delete:pre') as $callable)
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
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		$con->beginTransaction();
		try {
			ArticlePeer::doDelete($this, $con);
			$this->setDeleted(true);
			$con->commit();
		} catch (PropelException $e) {
			$con->rollBack();
			throw $e;
		}
	

    foreach (sfMixer::getCallables('BaseArticle:delete:post') as $callable)
    {
      call_user_func($callable, $this, $con);
    }

  }
	
	public function save(PropelPDO $con = null)
	{

    foreach (sfMixer::getCallables('BaseArticle:save:pre') as $callable)
    {
      $affectedRows = call_user_func($callable, $this, $con);
      if (is_int($affectedRows))
      {
        return $affectedRows;
      }
    }


    if ($this->isNew() && !$this->isColumnModified(ArticlePeer::CREATED_AT))
    {
      $this->setCreatedAt(time());
    }

		if ($this->isDeleted()) {
			throw new PropelException("You cannot save an object that has been deleted.");
		}

		if ($con === null) {
			$con = Propel::getConnection(ArticlePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}
		
		$con->beginTransaction();
		try {
			$affectedRows = $this->doSave($con);
			$con->commit();
    foreach (sfMixer::getCallables('BaseArticle:save:post') as $callable)
    {
      call_user_func($callable, $this, $con, $affectedRows);
    }

			ArticlePeer::addInstanceToPool($this);
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

												
			if ($this->aCategory !== null) {
				if ($this->aCategory->isModified() || $this->aCategory->isNew()) {
					$affectedRows += $this->aCategory->save($con);
				}
				$this->setCategory($this->aCategory);
			}

			if ($this->aBook !== null) {
				if ($this->aBook->isModified() || $this->aBook->isNew()) {
					$affectedRows += $this->aBook->save($con);
				}
				$this->setBook($this->aBook);
			}

			if ($this->isNew() ) {
				$this->modifiedColumns[] = ArticlePeer::ID;
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
				foreach ($this->collAuthorArticles as $referrerFK) {
					if (!$referrerFK->isDeleted()) {
						$affectedRows += $referrerFK->save($con);
					}
				}
			}

			if ($this->collAttachments !== null) {
				foreach ($this->collAttachments as $referrerFK) {
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
					foreach ($this->collAuthorArticles as $referrerFK) {
						if (!$referrerFK->validate($columns)) {
							$failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
						}
					}
				}

				if ($this->collAttachments !== null) {
					foreach ($this->collAttachments as $referrerFK) {
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
				return $this->getTitle();
				break;
			case 2:
				return $this->getBody();
				break;
			case 3:
				return $this->getOnline();
				break;
			case 4:
				return $this->getExcerpt();
				break;
			case 5:
				return $this->getCategoryId();
				break;
			case 6:
				return $this->getCreatedAt();
				break;
			case 7:
				return $this->getEndDate();
				break;
			case 8:
				return $this->getBookId();
				break;
			default:
				return null;
				break;
		} 	}

	
	public function toArray($keyType = BasePeer::TYPE_PHPNAME, $includeLazyLoadColumns = true)
	{
		$keys = ArticlePeer::getFieldNames($keyType);
		$result = array(
			$keys[0] => $this->getId(),
			$keys[1] => $this->getTitle(),
			$keys[2] => $this->getBody(),
			$keys[3] => $this->getOnline(),
			$keys[4] => $this->getExcerpt(),
			$keys[5] => $this->getCategoryId(),
			$keys[6] => $this->getCreatedAt(),
			$keys[7] => $this->getEndDate(),
			$keys[8] => $this->getBookId(),
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
				$this->setExcerpt($value);
				break;
			case 5:
				$this->setCategoryId($value);
				break;
			case 6:
				$this->setCreatedAt($value);
				break;
			case 7:
				$this->setEndDate($value);
				break;
			case 8:
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
		if (array_key_exists($keys[4], $arr)) $this->setExcerpt($arr[$keys[4]]);
		if (array_key_exists($keys[5], $arr)) $this->setCategoryId($arr[$keys[5]]);
		if (array_key_exists($keys[6], $arr)) $this->setCreatedAt($arr[$keys[6]]);
		if (array_key_exists($keys[7], $arr)) $this->setEndDate($arr[$keys[7]]);
		if (array_key_exists($keys[8], $arr)) $this->setBookId($arr[$keys[8]]);
	}

	
	public function buildCriteria()
	{
		$criteria = new Criteria(ArticlePeer::DATABASE_NAME);

		if ($this->isColumnModified(ArticlePeer::ID)) $criteria->add(ArticlePeer::ID, $this->id);
		if ($this->isColumnModified(ArticlePeer::TITLE)) $criteria->add(ArticlePeer::TITLE, $this->title);
		if ($this->isColumnModified(ArticlePeer::BODY)) $criteria->add(ArticlePeer::BODY, $this->body);
		if ($this->isColumnModified(ArticlePeer::ONLINE)) $criteria->add(ArticlePeer::ONLINE, $this->online);
		if ($this->isColumnModified(ArticlePeer::EXCERPT)) $criteria->add(ArticlePeer::EXCERPT, $this->excerpt);
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

		$copyObj->setExcerpt($this->excerpt);

		$copyObj->setCategoryId($this->category_id);

		$copyObj->setCreatedAt($this->created_at);

		$copyObj->setEndDate($this->end_date);

		$copyObj->setBookId($this->book_id);


		if ($deepCopy) {
									$copyObj->setNew(false);

			foreach ($this->getAuthorArticles() as $relObj) {
				if ($relObj !== $this) {  					$copyObj->addAuthorArticle($relObj->copy($deepCopy));
				}
			}

			foreach ($this->getAttachments() as $relObj) {
				if ($relObj !== $this) {  					$copyObj->addAttachment($relObj->copy($deepCopy));
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
			self::$peer = new ArticlePeer();
		}
		return self::$peer;
	}

	
	public function setCategory(Category $v = null)
	{
		if ($v === null) {
			$this->setCategoryId(NULL);
		} else {
			$this->setCategoryId($v->getId());
		}

		$this->aCategory = $v;

						if ($v !== null) {
			$v->addArticle($this);
		}

		return $this;
	}


	
	public function getCategory(PropelPDO $con = null)
	{
		if ($this->aCategory === null && ($this->category_id !== null)) {
			$c = new Criteria(CategoryPeer::DATABASE_NAME);
			$c->add(CategoryPeer::ID, $this->category_id);
			$this->aCategory = CategoryPeer::doSelectOne($c, $con);
			
		}
		return $this->aCategory;
	}

	
	public function setBook(Book $v = null)
	{
		if ($v === null) {
			$this->setBookId(NULL);
		} else {
			$this->setBookId($v->getId());
		}

		$this->aBook = $v;

						if ($v !== null) {
			$v->addArticle($this);
		}

		return $this;
	}


	
	public function getBook(PropelPDO $con = null)
	{
		if ($this->aBook === null && ($this->book_id !== null)) {
			$c = new Criteria(BookPeer::DATABASE_NAME);
			$c->add(BookPeer::ID, $this->book_id);
			$this->aBook = BookPeer::doSelectOne($c, $con);
			
		}
		return $this->aBook;
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
			$criteria = new Criteria(ArticlePeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collAuthorArticles === null) {
			if ($this->isNew()) {
			   $this->collAuthorArticles = array();
			} else {

				$criteria->add(AuthorArticlePeer::ARTICLE_ID, $this->id);

				AuthorArticlePeer::addSelectColumns($criteria);
				$this->collAuthorArticles = AuthorArticlePeer::doSelect($criteria, $con);
			}
		} else {
						if (!$this->isNew()) {
												

				$criteria->add(AuthorArticlePeer::ARTICLE_ID, $this->id);

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
			$criteria = new Criteria(ArticlePeer::DATABASE_NAME);
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

				$criteria->add(AuthorArticlePeer::ARTICLE_ID, $this->id);

				$count = AuthorArticlePeer::doCount($criteria, $con);
			}
		} else {
						if (!$this->isNew()) {
												

				$criteria->add(AuthorArticlePeer::ARTICLE_ID, $this->id);

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
			$l->setArticle($this);
		}
	}


	
	public function getAuthorArticlesJoinAuthor($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
	{
		if ($criteria === null) {
			$criteria = new Criteria(ArticlePeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collAuthorArticles === null) {
			if ($this->isNew()) {
				$this->collAuthorArticles = array();
			} else {

				$criteria->add(AuthorArticlePeer::ARTICLE_ID, $this->id);

				$this->collAuthorArticles = AuthorArticlePeer::doSelectJoinAuthor($criteria, $con, $join_behavior);
			}
		} else {
									
			$criteria->add(AuthorArticlePeer::ARTICLE_ID, $this->id);

			if (!isset($this->lastAuthorArticleCriteria) || !$this->lastAuthorArticleCriteria->equals($criteria)) {
				$this->collAuthorArticles = AuthorArticlePeer::doSelectJoinAuthor($criteria, $con, $join_behavior);
			}
		}
		$this->lastAuthorArticleCriteria = $criteria;

		return $this->collAuthorArticles;
	}

	
	public function clearAttachments()
	{
		$this->collAttachments = null; 	}

	
	public function initAttachments()
	{
		$this->collAttachments = array();
	}

	
	public function getAttachments($criteria = null, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(ArticlePeer::DATABASE_NAME);
		}
		elseif ($criteria instanceof Criteria)
		{
			$criteria = clone $criteria;
		}

		if ($this->collAttachments === null) {
			if ($this->isNew()) {
			   $this->collAttachments = array();
			} else {

				$criteria->add(AttachmentPeer::ARTICLE_ID, $this->id);

				AttachmentPeer::addSelectColumns($criteria);
				$this->collAttachments = AttachmentPeer::doSelect($criteria, $con);
			}
		} else {
						if (!$this->isNew()) {
												

				$criteria->add(AttachmentPeer::ARTICLE_ID, $this->id);

				AttachmentPeer::addSelectColumns($criteria);
				if (!isset($this->lastAttachmentCriteria) || !$this->lastAttachmentCriteria->equals($criteria)) {
					$this->collAttachments = AttachmentPeer::doSelect($criteria, $con);
				}
			}
		}
		$this->lastAttachmentCriteria = $criteria;
		return $this->collAttachments;
	}

	
	public function countAttachments(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if ($criteria === null) {
			$criteria = new Criteria(ArticlePeer::DATABASE_NAME);
		} else {
			$criteria = clone $criteria;
		}

		if ($distinct) {
			$criteria->setDistinct();
		}

		$count = null;

		if ($this->collAttachments === null) {
			if ($this->isNew()) {
				$count = 0;
			} else {

				$criteria->add(AttachmentPeer::ARTICLE_ID, $this->id);

				$count = AttachmentPeer::doCount($criteria, $con);
			}
		} else {
						if (!$this->isNew()) {
												

				$criteria->add(AttachmentPeer::ARTICLE_ID, $this->id);

				if (!isset($this->lastAttachmentCriteria) || !$this->lastAttachmentCriteria->equals($criteria)) {
					$count = AttachmentPeer::doCount($criteria, $con);
				} else {
					$count = count($this->collAttachments);
				}
			} else {
				$count = count($this->collAttachments);
			}
		}
		return $count;
	}

	
	public function addAttachment(Attachment $l)
	{
		if ($this->collAttachments === null) {
			$this->initAttachments();
		}
		if (!in_array($l, $this->collAttachments, true)) { 			array_push($this->collAttachments, $l);
			$l->setArticle($this);
		}
	}

	
	public function clearAllReferences($deep = false)
	{
		if ($deep) {
			if ($this->collAuthorArticles) {
				foreach ((array) $this->collAuthorArticles as $o) {
					$o->clearAllReferences($deep);
				}
			}
			if ($this->collAttachments) {
				foreach ((array) $this->collAttachments as $o) {
					$o->clearAllReferences($deep);
				}
			}
		} 
		$this->collAuthorArticles = null;
		$this->collAttachments = null;
			$this->aCategory = null;
			$this->aBook = null;
	}


  public function __call($method, $arguments)
  {
    if (!$callable = sfMixer::getCallable('BaseArticle:'.$method))
    {
      throw new sfException(sprintf('Call to undefined method BaseArticle::%s', $method));
    }

    array_unshift($arguments, $this);

    return call_user_func_array($callable, $arguments);
  }


} 