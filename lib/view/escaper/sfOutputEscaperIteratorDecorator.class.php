<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Output escaping iterator decorator.
 *
 * This takes an object that implements the Traversable interface and turns it
 * into an iterator with each value escaped.
 *
 * Note: Prior to PHP 5.1, the IteratorIterator class was not implemented in the
 * core of PHP. This means that although it will still work with classes that
 * implement Iterator or IteratorAggregate, internal PHP classes that only
 * implement the Traversable interface will cause the constructor to throw an
 * exception.
 *
 * @see        sfOutputEscaper
 * @package    symfony
 * @subpackage view
 * @author     Mike Squire <mike@somosis.co.uk>
 * @version    SVN: $Id$
 */
class sfOutputEscaperIteratorDecorator extends sfOutputEscaperObjectDecorator implements Iterator
{
  /**
   * The iterator to be used.
   *
   * @var IteratorIterator
   */
  private $iterator;

  /**
   * Construct a new escaping iteratoror using the escaping method and value
   * supplied.
   *
   * @param string $escapingMethod the escaping method to use
   * @param Traversable $value the iterator to escape
   */
  public function __construct($escapingMethod, Traversable $value)
  {
    // Set the original value for __call(). Set our own iterator because passing
    // it to IteratorIterator will lose any other method calls.

    parent::__construct($escapingMethod, $value);

    $this->iterator = new IteratorIterator($value);
  }

  /**
   * Reset the iterator (as required by the Iterator interface).
   */
  public function rewind()
  {
    return $this->iterator->rewind();
  }

  /**
   * Escapes and gets the current element (as required by the Iterator
   * interface).
   *
   * @return mixed the escaped value
   */
  public function current()
  {
    return sfOutputEscaper::escape($this->escapingMethod, $this->iterator->current());
  }

  /**
   * Gets the current key (as required by the Iterator interface).
   */
  public function key()
  {
    return $this->iterator->key();
  }

  /**
   * Moves to the next element in the iterator (as required by the Iterator
   * interface).
   */
  public function next()
  {
    return $this->iterator->next();
  }

  /**
   * Returns whether the current element is valid or not (as required by the
   * Iterator interface).
   *
   * @return boolean true if the current element is valid; false otherwise
   */
  public function valid()
  {
    return $this->iterator->valid();
  }
}
