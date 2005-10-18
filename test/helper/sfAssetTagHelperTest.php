<?php

require_once 'symfony/helper/TagHelper.php';
require_once 'symfony/helper/AssetHelper.php';
require_once 'symfony/core/sfContext.class.php';

Mock::generate('sfContext');

class sfAssetTagHelperTest extends UnitTestCase
{
  private $context;

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
      'return javascript_tag("xmlhr");' => "<script language=\"javascript\" type=\"text/javascript\" src=\"/js/xmlhr.js\"></script>\n",
      'return javascript_tag("common.javascript", "/elsewhere/cools");' => "<script language=\"javascript\" type=\"text/javascript\" src=\"/js/common.javascript\"></script>\n<script language=\"javascript\" type=\"text/javascript\" src=\"/elsewhere/cools.js\"></script>\n"
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

  public function test_javascript_tag()
  {
    $this->assertEqual(javascript_tag('xmlhr'), 
      '<script language="javascript" type="text/javascript" src="/js/xmlhr.js"></script>'."\n");
    $this->assertEqual(javascript_tag('common.javascript', '/elsewhere/cools'), 
      '<script language="javascript" type="text/javascript" src="/js/common.javascript"></script>'."\n".
      '<script language="javascript" type="text/javascript" src="/elsewhere/cools.js"></script>'."\n");
  }

  public function test_asset_javascript_path()
  {
    foreach (sfAssetTagHelperTest::$JavascriptPathToTag as $method => $tag)
      $this->assertEqual($tag, eval($method));
  }

  public function test_asset_javascript_include()
  {
    foreach (sfAssetTagHelperTest::$JavascriptIncludeToTag as $method => $tag)
      $this->assertEqual($tag, eval($method));
  }

  public function test_asset_style_path()
  {
    foreach (sfAssetTagHelperTest::$StylePathToTag as $method => $tag)
      $this->assertEqual($tag, eval($method));
  }

  public function test_asset_style_link()
  {
    foreach (sfAssetTagHelperTest::$StyleLinkToTag as $method => $tag)
      $this->assertEqual($tag, eval($method));
  }

  public function test_asset_image_path()
  {
    foreach (sfAssetTagHelperTest::$ImagePathToTag as $method => $tag)
      $this->assertEqual($tag, eval($method));
  }

  public function test_asset_image_tag()
  {
    foreach (sfAssetTagHelperTest::$ImageLinkToTag as $method => $tag)
      $this->assertEqual($tag, eval($method));
  }

/*
  public function test_asset_auto_discovery()
  {
    foreach (sfAssetTagHelperTest::$AutoDiscoveryToTag as $method => $tag)
      $this->assertEqual($tag, eval($method));
  }
*/
/*
  
end

class AssetTagHelperNonVhostTest < Test::Unit::TestCase
  include ActionView::Helpers::TagHelper
  include ActionView::Helpers::UrlHelper
  include ActionView::Helpers::AssetTagHelper

  def setup
    @controller = Class.new do
    
      def url_for(options, *parameters_for_method_reference)
        "http://www.example.com/calloboration/hieraki"
      end
      
    end.new
    
    @request = Class.new do 
      def relative_url_root
        "/calloboration/hieraki"
      end
    end.new
    
  end

  AutoDiscoveryToTag = {
    %(auto_discovery_link_tag(:rss, :action => "feed")) => %(<link href="http://www.example.com/calloboration/hieraki" rel="alternate" title="RSS" type="application/rss+xml" />),
    %(auto_discovery_link_tag(:atom)) => %(<link href="http://www.example.com/calloboration/hieraki" rel="alternate" title="ATOM" type="application/atom+xml" />),
    %(auto_discovery_link_tag) => %(<link href="http://www.example.com/calloboration/hieraki" rel="alternate" title="RSS" type="application/rss+xml" />),
  }

  JavascriptPathToTag = {
    %(javascript_path("xmlhr")) => %(/calloboration/hieraki/js/xmlhr.js),
  }

  JavascriptIncludeToTag = {
    %(javascript_tag("xmlhr")) => %(<script src="/calloboration/hieraki/js/xmlhr.js" type="text/javascript"></script>),
    %(javascript_tag("common.javascript", "/elsewhere/cools")) => %(<script src="/calloboration/hieraki/js/common.javascript" type="text/javascript"></script>\n<script src="/calloboration/hieraki/elsewhere/cools.js" type="text/javascript"></script>),
  }

  StylePathToTag = {
    %(stylesheet_path("style")) => %(/calloboration/hieraki/css/style.css),
  }

  StyleLinkToTag = {
    %(stylesheet_tag("style")) => %(<link href="/calloboration/hieraki/css/style.css" media="screen" rel="Stylesheet" type="text/css" />),
    %(stylesheet_tag("random.styles", "/css/stylish")) => %(<link href="/calloboration/hieraki/css/random.styles" media="screen" rel="Stylesheet" type="text/css" />\n<link href="/calloboration/hieraki/css/stylish.css" media="screen" rel="Stylesheet" type="text/css" />)
  }

  ImagePathToTag = {
    %(image_path("xml")) => %(/calloboration/hieraki/images/xml.png),
  }
  
  ImageLinkToTag = {
    %(image_tag("xml")) => %(<img alt="Xml" src="/calloboration/hieraki/images/xml.png" />),
    %(image_tag("rss", :alt => "rss syndication")) => %(<img alt="rss syndication" src="/calloboration/hieraki/images/rss.png" />),
    %(image_tag("gold", :size => "45x70")) => %(<img alt="Gold" height="70" src="/calloboration/hieraki/images/gold.png" width="45" />),
  }

  def test_auto_discovery
    AutoDiscoveryToTag.each { |method, tag| assert_equal(tag, eval(method)) }
  end

  def test_javascript_path
    JavascriptPathToTag.each { |method, tag| assert_equal(tag, eval(method)) }
  end

  def test_javascript_include
    JavascriptIncludeToTag.each { |method, tag| assert_equal(tag, eval(method)) }
  end

  def test_style_path
    StylePathToTag.each { |method, tag| assert_equal(tag, eval(method)) }
  end

  def test_style_link
    StyleLinkToTag.each { |method, tag| assert_equal(tag, eval(method)) }
  end

  def test_image_tag
    assert_equal %(<img alt="Gold" height="70" src="/calloboration/hieraki/images/gold.png" width="45" />), image_tag("gold", :size => "45x70")
  end

  def test_image_path
    ImagePathToTag.each { |method, tag| assert_equal(tag, eval(method)) }
  end
  
  def test_image_tag
    ImageLinkToTag.each { |method, tag| assert_equal(tag, eval(method)) }
  end
*/
}

?>
