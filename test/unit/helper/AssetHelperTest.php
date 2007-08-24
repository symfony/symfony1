<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

sfLoader::loadHelpers(array('Helper', 'Tag', 'Url', 'Asset'));

$t = new lime_test(45, new lime_output_color());

class myRequest
{
  public $relativeUrlRoot = '';

  public function getRelativeUrlRoot()
  {
    return $this->relativeUrlRoot;
  }

  public function isSecure()
  {
    return false;
  }

  public function getHost()
  {
    return 'localhost';
  }
}

$context = sfContext::getInstance(array('request' => 'myRequest', 'response' => 'sfWebResponse'));

// _compute_public_path()
$t->diag('_compute_public_path');
$t->is(_compute_public_path('foo', 'css', 'css'), '/css/foo.css', '_compute_public_path() converts a string to a web path');
$t->is(_compute_public_path('foo', 'css', 'css', true), 'http://localhost/css/foo.css', '_compute_public_path() can create absolute links');
$t->is(_compute_public_path('foo.css2', 'css', 'css'), '/css/foo.css2', '_compute_public_path() does not add suffix if one already exists');
$context->request->relativeUrlRoot = '/bar';
$t->is(_compute_public_path('foo', 'css', 'css'), '/bar/css/foo.css', '_compute_public_path() takes into account the relative url root configuration');
$context->request->relativeUrlRoot = '';
$t->is(_compute_public_path('foo.css?foo=bar', 'css', 'css'), '/css/foo.css?foo=bar', '_compute_public_path() takes into account query strings');
$t->is(_compute_public_path('foo?foo=bar', 'css', 'css'), '/css/foo.css?foo=bar', '_compute_public_path() takes into account query strings');

// image_tag()
$t->diag('image_tag()');
$t->is(image_tag(''), '', 'image_tag() returns nothing when called without arguments');
$t->is(image_tag('test'), '<img src="/images/test.png" alt="Test" />', 'image_tag() takes an image name as its first argument');
$t->is(image_tag('test.png'), '<img src="/images/test.png" alt="Test" />', 'image_tag() can take an image name with an extension');
$t->is(image_tag('/images/test.png'), '<img src="/images/test.png" alt="Test" />', 'image_tag() can take an absolute image path');
$t->is(image_tag('/images/test'), '<img src="/images/test.png" alt="Test" />', 'image_tag() can take an absolute image path without extension');
$t->is(image_tag('test.jpg'), '<img src="/images/test.jpg" alt="Test" />', 'image_tag() can take an image name with an extension');
$t->is(image_tag('test', array('alt' => 'Foo')), '<img alt="Foo" src="/images/test.png" />', 'image_tag() takes an array of options as its second argument to override alt');
$t->is(image_tag('test', array('size' => '10x10')), '<img src="/images/test.png" alt="Test" height="10" width="10" />', 'image_tag() takes a size option');
$t->is(image_tag('test', array('absolute' => true)), '<img src="http://localhost/images/test.png" alt="Test" />', 'image_tag() can take an absolute parameter');
$t->is(image_tag('test', array('class' => 'bar')), '<img class="bar" src="/images/test.png" alt="Test" />', 'image_tag() takes whatever option you want');

// stylesheet_tag()
$t->diag('stylesheet_tag()');
$t->is(stylesheet_tag('style'), 
  '<link rel="stylesheet" type="text/css" media="screen" href="/css/style.css" />'."\n", 
  'stylesheet_tag() takes a stylesheet name as its first argument');
$t->is(stylesheet_tag('random.styles', '/css/stylish'),
  '<link rel="stylesheet" type="text/css" media="screen" href="/css/random.styles" />'."\n".
  '<link rel="stylesheet" type="text/css" media="screen" href="/css/stylish.css" />'."\n", 
  'stylesheet_tag() can takes n stylesheet names as its arguments');
$t->is(stylesheet_tag('style', array('media' => 'all')), 
  '<link rel="stylesheet" type="text/css" media="all" href="/css/style.css" />'."\n", 
  'stylesheet_tag() can take a media option');
$t->is(stylesheet_tag('style', array('absolute' => true)), 
  '<link rel="stylesheet" type="text/css" media="screen" href="http://localhost/css/style.css" />'."\n", 
  'stylesheet_tag() can take an absolute option to output an absolute file name');
$t->is(stylesheet_tag('style', array('raw_name' => true)), 
  '<link rel="stylesheet" type="text/css" media="screen" href="style" />'."\n", 
  'stylesheet_tag() can take a raw_name option to bypass file name decoration');

// javascript_include_tag()
$t->diag('javascript_include_tag()');
$t->is(javascript_include_tag('xmlhr'),
  '<script type="text/javascript" src="/js/xmlhr.js"></script>'."\n", 
  'javascript_include_tag() takes a javascript name as its first argument');
