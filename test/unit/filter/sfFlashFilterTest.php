<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(6, new lime_output_color());

class sfContext
{
  public $user = null;

  public function getUser()
  {
    return $this->user;
  }
}

class myUser extends sfUser
{
  public function initialize ($context, $parameters = array())
  {
    $this->context = $context;
    $this->attribute_holder = new sfParameterHolder(self::ATTRIBUTE_NAMESPACE);
  }
}

class myComponent extends sfComponent
{
  public $user = null;

  public function execute()
  {
  }

  public function getUser()
  {
    return $this->user;
  }
}

class firstTestFilter extends sfFilter
{
  public $t = null;
  public $user = null;

  public function execute($filterChain)
  {
    $t  = $this->t;
    $user = $this->user;

    // sfFlashFilter has executed code before its call to filterChain->execute()
    $t->is($user->getAttribute('previous_request', true, 'symfony/flash/remove'), true, '->execute() flags flash variables to be removed after request execution');
    $t->is($user->getAttribute('every_request', true, 'symfony/flash/remove'), true, '->execute() flags flash variables to be removed after request execution');

    $filterChain->execute();

    // action execution
    $component = new myComponent();
    $component->user = $user;
    $component->setFlash('this_request', 'foo', 'symfony/flash');
    $component->setFlash('every_request', 'foo', 'symfony/flash');
  }
}

class lastTestFilter extends sfFilter
{
  public $t = null;
  public $user = null;

  public function execute($filterChain)
  {
    $t  = $this->t;
    $user = $this->user;

    // sfFlashFilter has executed no code

    // register some flash from previous request
    $user->setAttribute('previous_request', 'foo', 'symfony/flash');
    $user->setAttribute('every_request', 'foo', 'symfony/flash');

    $filterChain->execute();

    // sfFlashFilter has executed all its code
    $t->ok(!$user->hasAttribute('previous_request', 'symfony/flash'), '->execute() removes flash variables that have been tagged before');
    $t->ok(!$user->hasAttribute('previous_request', 'symfony/flash/remove'), '->execute() removes flash variables that have been tagged before');
    $t->is($user->getAttribute('this_request', null, 'symfony/flash'), 'foo', '->execute() keeps current request flash variables');
    $t->is($user->getAttribute('every_request', null, 'symfony/flash'), 'foo', '->execute() flash variables that have been overriden in current request');
  }
}

$context = new sfContext();
$user = new myUser();
$user->initialize($context);
$context->user = $user;

$filterChain = new sfFilterChain();

$filter = new lastTestFilter();
$filter->t = $t;
$filter->user = $user;
$filter->initialize($context);
$filterChain->register($filter);

$filter = new sfFlashFilter();
$filter->initialize($context);
$filterChain->register($filter);

$filter = new firstTestFilter();
$filter->t = $t;
$filter->user = $user;
$filter->initialize($context);
$filterChain->register($filter);

$filterChain->execute();
