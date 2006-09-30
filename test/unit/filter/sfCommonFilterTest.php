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
require_once($_test_dir.'/unit/bootstrap.php');

$t = new lime_test(12, new lime_output_color());

class sfContext
{
  public $response = null;
  public $request = null;
  static public $instance = null;

  public static function getInstance()
  {
    if (!isset(self::$instance))
    {
      self::$instance = new sfContext();
    }

    return self::$instance;
  }

  public function getRequest()
  {
    return $this->request;
  }

  public function getResponse()
  {
    return $this->response;
  }
}

class myRequest
{
  public function getRelativeUrlRoot()
  {
    return '';
  }
}

class firstTestFilter extends sfFilter
{
  public $t = null;
  public $response = null;

  public function execute($filterChain)
  {
    $t  = $this->t;
    $response = $this->response;

    // sfCommonFilter has executed code before its call to filterChain->execute()

    $filterChain->execute();

    // action execution
    $response->setContent('<html><head></head></html>');
  }
}

class lastTestFilter extends sfFilter
{
  public $t = null;
  public $response = null;

  public function execute($filterChain)
  {
    $t  = $this->t;
    $response = $this->response;

    // sfCommonFilter has executed no code
    $response->addJavascript('last1', 'last');
    $response->addJavascript('first1', 'first');
    $response->addJavascript('middle');
    $response->addJavascript('last2', 'last');
    $response->addJavascript('multiple');
    $response->addJavascript('multiple');
    $response->addJavascript('first2', 'first');
    $response->addJavascript('multiple', 'last');

    $response->addStylesheet('last1', 'last');
    $response->addStylesheet('first1', 'first');
    $response->addStylesheet('middle');
    $response->addStylesheet('last2', 'last');
    $response->addStylesheet('multiple');
    $response->addStylesheet('multiple');
    $response->addStylesheet('first2', 'first');
    $response->addStylesheet('multiple', 'last');

    $filterChain->execute();

    // sfCommmonFilter has executed all its code
    $dom = new DomDocument('1.0', 'UTF-8');
    $dom->validateOnParse = true;
    $dom->loadHTML($response->getContent());
    $selector = new sfDomCssSelector($dom);

    $scripts = $selector->getElements('head script');
    $t->is($scripts[0]->getAttribute('src'), '/js/first1.js', '->execute() add javascripts with position "first" at the beginning');
    $t->is($scripts[1]->getAttribute('src'), '/js/first2.js', '->execute() add javascripts with same position in their registration order');
    $t->is($scripts[2]->getAttribute('src'), '/js/middle.js', '->execute() add javascripts with no position after "first" ones and before "lasts" one');
    $t->is($scripts[3]->getAttribute('src'), '/js/multiple.js', '->execute() add a javascript only once');
    $t->is($scripts[4]->getAttribute('src'), '/js/last1.js', '->execute() add javascripts with position "last" at the end');
    $t->is($scripts[5]->getAttribute('src'), '/js/last2.js', '->execute() add javascripts with same position in their registration order');

    $stylesheets = $selector->getElements('head link');
    $t->is($stylesheets[0]->getAttribute('href'), '/css/first1.css', '->execute() add stylesheets with position "first" at the beginning');
    $t->is($stylesheets[1]->getAttribute('href'), '/css/first2.css', '->execute() add stylesheets with same position in their registration order');
    $t->is($stylesheets[2]->getAttribute('href'), '/css/middle.css', '->execute() add stylesheets with no position after "first" ones and before "lasts" one');
    $t->is($stylesheets[3]->getAttribute('href'), '/css/multiple.css', '->execute() add a stylesheet only once');
    $t->is($stylesheets[4]->getAttribute('href'), '/css/last1.css', '->execute() add stylesheets with position "last" at the end');
    $t->is($stylesheets[5]->getAttribute('href'), '/css/last2.css', '->execute() add stylesheets with same position in their registration order');
  }
}

$context = sfContext::getInstance();
$response = new sfWebResponse();
$response->initialize($context);
$context->response = $response;
$context->request = new myRequest();

$filterChain = new sfFilterChain();

$filter = new lastTestFilter();
$filter->t = $t;
$filter->response = $response;
$filter->initialize($context);
$filterChain->register($filter);

$filter = new sfCommonFilter();
$filter->initialize($context);
$filterChain->register($filter);

$filter = new firstTestFilter();
$filter->t = $t;
$filter->response = $response;
$filter->initialize($context);
$filterChain->register($filter);

$filterChain->execute();
