<?php

Mock::generate('sfContext');

class sfUserTest extends UnitTestCase
{
  private $context;
  private $user;

  public function SetUp()
  {
/*
    $this->context = new MockSfContext($this);
    $this->context->storage = new MockSfSessionStorage($this);
    $this->user = new sfUser();
    $this->user->initialize($this->context);

*/
  }

  public function test_credentials()
  {
/*
    $user = $this->user;
    $user->addCredential('admin');
    $this->assertEqual('admin', $user->hasCrendential('admin'));

*/
  }
}

?>
