<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// fix for PHP 5.0 (no Countable interface)
if (!interface_exists('Countable', false))
{
  interface Countable
  {
    public function count();
  }
}

/**
 * Output escaping decorator class for arrays.
 *
 * @see        sfOutputEscaper
 * @package    symfony.view
 * @subpackage escaper
 * @author     Mike Squire <mike@somosis.co.uk>
 * @version    SVN: $Id$
 */
class sfOutputEscaperArrayDecorator extends sfOutputEscaperGetterDecorator implements Iterator, ArrayAccess, Countable
{
  /**
   * Used by the iterator to know if the current element is valid.
   *
   * @var int
   */
  private $count;

  /**
   * Reset the array to the beginning (as required for the Iterator interface).
   */
  public function rewind()
  {
    reset($this->value);

    $this->count = count($this->value);
  }

  /**
   * Get the key associated with the current value (as required by the Iterator
   * interface).
   *
   * @return string the key
   */
  public function key()
  {
    return key($this->value);
  }

  /**
   * Escape and return the current value (as required by the Iterator
   * interface).
   *
   * This escapes the value using {@link sfOutputEscaper::escape()} with
   * whatever escaping method is set for this instance.
   *
   * @return mixed the escaped value
   */
  public function current()
  {
    return sfOutputEscaper::escape($this->escapingMethod, current($this->value));
  }

  /**
   * Move to the next element (as required by the Iterator interface).
   */
  public function next()
  {
    next($this->value);

    $this->count --;
  }

  /**
   * Returns true if the current element is valid (as required by the Iterator
   * interface).
   *
   * The current element will not be valid if {@link next()} has fallen off the
   * end of the array or if there are no elements in the array and {@link
   * rewind()} was called.
   *
   * @return boolean the validity of the current element; true if it is valid
   */
  public function valid()
  {
    return $this->count > 0;
  }

  /**
   * Returns true if the supplied offset is set in the array (as required by
   * the ArrayAccess interface).
   *
   * @param string $offset the offset of the value to check existance of
   * @return boolean true if the offset exists; false otherwise
   */
  public function offsetExists($offset)
  {
    return array_key_exists($offset, $this->value);
  }

  /**
   * Returns the element associated with the offset supplied (as required by
   * the ArrayAccess interface).
   *
   * @param string $offset the offset of the value to get
   * @return mixed the escaped value
   */
  public function offsetGet($offset)
  {
    return sfOutputEscaper::escape($this->escapingMethod, $this->value[$offset]);
  }

  /**
   * Throws an exception saying that values cannot be set (this method is
   * required for the ArrayAccess interface).
   *
   * This (and the other sfOutputEscaper classes) are designed to be read only
   * so this is an illegal operation.
   *
   * @throws sfException
   * @param string $offset (ignored)
   * @param string $value (ignored)
   */
  public function offsetSet($offset, $value)
  {
    throw new sfException('Cannot set values.');
  }

  /**
   * Throws an exception saying that values cannot be unset (this method is
   * required for the ArrayAccess interface).
   *
   * This (and the other sfOutputEscaper classes) are designed to be read only
   * so this is an illegal operation.
   *
   * @param string $offset (ignored)
   * @throws sfException
   */
  public function offsetUnset($offset)
  {
    throw new sfException('Cannot unset values.');
  }

  /**
   * Returns the size of the array (are required by the Countable interface).
   *
   * @return int the size of the array
   */
  public function count()
  {
    return count($this->value);
  }

  /**
   * Returns the (unescaped) value from the array associated with the key
   * supplied.
   *
   * @param string $key the key into the array to use
   * @return mixed the value
   */
  public function getRaw($key)
  {
    return $this->value[$key];
  }
}
