<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony.runtime.addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfPager.class.php 535 2005-10-18 13:01:23Z root $
 */

/**
 *
 * sfPager class.
 *
 * @package    symfony.runtime.addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfPager.class.php 535 2005-10-18 13:01:23Z root $
 */
class sfPager
{
  private
    $page                   = 1,
    $maxPerPage             = 0,
    $lastPage               = 1,
    $nbResults              = 0,
    $class                  = '',
    $tableName              = '',
    $criteria               = null,
    $objects                = null,
    $cursor                 = 1,
    $sort                   = '',
    $sortType               = '',
    $parameters             = array(),
    $currentMaxLink         = 1,
    $parameter_holder       = null,
    $peer_method_name       = 'doSelect',
    $peer_count_method_name = 'doCount';

  public function __construct($class, $defaultMaxPerPage = 10)
  {
    $this->setClass($class);
    $this->tableName = constant($class.'Peer::TABLE_NAME');
    $this->setCriteria(new Criteria());
    $this->setMaxPerPage($defaultMaxPerPage);
    $this->setPage(1);
    $this->parameter_holder = new sfParameterHolder();
  }

  public function init()
  {
    $cForCount = clone $this->getCriteria();
    $cForCount->setOffset(0);
    $cForCount->setLimit(0);
    $cForCount->clearGroupByColumns();

    // require the model class (because autoloading can crash under some conditions)
    require_once('model/'.$this->getClassPeer().'.php');
    $this->setNbResults(call_user_func(array($this->getClassPeer(), $this->getPeerCountMethod()), $cForCount));

    $c = $this->getCriteria();
    $c->setOffset(0);
    $c->setLimit(0);

    if (($this->getPage() == 0 || $this->getMaxPerPage() == 0))
    {
      $this->setLastPage(0);
    }
    else
    {
      $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));
      $c->setOffset(($this->getPage() - 1) * $this->getMaxPerPage());
      $c->setLimit($this->getMaxPerPage());
    }
  }

  public function getPeerMethod()
  {
    return $this->peer_method_name;
  }

  public function setPeerMethod($peer_method_name)
  {
    $this->peer_method_name = $peer_method_name;
  }

  public function getPeerCountMethod()
  {
    return $this->peer_count_method_name;
  }

  public function setPeerCountMethod($peer_count_method_name)
  {
    $this->peer_count_method_name = $peer_count_method_name;
  }

  public function getCurrentMaxLink()
  {
    return $this->currentMaxLink;
  }

  public function getLinks($nb_links = 5)
  {
    $links = array();
    $begin = ($this->page - floor($nb_links / 2) > 0) ? $this->page - floor($nb_links / 2) : 1;
    $i = $begin;
    while (($i < $begin + $nb_links) && ($i <= $this->lastPage))
    {
      $links[] = $i++;
    }

    $this->currentMaxLink = $links[count($links) - 1];

    return $links;
  }    

  public function haveToPaginate()
  {
    return (($this->getPage() != 0) && ($this->getNbResults() > $this->getMaxPerPage()));
  }

  public function setSort($sort, $type = 'asc')
  {
    $this->sort = $sort;
    if (($type != 'asc') && ($type != 'desc'))
    {
      $type = 'asc';
    }
    $this->sortType = $type;

    $c = $this->getCriteria();
    $sort = strtoupper($sort);
    $c->clearOrderByColumns();

    if ($type == 'asc')
    {
      $c->addDescendingOrderByColumn($this->tableName.'.'.$sort);
    }
    else if ($type == 'desc')
    {
      $c->addAscendingOrderByColumn($this->tableName.'.'.$sort);
    }

    if ($this->getPage() > 0)
    {
      $this->setPage(1);
    }
  }

  public function getSort()
  {
    return $this->sort;
  }

  public function getSortType()
  {
    return $this->sortType;
  }

  public function getCursor()
  {
    return $this->cursor;
  }

  public function setCursor($pos)
  {
    if ($pos < 1)
    {
      $this->cursor = 1;
    }
    else if ($pos > $this->nbResults)
    {
      $this->cursor = $this->nbResults;
    }
    else
    {
      $this->cursor = $pos;
    }
  }

  public function getObjectByCursor($pos)
  {
    $this->setCursor($pos);

    return $this->getCurrent();
  }

  public function getCurrent()
  {
    return $this->retrieveObject($this->cursor);
  }

  public function getNext()
  {
    if (($this->cursor + 1) > $this->nbResults)
    {
      return null;
    }
    else
    {
      return $this->retrieveObject($this->cursor + 1);
    }
  }

  public function getPrevious()
  {
    if (($this->cursor - 1) < 1)
    {
      return null;
    }
    else
    {
      return $this->retrieveObject($this->cursor - 1);
    }
  }

  private function retrieveObject($offset)
  {
    $c = $this->getCriteria();
    $c->setOffset($offset - 1);
    $c->setLimit(1);

    $results = call_user_func(array($this->getClassPeer(), $this->getPeerMethod()), $c);

    return $results[0];
  }

  public function getResults()
  {
    $c = $this->getCriteria();
    return call_user_func(array($this->getClassPeer(), $this->getPeerMethod()), $c);
  }

  public function getFirstIndice()
  {
    if ($this->page == 0)
    {
      return 1;
    }
    else
    {
      return ($this->page - 1) * $this->maxPerPage + 1;
    }
  }

  public function getLastIndice()
  {
    if ($this->page == 0)
    {
      return $this->nbResults;
    }
    else
    {
      if (($this->page * $this->maxPerPage) >= $this->nbResults)
      {
        return $this->nbResults;
      }
      else
      {
        return ($this->page * $this->maxPerPage);
      }
    }
  }

  public function getCriteria()
  {
    return $this->criteria;
  }

  public function setCriteria($c)
  {
    $this->criteria = $c;
  }

  public function getClass()
  {
    return $this->class;
  }

  public function setClass($class)
  {
    $this->class = $class;
  }

  public function getClassPeer()
  {
    return $this->class.'Peer';
  }

  public function getNbResults()
  {
    return $this->nbResults;
  }

  private function setNbResults($nb)
  {
    $this->nbResults = $nb;
  }

  public function getFirstPage()
  {
    return 1;
  }

  public function getLastPage()
  {
    return $this->lastPage;
  }

  private function setLastPage($page)
  {
    $this->lastPage = $page;
    if ($this->getPage() > $page)
    {
      $this->setPage($page);
    }
  }

  public function getPage()
  {
    return $this->page;
  }

  public function getNextPage()
  {
    return min($this->getPage() + 1, $this->getLastPage());
  }

  public function getPreviousPage()
  {
    return max($this->getPage() - 1, $this->getFirstPage());
  }

  public function setPage($page)
  {
    $this->page = ($page < 0) ? 1 : $page;
  }

  public function getMaxPerPage()
  {
    return $this->maxPerPage;
  }

  public function setMaxPerPage($max)
  {
    if ($max > 0)
    {
      $this->maxPerPage = $max;
      if ($this->page == 0)
      {
        $this->page = 1;
      }
    }
    else if ($max == 0)
    {
      $this->maxPerPage = 0;
      $this->page = 0;
    }
    else
    {
      $this->maxPerPage = 1;
      if ($this->page == 0)
      {
        $this->page = 1;
      }
    }
  }

  public function getParameterHolder()
  {
    return $this->parameter_holder;
  }
}

?>