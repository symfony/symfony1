<?php

/**
 * Subclass for representing a row from the 'book' table.
 *
 * 
 *
 * @package lib.model
 */ 
class Book extends BaseBook
{
  public function __toString()
  {
    return $this->getName();
  }
}