$t->is(javascript_include_tag('common.javascript', '/elsewhere/cools'),
  '<script type="text/javascript" src="/js/common.javascript"></script>'."\n".
  '<script type="text/javascript" src="/elsewhere/cools.js"></script>'."\n",
  'javascript_include_tag() can takes n javascript file names as its arguments');
$t->is(javascript_include_tag('xmlhr', array('absolute' => true)),
  '<script type="text/javascript" src="http://localhost/js/xmlhr.js"></script>'."\n", 
  'javascript_include_tag() can take an absolute option to output an absolute file name');
$t->is(javascript_include_tag('xmlhr', array('raw_name' => true)),
  '<script type="text/javascript" src="xmlhr"></script>'."\n", 
  'javascript_include_tag() can take a raw_name option to bypass file name decoration');

// javascript_path()
$t->diag('javascript_path()');
$t->is(javascript_path('xmlhr'), '/js/xmlhr.js', 'javascript_path() decorates a relative filename with js dir name and extension');
$t->is(javascript_path('/xmlhr'), '/xmlhr.js', 'javascript_path() does not decorate absolute file names with js dir name');
$t->is(javascript_path('xmlhr.foo'), '/js/xmlhr.foo', 'javascript_path() does not decorate file names with extension with .js');
$t->is(javascript_path('xmlhr.foo', true), 'http://localhost/js/xmlhr.foo', 'javascript_path() accepts a second parameter to output an absolute resource path');

// stylesheet_path()
$t->diag('stylesheet_path()');
$t->is(stylesheet_path('style'), '/css/style.css', 'stylesheet_path() decorates a relative filename with css dir name and extension');
$t->is(stylesheet_path('/style'), '/style.css', 'stylesheet_path() does not decorate absolute file names with css dir name');
$t->is(stylesheet_path('style.foo'), '/css/style.foo', 'stylesheet_path() does not decorate file names with extension with .css');
$t->is(stylesheet_path('style.foo', true), 'http://localhost/css/style.foo', 'stylesheet_path() accepts a second parameter to output an absolute resource path');

// image_path()
$t->diag('image_path()');
$t->is(image_path('img'), '/images/img.png', 'image_path() decorates a relative filename with images dir name and png extension');
$t->is(image_path('/img'), '/img.png', 'image_path() does not decorate absolute file names with images dir name');
$t->is(image_path('img.jpg'), '/images/img.jpg', 'image_path() does not decorate file names with extension with .png');
$t->is(image_path('img.jpg', true), 'http://localhost/images/img.jpg', 'image_path() accepts a second parameter to output an absolute resource path');

// use_javascript() get_javascripts()
$t->diag('use_javascript() get_javascripts()');
use_javascript('xmlhr');
$t->is(get_javascripts(),
  '<script type="text/javascript" src="/js/xmlhr.js"></script>'."\n", 
  'get_javascripts() returns a javascript previously added by use_javascript()');
use_javascript('xmlhr', '', array('raw_name' => true));
$t->is(get_javascripts(),
  '<script type="text/javascript" src="xmlhr"></script>'."\n", 
  'use_javascript() accepts an array of options as a third parameter');
use_javascript('xmlhr', '', array('absolute' => true));
$t->is(get_javascripts(),
  '<script type="text/javascript" src="http://localhost/js/xmlhr.js"></script>'."\n", 
  'use_javascript() accepts an array of options as a third parameter');
use_javascript('xmlhr');
use_javascript('xmlhr2');
$t->is(get_javascripts(),
  '<script type="text/javascript" src="/js/xmlhr.js"></script>'."\n".'<script type="text/javascript" src="/js/xmlhr2.js"></script>'."\n", 
  'get_javascripts() returns all the javascripts previously added by use_javascript()');

// use_stylesheet() get_stylesheets()
$t->diag('use_stylesheet() get_stylesheets()');
use_stylesheet('style');
$t->is(get_stylesheets(),
  '<link rel="stylesheet" type="text/css" media="screen" href="/css/style.css" />'."\n", 
  'get_stylesheets() returns a stylesheet previously added by use_stylesheet()');
use_stylesheet('style', '', array('raw_name' => true));
$t->is(get_stylesheets(),
  '<link rel="stylesheet" type="text/css" media="screen" href="style" />'."\n", 
  'use_stylesheet() accepts an array of options as a third parameter');
use_stylesheet('style', '', array('absolute' => true));
$t->is(get_stylesheets(),
  '<link rel="stylesheet" type="text/css" media="screen" href="http://localhost/css/style.css" />'."\n", 
  'use_stylesheet() accepts an array of options as a third parameter');
use_stylesheet('style');
use_stylesheet('style2');
$t->is(get_stylesheets(),
  '<link rel="stylesheet" type="text/css" media="screen" href="/css/style.css" />'."\n".'<link rel="stylesheet" type="text/css" media="screen" href="/css/style2.css" />'."\n",
  'get_stylesheets() returns all the stylesheets previously added by use_stylesheet()');
