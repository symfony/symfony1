<?php

require_once 'symfony/config/sfConfig.class.php';
require_once 'symfony/helper/TagHelper.php';
require_once 'symfony/helper/AssetHelper.php';
require_once 'symfony/core/sfContext.class.php';

Mock::generate('sfContext');

class sfAssetTagHelperTest extends UnitTestCase
{
  private
    $context = null,
    $config  = null;

  private static $AutoDiscoveryToTag = array(
    'return auto_discovery_link_tag();' => '<link href="http://www.example.com" rel="alternate" title="RSS" type="application/rss+xml" />',
/*
    %(auto_discovery_link_tag(:atom)) => %(<link href="http://www.example.com" rel="alternate" title="ATOM" type="application/atom+xml" />),
    %(auto_discovery_link_tag(:rss, :action => "feed")) => %(<link href="http://www.example.com" rel="alternate" title="RSS" type="application/rss+xml" />),
*/
  );

  private static $JavascriptPathToTag = array(
    'return javascript_path("xmlhr");' => '/js/xmlhr.js',
  );

  private static $JavascriptIncludeToTag = array(
    'return javascript_include_tag("xmlhr");' => "<script language=\"javascript\" type=\"text/javascript\" src=\"/js/xmlhr.js\"></script>\n",
    'return javascript_include_tag("common.javascript", "/elsewhere/cools");' => "<script language=\"javascript\" type=\"text/javascript\" src=\"/js/common.javascript\"></script>\n<script language=\"javascript\" type=\"text/javascript\" src=\"/elsewhere/cools.js\"></script>\n"
  );

  private static $StylePathToTag = array(
    ' return stylesheet_path("style");' => '/css/style.css',
  );

  private static $StyleLinkToTag = array(
    'return stylesheet_tag("style");' => "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"/css/style.css\" />\n",
    'return stylesheet_tag("random.styles", "/css/stylish");' => "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"/css/random.styles\" />\n<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"/css/stylish.css\" />\n"
  );

  private static $ImagePathToTag = array(
    'return image_path("xml");' => '/images/xml.png',
  );

  private static $ImageLinkToTag = array(
    'return image_tag("xml");' => '<img src="/images/xml.png" alt="Xml" />',
    'return image_tag("rss", array("alt" => "rss syndication"));' => '<img alt="rss syndication" src="/images/rss.png" />',
    'return image_tag("gold", array("size" => "45x70"));' => '<img src="/images/gold.png" alt="Gold" height="70" width="45" />',
  );

  public function SetUp()
  {
    $this->context = new MockSfContext($this);
    sfConfig::set('sf_relative_url_root', '');
  }

  public function test_image_tag()
  {
    $this->assertEqual(image_tag(''), '');
    $this->assertEqual(image_tag('test'), '<img src="/images/test.png" alt="Test" />');
    $this->assertEqual(image_tag('test.png'), '<img src="/images/test.png" alt="Test" />');
    $this->assertEqual(image_tag('/images/test.png'), '<img src="/images/test.png" alt="Test" />');
    $this->assertEqual(image_tag('/images/test'), '<img src="/images/test.png" alt="Test" />');
    $this->assertEqual(image_tag('test.jpg'), '<img src="/images/test.jpg" alt="Test" />');
    $this->assertEqual(image_tag('/anotherpath/path/test.jpg'), '<img src="/anotherpath/path/test.jpg" alt="Test" />');
    $this->assertEqual(image_tag('test', array('alt' => 'Foo')), '<img alt="Foo" src="/images/test.png" />');
    $this->assertEqual(image_tag('test', array('size' => '10x10')), '<img src="/images/test.png" alt="Test" height="10" width="10" />');
    $this->assertEqual(image_tag('test', array('class' => 'bar')), '<img class="bar" src="/images/test.png" alt="Test" />');
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
      '<script language="javascript" type="text/javascript" src="/js/xmlhr.js"></script>'."\n");
    $this->assertEqual(javascript_include_tag('common.javascript', '/elsewhere/cools'), 
      '<script language="javascript" type="text/javascript" src="/js/common.javascript"></script>'."\n".
      '<script language="javascript" type="text/javascript" src="/elsewhere/cools.js"></script>'."\n");
  }

  public function test_asset_javascript_path()
  {
    foreach (sfAssetTagHelperTest::$JavascriptPathToTag as $method => $tag)
    {
      $this->assertEqual($tag, eval($method));
    }
  }

  public function test_asset_javascript_include()
  {
    foreach (sfAssetTagHelperTest::$JavascriptIncludeToTag as $method => $tag)
    {
      $this->assertEqual($tag, eval($method));
    }
  }

  public function test_asset_style_path()
  {
    foreach (sfAssetTagHelperTest::$StylePathToTag as $method => $tag)
    {
      $this->assertEqual($tag, eval($method));
    }
  }

  public function test_asset_style_link()
  {
    foreach (sfAssetTagHelperTest::$StyleLinkToTag as $method => $tag)
    {
      $this->assertEqual($tag, eval($method));
    }
  }

  public function test_asset_image_path()
  {
    foreach (sfAssetTagHelperTest::$ImagePathToTag as $method => $tag)
    {
      $this->assertEqual($tag, eval($method));
    }
  }

  public function test_asset_image_tag()
  {
    foreach (sfAssetTagHelperTest::$ImageLinkToTag as $method => $tag)
    {
      $this->assertEqual($tag, eval($method));
    }
  }
}

?>