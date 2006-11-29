<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

sfLoader::loadHelpers(array('Helper', 'Asset', 'Url', 'Tag'));

class myController
{
  public function genUrl($parameters = array(), $absolute = false)
  {
    return ($absolute ? '/' : '').'module/action';
  }
}

class sfContext
{
  public $controller = null;

  static public $instance = null;

  public static function getInstance()
  {
    if (!isset(self::$instance))
    {
      self::$instance = new sfContext();
    }

    return self::$instance;
  }

  public function getController()
  {
    return $this->controller;
  }
}

$t = new lime_test(15, new lime_output_color());

$context = sfContext::getInstance();
$context->controller = new myController();

// url_for()
$t->diag('url_for()');
$t->is(url_for('test'), 'module/action', 'url_for() converts an internal URI to a web URI');
$t->is(url_for('test', true), '/module/action', 'url_for() can take an absolute boolean as its second argument');
$t->is(url_for('test', false), 'module/action', 'url_for() can take an absolute boolean as its second argument');

// link_to()
$t->diag('link_to()');
$t->is(link_to('test'), '<a href="module/action">test</a>', 'link_to() returns an HTML "a" tag');
$t->is(link_to('test', '', array('absolute' => true)), '<a href="/module/action">test</a>', 'link_to() can take an "absolute" option');
$t->is(link_to('test', '', array('absolute' => false)), '<a href="module/action">test</a>', 'link_to() can take an "absolute" option');
$t->is(link_to('test', '', array('query_string' => 'foo=bar')), '<a href="module/action?foo=bar">test</a>', 'link_to() can take an "query_string" option');
$t->is(link_to(''), '<a href="module/action">module/action</a>', 'link_to() takes the url as the link name if the first argument is empty');
class testObject
{
}
try
{
  $o1 = new testObject();
  link_to($o1);
  $t->fail('link_to() can take an object as its first argument if __toString() method is defined');
}
catch (sfException $e)
{
  $t->pass('link_to() can take an object as its first argument if __toString() method is defined');
}

class testObjectWithToString
{
  public function __toString()
  {
    return 'test';
  }
}
$o2 = new testObjectWithToString();
$t->is(link_to($o2), '<a href="module/action">test</a>', 'link_to() can take an object as its first argument');

// link_to_if()
$t->diag('link_to_if()');
$t->is(link_to_if(true, 'test', ''), '<a href="module/action">test</a>', 'link_to_if() returns an HTML "a" tag if the condition is true');
$t->is(link_to_if(false, 'test', ''), '<span>test</span>', 'link_to_if() returns an HTML "span" tag by default if the condition is false');
$t->is(link_to_if(false, 'test', '', array('tag' => 'div')), '<div>test</div>', 'link_to_if() takes a "tag" option');

// link_to_unless()
$t->diag('link_to_unless()');
$t->is(link_to_unless(false, 'test', ''), '<a href="module/action">test</a>', 'link_to_unless() returns an HTML "a" tag if the condition is false');
$t->is(link_to_unless(true, 'test', ''), '<span>test</span>', 'link_to_unless() returns an HTML "span" tag by default if the condition is true');
