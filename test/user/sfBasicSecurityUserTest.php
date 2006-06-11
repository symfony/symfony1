<?php

Mock::generate('sfContext');

class sfBasicSecurityUserTest extends UnitTestCase
{
  private
    $context,
    $storage;

  public function SetUp()
  {
    $this->context = new MockSfContext($this);
    $this->storage = sfStorage::newInstance('sfSessionTestStorage');
    $this->storage->initialize($this->context);

    // mock $this->getContext()->getStorage()
    $this->context->setReturnValue('getStorage', $this->storage);

    $this->user = new sfBasicSecurityUser();
    $this->user->initialize($this->context);
  }

  public function test_credentials()
  {
    $this->assertEqual(false, $this->user->hasCredential('admin'));

    $this->user->addCredential('admin');

    $this->assertEqual(true, $this->user->hasCredential('admin'));

    // admin and user
    $this->assertEqual(false, $this->user->hasCredential(array('admin', 'user')));

    // admin or user
    $this->assertEqual(true, $this->user->hasCredential(array(array('admin', 'user'))));

    $this->user->addCredential('user');
    $this->assertEqual(true, $this->user->hasCredential('admin'));
    $this->assertEqual(true, $this->user->hasCredential('user'));

    $this->user->addCredentials('superadmin', 'subscriber');
    $this->assertEqual(true, $this->user->hasCredential('subscriber'));
    $this->assertEqual(true, $this->user->hasCredential('superadmin'));

    // admin and (user or subscriber)
    $this->assertEqual(true, $this->user->hasCredential(array(
      array('admin', array('user', 'subscriber'))))
    );

    $this->user->addCredentials(array('superadmin1', 'subscriber1'));
    $this->assertEqual(true, $this->user->hasCredential('subscriber1'));
    $this->assertEqual(true, $this->user->hasCredential('superadmin1'));

    // admin and (user or subscriber) and (superadmin1 or subscriber1)
    $this->assertEqual(true, $this->user->hasCredential(array(
      array('admin', array('user', 'subscriber'), array('superadmin1', 'subscriber1'))))
    );

    $this->user->removeCredential('user');
    $this->assertEqual(false, $this->user->hasCredential('user'));

    $this->user->clearCredentials();
    $this->assertEqual(false, $this->user->hasCredential('subscriber'));
    $this->assertEqual(false, $this->user->hasCredential('superadmin'));
  }
}
