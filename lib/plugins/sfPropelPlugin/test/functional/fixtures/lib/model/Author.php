<?php

/**
 * Subclass for representing a row from the 'author' table.
 *
 * 
 *
 * @package lib.model
 */ 
class Author extends BaseAuthor
{
  public function __toString()
  {
    return $this->getName();
  }
}
