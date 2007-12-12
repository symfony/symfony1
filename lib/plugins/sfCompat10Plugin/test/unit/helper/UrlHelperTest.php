<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../../../../../test/bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

class myController
{
  public function genUrl($parameters = array(), $absolute = false)
  {
    return ($absolute ? '/' : '').'module/action';
  }
}

$t = new lime_test(24, new lime_output_color());

$context = sfContext::getInstance(array('controller' => 'myController'));

require_once(dirname(__FILE__).'/../../../lib/helper/AssetHelper.php');
require_once(dirname(__FILE__).'/../../../lib/helper/UrlHelper.php');
require_once(dirname(__FILE__).'/../../../lib/helper/TagHelper.php');

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
$t->is(link_to_if(false, 'test', '', array('query_string' => 'foo=bar', 'absolute' => true, 'absolute_url' => 'http://www.google.com/')), '<span>test</span>', 'link_to_if() returns an HTML "span" tag by default if the condition is false');

// link_to_unless()
$t->diag('link_to_unless()');
$t->is(link_to_unless(false, 'test', ''), '<a href="module/action">test</a>', 'link_to_unless() returns an HTML "a" tag if the condition is false');
$t->is(link_to_unless(true, 'test', ''), '<span>test</span>', 'link_to_unless() returns an HTML "span" tag by default if the condition is true');

// mail_to()
$t->diag('mail_to()');
$t->is(mail_to('fabien.potencier@symfony-project.com'), '<a href="mailto:fabien.potencier@symfony-project.com">fabien.potencier@symfony-project.com</a>', 'mail_to() creates a mailto a tag');
$t->is(mail_to('fabien.potencier@symfony-project.com', 'fabien'), '<a href="mailto:fabien.potencier@symfony-project.com">fabien</a>', 'mail_to() creates a mailto a tag');
preg_match('/href="(.+?)"/', mail_to('fabien.potencier@symfony-project.com', 'fabien', array('encode' => true)), $matches);
$t->is(html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8'), 'mailto:fabien.potencier@symfony-project.com', 'mail_to() can encode the email address');

$t->diag('mail_to test');
$t->is(mail_to('webmaster@example.com'),'<a href="mailto:webmaster@example.com">webmaster@example.com</a>','mail_to with only given email works');
$t->is(mail_to('webmaster@example.com', 'send us an email'),'<a href="mailto:webmaster@example.com">send us an email</a>','mail_to with given email and title works');

$t->is(mail_to('webmaster@example.com', '', array(), array('subject' => 'test subject', 'body' => 'test body')),'<a href="mailto:webmaster@example.com?subject=test+subject&amp;body=test+body">webmaster@example.com</a>', 'mail_to() works with given default values in array form');
$t->is(mail_to('webmaster@example.com', '', array(), 'subject=test subject body=test body'),'<a href="mailto:webmaster@example.com?subject=test+subject&amp;body=test+body">webmaster@example.com</a>', 'mail_to() works with given default values in string form');
$t->is(mail_to('webmaster@example.com', '', array(), 'subject=Hello World and more'),'<a href="mailto:webmaster@example.com?subject=Hello+World+and+more">webmaster@example.com</a>', 'mail_to() works with given default value with spaces');
