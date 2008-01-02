<?php

/**
 * Subclass for representing a row from the 'category' table.
 *
 * 
 *
 * @package lib.model
 */ 
class Category extends BaseCategory
{
  public function __toString()
  {
    return $this->getName();
  }
}
