<?php

require_once 'symfony/config/sfConfig.class.php';
require_once 'symfony/controller/sfController.class.php';
require_once 'symfony/controller/sfWebController.class.php';
require_once 'symfony/controller/sfFrontWebController.class.php';
require_once 'symfony/exception/sfParseException.class.php';
require_once 'symfony/view/sfView.class.php';

Mock::generate('', 'sfContext');

class sfWebControllerTest extends UnitTestCase
{
  private $context;
  private $controller;

  private static $tests = array(
    'module/action' => array(
      '',
      array(
        'module' => 'module',
        'action' => 'action',
      ),
      ),
    'module/action?id=12' => array(
      '',
      array(
        'module' => 'module',
        'action' => 'action',
        'id'     => 12,
      ),
      ),
    'module/action?id=12&test=4&toto=9' => array(
      '',
      array(
        'module' => 'module',
        'action' => 'action',
        'id'     => 12,
        'test'   => 4,
        'toto'   => 9,
      ),
      ),
    '@test?test=4' => array(
      'test',
      array(
        'test' => 4
      ),
      ),
    '@test' => array(
      'test',
      array(
      ),
      ),
    '@test?id=12&foo=bar' => array(
      'test',
      array(
        'id' => 12,
        'foo' => 'bar',
      ),
      ),
  );

  public function SetUp()
  {
    sfConfig::set('sf_max_forwards', 10);
    $this->context = new MockSfContext($this);
    $this->controller = sfController::newInstance('sfFrontWebController');
    $this->controller->initialize($this->context, null);
  }

  public function test_simple()
  {
    $c = $this->controller;

    foreach (self::$tests as $url => $result)
    {
      $this->assertEqual($result, $c->convertUrlStringToParameters($url));
    }
  }
}

?>