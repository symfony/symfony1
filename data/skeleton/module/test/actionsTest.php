<?php

class ##MODULE_NAME##ActionsWebBrowserTest extends UnitTestCase
{
  private
    $browser = null;

  public function setUp ()
  {
    // create a new test browser
    $this->browser = new sfTestBrowser();
    $this->browser->initialize('hostname');
  }

  public function tearDown ()
  {
    $this->browser->shutdown();
  }

  public function test_simple()
  {
    $url = '/##MODULE_NAME##/index';
    $html = $this->browser->get($url);
    $this->assertWantedPattern('/##MODULE_NAME##/', $html);
  }
}

?>