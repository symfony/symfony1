<?php

require_once 'helper/TagHelper.php';
require_once 'helper/AssetHelper.php';
require_once 'symfony/helper/UrlHelper.php';

require_once 'symfony/request/sfRequest.class.php';
require_once 'symfony/request/sfWebRequest.class.php';

Mock::generate('sfContext');
Mock::generate('sfWebRequest');

class sfAssetTagHelperTest extends UnitTestCase
{
  private
    $context = null,
    $request = null;

  public function SetUp()
  {
    $this->request = new MockSfWebRequest($this);
    $this->request->setReturnValue('getRelativeUrlRoot', '');

    $this->context = new MockSfContext($this);
    $this->context->setReturnValue('getRequest', $this->request);
  }

  public function test_image_tag()
  {
    $this->assertEqual(image_tag(''),
      '');
    $this->assertEqual(image_tag('test'),
      '<img src="/images/test.png" alt="Test" />');

    $this->assertEqual(image_tag('test.png'),
      '<img src="/images/test.png" alt="Test" />');

    $this->assertEqual(image_tag('/images/test.png'),
      '<img src="/images/test.png" alt="Test" />');

    $this->assertEqual(image_tag('/images/test'),
      '<img src="/images/test.png" alt="Test" />');

    $this->assertEqual(image_tag('test.jpg'),
      '<img src="/images/test.jpg" alt="Test" />');

    $this->assertEqual(image_tag('/anotherpath/path/test.jpg'),
      '<img src="/anotherpath/path/test.jpg" alt="Test" />');

    $this->assertEqual(image_tag('test', array('alt' => 'Foo')),
      '<img alt="Foo" src="/images/test.png" />');

    $this->assertEqual(image_tag('test', array('size' => '10x10')),
      '<img src="/images/test.png" alt="Test" height="10" width="10" />');

    $this->assertEqual(image_tag('test', array('class' => 'bar')),
      '<img class="bar" src="/images/test.png" alt="Test" />');
  }

  public function test_stylesheet_tag()
  {
    $this->assertEqual(stylesheet_tag('style'),
      '<link rel="stylesheet" type="text/css" media="screen" href="/css/style.css" />'."\n");

    $this->assertEqual(stylesheet_tag('random.styles', '/css/stylish'),
      '<link rel="stylesheet" type="text/css" media="screen" href="/css/random.styles" />'."\n".
      '<link rel="stylesheet" type="text/css" media="screen" href="/css/stylish.css" />'."\n");
  }

  public function test_javascript_include_tag()
  {
    $this->assertEqual(javascript_include_tag('xmlhr'),
      '<script type="text/javascript" src="/js/xmlhr.js"></script>'."\n");

    $this->assertEqual(javascript_include_tag('common.javascript', '/elsewhere/cools'),
      '<script type="text/javascript" src="/js/common.javascript"></script>'."\n".
      '<script type="text/javascript" src="/elsewhere/cools.js"></script>'."\n");
  }

  public function test_asset_javascript_path()
  {
    $this->assertEqual(javascript_path('xmlhr'),
      '/js/xmlhr.js');
  }

  public function test_asset_style_path()
  {
    $this->assertEqual(stylesheet_path('style'),
      '/css/style.css');
  }

  public function test_asset_style_link()
  {
    $this->assertEqual(stylesheet_tag('style'),
      "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"/css/style.css\" />\n");

    $this->assertEqual(stylesheet_tag('random.styles', '/css/stylish'),
      "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"/css/random.styles\" />\n".
      "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"/css/stylish.css\" />\n");
  }

  public function test_asset_image_path()
  {
    $this->assertEqual(image_path('xml'),
      '/images/xml.png');
  }

