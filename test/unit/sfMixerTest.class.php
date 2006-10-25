<?php

require_once($_test_dir.'/../lib/util/sfMixer.class.php');

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// helper class to test parameter holder proxies
class sfMixerTest
{
  protected $t = null;

  public function __construct($testObject)
  {
    $this->t = $testObject;
  }

  public function launchTests($object, $class)
  {
    $this->t->diag('Mixins via sfMixer');
    sfMixer::register($class, array('myMixinTest', 'newMethod'));
    $this->t->is($object->newMethod(), 'ok', '__call() accepts mixins via sfMixer');

    try
    {
      $object->nonexistantmethodname();
      $this->t->fail('__call() throws an exception if the method does not exist as a mixin');
    }
    catch (sfException $e)
    {
      $this->t->pass('__call() throws an exception if the method does not exist as a mixin');
    }
  }
}

class myMixinTest
{
  public function newMethod($object)
  {
    return 'ok';
  }
}
