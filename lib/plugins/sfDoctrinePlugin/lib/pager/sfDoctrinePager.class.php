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
 * sfDoctrine pager class.
 *
 * @package    sfDoctrinePlugin
 * @subpackage pager
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id$
 */
class sfDoctrinePager extends sfPager implements Serializable
{
  protected
    $query             = null,
    $tableMethodName   = null,
    $tableMethodCalled = false;

  /**
   * Get the name of the table method used to retrieve the query object for the pager
   *
   * @return string $tableMethodName
   */
  public function getTableMethod()
  {
    return $this->tableMethodName;
  }

  /**
   * Set the name of the table method used to retrieve the query object for the pager
   *
   * @param string $tableMethodName 
   * @return void
   */
  public function setTableMethod($tableMethodName)
  {
    $this->tableMethodName = $tableMethodName;
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
	unset($vars['objects']);
    return serialize($vars);
  }

  /**
   * Unserialize a pager object
   *
   * @param string $serialized 
   */
  public function unserialize($serialized)
  {
    $array = unserialize($serialized);

    foreach ($array as $name => $values)
    {
      $this->$name = $values;
    }
  }

  /**
   * Returns a query for counting the total results.
   * 
   * @return Doctrine_Query
   */
  public function getCountQuery()
  {
    $query = clone $this->getQuery();
    $query
      ->offset(0)
      ->limit(0)
    ;

    return $query;
  }

  /**
   * @see sfPager
   */
  public function init()
  {
    $countQuery = $this->getCountQuery();
    $count = $countQuery->count();

    $this->setNbResults($count);

    $query = $this->getQuery();
    $query
      ->offset(0)
      ->limit(0)
    ;

    if (0 == $this->getPage() || 0 == $this->getMaxPerPage() || 0 == $this->getNbResults())
    {
      $this->setLastPage(0);
    }
    else
    {
      $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

      $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));

      $query
        ->offset($offset)
        ->limit($this->getMaxPerPage())
      ;
    }
  }

  /**
   * Get the query for the pager.
   *
   * @return Doctrine_Query
   */
  public function getQuery()
  {
    if (!$this->tableMethodCalled && $this->tableMethodName)
    {
      $method = $this->tableMethodName;
      $this->query = Doctrine_Core::getTable($this->getClass())->$method($this->query);
      $this->tableMethodCalled = true;
    }
    else if (!$this->query)
    {
      $this->query = Doctrine_Core::getTable($this->getClass())->createQuery();
    }

    return $this->query;
  }

  /**
   * Set query object for the pager
   *
   * @param Doctrine_Query $query
   */
  public function setQuery($query)
  {
    $this->query = $query;
  }

  /**
   * Retrieve the object for a certain offset
   *
   * @param integer $offset
   *
   * @return Doctrine_Record
   */
  protected function retrieveObject($offset)
  {
    // If all results are known we can use the stored objects
    if (null !== $this->objects)
    {
      return $this->objects[$offset-1];
    }

    $queryForRetrieve = clone $this->getQuery();
    $queryForRetrieve
      ->offset($offset - 1)
      ->limit(1)
    ;

    $results = $queryForRetrieve->execute();

    return $results[0];
  }

  /**
   * Get all the results for the pager instance
   *
   * @param mixed $hydrationMode A hydration mode identifier
   *
   * @return Doctrine_Collection|array
   */
  public function getResults($hydrationMode = null)
  {
    if (Doctrine_Core::HYDRATE_ARRAY === $hydrationMode)
    {
      // If we hydrate an array, we can store it fo later reuse
      if (null === $this->objects)
      {
        $this->objects = $this->getQuery()->execute(array(), $hydrationMode);
      }
      return $this->objects;
    }
    return $this->getQuery()->execute(array(), $hydrationMode);
  }

  /**
   * Returns an Iterator for the current pager's results.
   *
   * Depending on the hydration mode of the query object, the return value of
   * {@link getResults()} may be either an object or an array.
   *
   * @see sfPager
   */
  public function getIterator()
  {
    $results = $this->getResults();
    return $results instanceof IteratorAggregate ? $results->getIterator() : new ArrayIterator($results);
  }

  /**
   * @see sfPager
   */
  public function count()
  {
    // If an hydrated array was stored just count it
    if (null === $this->objects)
    {
      return count($this->objects)
    }
    // Otherwise get results. There's no need to hydrate this result set for counting
    return count($this->getResults(Doctrine_Core::HYDRATE_NONE));
  }
}