  public function test_asset_image_tag()
  {
    $this->assertEqual(image_tag('xml'),
      '<img src="/images/xml.png" alt="Xml" />');

    $this->assertEqual(image_tag('rss', array('alt' => 'rss syndication')),
      '<img alt="rss syndication" src="/images/rss.png" />');

    $this->assertEqual(image_tag('gold', array('size' => '45x70')),
      '<img src="/images/gold.png" alt="Gold" height="70" width="45" />');
  }

/*
  public function test_auto_discovery_link_tag()
  {
    $this->assertEqual(auto_discovery_link_tag(),
      '<link href="http://www.example.com" rel="alternate" title="RSS" type="application/rss+xml" />');

    $this->assertEqual(auto_discovery_link_tag('atom'),
      '<link href="http://www.example.com" rel="alternate" title="ATOM" type="application/atom+xml" />');

    $this->assertEqual(auto_discovery_link_tag('rss', array('action' => 'feed')),
      '<link href="http://www.example.com" rel="alternate" title="RSS" type="application/rss+xml" />');
  }
*/
}

class sfAssetTagHelperNonVhostTest extends UnitTestCase
{
  private
    $context = null;

  public function SetUp()
  {
    $this->request = new MockSfRequest($this);
    $this->request->setReturnValue('getRelativeUrlRoot', '/mypath');

    $this->context = new MockSfContext($this);
    $this->context->setReturnValue('getRequest', $this->request);
  }
/*
  public function test_auto_discovery()
  {
    $this->assertEqual(auto_discovery_link_tag('rss', array('action' => 'feed')),
      '<link href="http://www.example.com/mypath" rel="alternate" title="RSS" type="application/rss+xml" />');

    $this->assertEqual(auto_discovery_link_tag('atom'),
      '<link href="http://www.example.com/mypath" rel="alternate" title="ATOM" type="application/atom+xml" />');

    $this->assertEqual(auto_discovery_link_tag(),
      '<link href="http://www.example.com/mypath" rel="alternate" title="RSS" type="application/rss+xml" />');
  }
*/
  public function test_javascript_path()
  {
    $this->assertEqual(javascript_path('xmlhr'),
      '/mypath/js/xmlhr.js');
  }

  public function test_javascript_include()
  {
    $this->assertEqual(javascript_include_tag('xmlhr'),
      '<script type="text/javascript" src="/mypath/js/xmlhr.js"></script>'."\n");

    $this->assertEqual(javascript_include_tag('common.javascript', '/elsewhere/cools'),
      '<script type="text/javascript" src="/mypath/js/common.javascript"></script>'."\n".
      '<script type="text/javascript" src="/mypath/elsewhere/cools.js"></script>'."\n");
  }

  public function test_style_path()
  {
    $this->assertEqual(stylesheet_path('style'),
      '/mypath/css/style.css');
  }

  public function test_style_link()
  {
    $this->assertEqual(stylesheet_tag('style'),
      '<link rel="stylesheet" type="text/css" media="screen" href="/mypath/css/style.css" />'."\n");

    $this->assertEqual(stylesheet_tag('random.styles', '/css/stylish'),
      '<link rel="stylesheet" type="text/css" media="screen" href="/mypath/css/random.styles" />'."\n".
      '<link rel="stylesheet" type="text/css" media="screen" href="/mypath/css/stylish.css" />'."\n");
  }

  public function test_image_path()
  {
    $this->assertEqual(image_path('xml'),
      '/mypath/images/xml.png');
  }

  public function test_image_tag()
  {
    $this->assertEqual(image_tag('xml'),
      '<img src="/mypath/images/xml.png" alt="Xml" />');

    $this->assertEqual(image_tag('rss', array('alt' => 'rss syndication')),
      '<img alt="rss syndication" src="/mypath/images/rss.png" />');

    $this->assertEqual(image_tag('gold', array('size' => '45x70')),
      '<img src="/mypath/images/gold.png" alt="Gold" height="70" width="45" />');

    $this->assertEqual(image_tag('http://www.example.com/images/icon.gif'),
      '<img src="http://www.example.com/images/icon.gif" alt="Icon" />');
  }

  public function test_stylesheet_with_asset_host_already_encoded()
  {
    $this->assertEqual(stylesheet_tag("http://bar.example.com/css/style.css"),
      '<link rel="stylesheet" type="text/css" media="screen" href="http://bar.example.com/css/style.css" />'."\n");
  }

}
?>