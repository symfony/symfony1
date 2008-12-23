<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$app = 'backend';
$fixtures = 'fixtures';
require_once(dirname(__FILE__).'/../bootstrap/functional.php');

$b = new sfTestBrowser();
$b->
  get('/doctrine/route/test1')->
  isStatusCode('200')->
  with('response')->begin()->
    contains('Article')->
  end()->
  get('/doctrine/route/test2')->
  isStatusCode('200')->
  with('response')->begin()->
    contains('Article')->
  end()->
  get('/doctrine/route/test3')->
  isStatusCode('200')->
  with('response')->begin()->
    contains('Doctrine_Collection')->
  end()->
  get('/doctrine/route/test4')->
  isStatusCode('200')->
  with('response')->begin()->
    contains('Doctrine_Collection')->
  end()
;