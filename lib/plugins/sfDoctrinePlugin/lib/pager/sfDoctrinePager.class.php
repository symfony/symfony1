<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDoctrine pager class
 *
 * @package    sfDoctrinePlugin
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id$
 */
class sfDoctrinePager extends sfPager implements Serializable
{
  protected $query;

  /**
   * __construct
   *
   * @return void
   */
  public function __construct($class, $defaultMaxPerPage = 10)
  {
    parent::__construct($class, $defaultMaxPerPage);

    $this->setQuery(Doctrine_Query::create()->from($class));
  }

  /**
   * Serialize the pager object
   *
   * @return string $serialized
   */
  public function serialize()
  {
    $vars = get_object_vars($this);
    unset($vars['query']);
    return serialize($vars);
  }

  /**
   * Unserialize a pager object
   *
   * @param string $serialized 
   * @return void
   */
  public function unserialize($serialized)
  {
    $array = unserialize($serialized);

    foreach($array as $name => $values)
    {
      $this->$name = $values;
    }
  }

  /**
   * Initialize the pager instance and prepare it to be used for rendering
   *
   * @return void
   */
  public function init()
  {
    $count = $this->getQuery()->offset(0)->limit(0)->count();

    $this->setNbResults($count);

    $p = $this->getQuery();
    $p->offset(0);
    $p->limit(0);
    if ($this->getPage() == 0 || $this->getMaxPerPage() == 0 || $this->getNbResults() == 0)
    {
      $this->setLastPage(0);
    }
    else
    {
      $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

      $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));

      $p->offset($offset);
      $p->limit($this->getMaxPerPage());
    }
  }

  /**
   * Get the query for the pager
   *
   * @return Doctrine_Query $query
   */
  public function getQuery()
  {
    return $this->query;
  }

  /**
   * Set query object for the pager
   *
   * @param Doctrine_Query $query
   * @return void
   */
  public function setQuery($query)
  {
    $this->query = $query;
  }

  /**
   * Retrieve the object for a certain offset
   *
   * @param integer $offset 
   * @return Doctrine_Record $record
   */
  protected function retrieveObject($offset)
  {
    $cForRetrieve = clone $this->getQuery();
    $cForRetrieve->offset($offset - 1);
    $cForRetrieve->limit(1);

    $results = $cForRetrieve->execute();

    return $results[0];
  }

  /**
   * Get all the results for the pager instance
   *
   * @param integer $fetchtype Doctrine::HYDRATE_* constants
   * @return Doctrine_Collection
   */
  public function getResults($fetchtype = null)
  {
    $p = $this->getQuery();

    if ($fetchtype == 'array')
    {
      return $p->execute(array(), Doctrine::HYDRATE_ARRAY);
    }

    return $p->execute();
  }
}