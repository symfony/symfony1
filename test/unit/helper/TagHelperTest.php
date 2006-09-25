<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(dirname(__FILE__).'/../..');
require_once($_test_dir.'/../lib/vendor/lime/lime.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once($_test_dir.'/unit/bootstrap.php');

sfLoader::loadHelpers(array('Helper', 'Tag'));

$t = new lime_test(13, new lime_output_color());

$context = new sfContext();

// tag()
$t->diag('tag()');
$t->is(tag(''), '', 'tag() returns an empty string with empty input');
$t->is(tag('br'), '<br />', 'tag() takes a tag as its first parameter');
$t->is(tag('p', null, true), '<p>', 'tag() takes a boolean parameter as its third parameter');
$t->is(tag('br', array('class' => 'foo'), false), '<br class="foo" />', 'tag() takes an array of options as its second parameters');
$t->is(tag('br', 'class=foo', false), '<br class="foo" />', 'tag() takes a string of options as its second parameters');
$t->is(tag('p', array('class' => 'foo', 'id' => 'bar'), true), '<p class="foo" id="bar">', 'tag() takes a boolean parameter as its third parameter');
//$t->is(tag('br', array('class' => '"foo"')), '<br class="&quot;foo&quot;" />');

// content_tag()
$t->diag('content_tag()');
$t->is(content_tag(''), '', 'content_tag() returns an empty string with empty input');
$t->is(content_tag('', ''), '', 'content_tag() returns an empty string with empty input');
$t->is(content_tag('p', 'Toto'), '<p>Toto</p>', 'content_tag() takes a content as its second parameter');
$t->is(content_tag('p', ''), '<p></p>', 'content_tag() takes a tag as its first parameter');

// cdata_section()
$t->diag('cdata_section()');
$t->is(cdata_section(''), '<![CDATA[]]>');
$t->is(cdata_section('foobar'), '<![CDATA[foobar]]>');

// escape_javascript()
$t->diag('escape_javascript()');
$t->is(escape_javascript("alert('foo');\nalert(\"bar\");"), 'alert(\\\'foo\\\');\\nalert(\\"bar\\");');
