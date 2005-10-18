<?php

require_once 'symfony/core/sfContext.class.php';
require_once 'symfony/util/sfParameterHolder.class.php';
require_once 'symfony/user/sfUser.class.php';
require_once 'symfony/user/sfSecurityUser.class.php';
require_once 'symfony/storage/sfStorage.class.php';
require_once 'symfony/storage/sfSessionStorage.class.php';

Mock::generate('sfContext');
Mock::generate('sfSessionStorage');

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
