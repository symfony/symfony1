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
require_once($_test_dir.'/unit/sfWebRequestMock.class.php');
require_once($_test_dir.'/../lib/config/sfConfig.class.php');
require_once($_test_dir.'/../lib/config/sfLoader.class.php');

sfLoader::loadHelpers(array('Helper', 'Tag', 'Url', 'Asset'));

$t = new lime_test(11, new lime_output_color());

// image_tag()
$t->is(image_tag(''), '', 'image_tag() returns nothing when called without arguments');
$t->is(image_tag('test'), '<img src="/images/test.png" alt="Test" />', 'image_tag() takes an image name as its first argument');
$t->is(image_tag('test.png'), '<img src="/images/test.png" alt="Test" />', 'image_tag() can take an image name with an extension');
$t->is(image_tag('/images/test.png'), '<img src="/images/test.png" alt="Test" />', 'image_tag() can take an absolute image path');
$t->is(image_tag('/images/test'), '<img src="/images/test.png" alt="Test" />', 'image_tag() can take an absolute image path without extension');
$t->is(image_tag('test.jpg'), '<img src="/images/test.jpg" alt="Test" />', 'image_tag() can take an image name with an extension');
$t->is(image_tag('test', array('alt' => 'Foo')), '<img alt="Foo" src="/images/test.png" />', 'image_tag() takes an array of options as its second argument to override alt');
$t->is(image_tag('test', array('size' => '10x10')), '<img src="/images/test.png" alt="Test" height="10" width="10" />', 'image_tag() takes a size option');
$t->is(image_tag('test', array('class' => 'bar')), '<img class="bar" src="/images/test.png" alt="Test" />', 'image_tag() takes whatever option you want');
/*
// stylesheet_tag()
$t->is(stylesheet_tag('style'), '<link rel="stylesheet" type="text/css" media="screen" href="/css/style.css" />'."\n", 'stylesheet_tag() takes a stylesheet name as its first argument');

$t->is(stylesheet_tag('random.styles', '/css/stylish'),
  '<link rel="stylesheet" type="text/css" media="screen" href="/css/random.styles" />'."\n".
  '<link rel="stylesheet" type="text/css" media="screen" href="/css/stylish.css" />'."\n", 'stylesheet_tag() can takes n stylesheet names as its arguments');

// javascript_include_tag()
$t->is(javascript_include_tag('xmlhr'),
  '<script type="text/javascript" src="/js/xmlhr.js"></script>'."\n");

$t->is(javascript_include_tag('common.javascript', '/elsewhere/cools'),
  '<script type="text/javascript" src="/js/common.javascript"></script>'."\n".
  '<script type="text/javascript" src="/elsewhere/cools.js"></script>'."\n");

// asset_javascript_path()
$t->is(javascript_path('xmlhr'),
  '/js/xmlhr.js');

// asset_style_path()
$t->is(stylesheet_path('style'),
  '/css/style.css');

// asset_style_link()
$t->is(stylesheet_tag('style'),
  "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"/css/style.css\" />\n");

$t->is(stylesheet_tag('random.styles', '/css/stylish'),
  "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"/css/random.styles\" />\n".
  "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"/css/stylish.css\" />\n");

// asset_image_path()
$t->is(image_path('xml'), '/images/xml.png');

// asset_image_tag()
$t->is(image_tag('xml'),
  '<img src="/images/xml.png" alt="Xml" />');

$t->is(image_tag('rss', array('alt' => 'rss syndication')),
  '<img alt="rss syndication" src="/images/rss.png" />');

$t->is(image_tag('gold', array('size' => '45x70')),
  '<img src="/images/gold.png" alt="Gold" height="70" width="45" />');

// auto_discovery_link_tag()
$t->is(auto_discovery_link_tag(),
  '<link href="http://www.example.com" rel="alternate" title="RSS" type="application/rss+xml" />');

$t->is(auto_discovery_link_tag('atom'),
  '<link href="http://www.example.com" rel="alternate" title="ATOM" type="application/atom+xml" />');

$t->is(auto_discovery_link_tag('rss', array('action' => 'feed')),
  '<link href="http://www.example.com" rel="alternate" title="RSS" type="application/rss+xml" />');

$request = new sfWebRequest();
sfConfig::set('test_sfWebRequest_relative_url_root', '/mypath');
$context = new sfContext();

// auto_discovery()
$t->is(auto_discovery_link_tag('rss', array('action' => 'feed')),
  '<link href="http://www.example.com/mypath" rel="alternate" title="RSS" type="application/rss+xml" />');

$t->is(auto_discovery_link_tag('atom'),
  '<link href="http://www.example.com/mypath" rel="alternate" title="ATOM" type="application/atom+xml" />');

$t->is(auto_discovery_link_tag(),
  '<link href="http://www.example.com/mypath" rel="alternate" title="RSS" type="application/rss+xml" />');

// javascript_path()
$t->is(javascript_path('xmlhr'),
  '/mypath/js/xmlhr.js');

// javascript_include()
$t->is(javascript_include_tag('xmlhr'),
  '<script type="text/javascript" src="/mypath/js/xmlhr.js"></script>'."\n");

$t->is(javascript_include_tag('common.javascript', '/elsewhere/cools'),
  '<script type="text/javascript" src="/mypath/js/common.javascript"></script>'."\n".
  '<script type="text/javascript" src="/mypath/elsewhere/cools.js"></script>'."\n");

// style_path()
$t->is(stylesheet_path('style'),
  '/mypath/css/style.css');

// style_link()
$t->is(stylesheet_tag('style'),
  '<link rel="stylesheet" type="text/css" media="screen" href="/mypath/css/style.css" />'."\n");

$t->is(stylesheet_tag('random.styles', '/css/stylish'),
  '<link rel="stylesheet" type="text/css" media="screen" href="/mypath/css/random.styles" />'."\n".
  '<link rel="stylesheet" type="text/css" media="screen" href="/mypath/css/stylish.css" />'."\n");

// image_path()
$t->is(image_path('xml'),
  '/mypath/images/xml.png');

// image_tag()
$t->is(image_tag('xml'),
  '<img src="/mypath/images/xml.png" alt="Xml" />');

$t->is(image_tag('rss', array('alt' => 'rss syndication')),
  '<img alt="rss syndication" src="/mypath/images/rss.png" />');

$t->is(image_tag('gold', array('size' => '45x70')),
  '<img src="/mypath/images/gold.png" alt="Gold" height="70" width="45" />');

$t->is(image_tag('http://www.example.com/images/icon.gif'),
  '<img src="http://www.example.com/images/icon.gif" alt="Icon" />');

// stylesheet_with_asset_host_already_encoded()
$t->is(stylesheet_tag("http://bar.example.com/css/style.css"),
  '<link rel="stylesheet" type="text/css" media="screen" href="http://bar.example.com/css/style.css" />'."\n");
*/