<?php

class TestObject
{
  protected $value = 'value';
  protected $text  = 'text';

  public function getValue()
  {
    return $this->value;
  }

  public function getText()
  {
    return $this->text;
  }

  public function setText($text)
  {
    $this->text = $text;
  }

  public function setValue($value)
  {
    $this->value = $value;
  }

}

?>