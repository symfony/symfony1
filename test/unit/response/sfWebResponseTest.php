<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../bootstrap.php');
require_once($_test_dir.'/unit/sfContextMock.class.php');

$t = new lime_test(28, new lime_output_color());

class myWebResponse extends sfWebResponse
{
  public function getStatusText()
  {
    return $this->statusText;
  }

  public function normalizeHeaderName($name)
  {
    return parent::normalizeHeaderName($name);
  }
}

$context = new sfContext();
$response = sfResponse::newInstance('myWebResponse');
$response->initialize($context);

// ->getStatusCode() ->setStatusCode()
$t->diag('->getStatusCode() ->setStatusCode()');
$t->is($response->getStatusCode(), 200, '->getStatusCode() returns 200 by default');
$response->setStatusCode(404);
$t->is($response->getStatusCode(), 404, '->setStatusCode() sets status code');
$t->is($response->getStatusText(), 'Not Found', '->setStatusCode() also sets the status text associated with the status code if no message is given');
$response->setStatusCode(404, 'my text');
$t->is($response->getStatusText(), 'my text', '->setStatusCode() takes a message as its second argument as the status text');
$response->setStatusCode(404, '');
$t->is($response->getStatusText(), '', '->setStatusCode() takes a message as its second argument as the status text');

// ->hasHttpHeader()
$t->diag('->hasHttpHeader()');
$t->is($response->hasHttpHeader('non-existant'), false, '->hasHttpHeader() returns false if http header is not set');
$response->setHttpHeader('My-Header', 'foo');
$t->is($response->hasHttpHeader('My-Header'), true, '->hasHttpHeader() returns true if http header is not set');
$t->is($response->hasHttpHeader('my-header'), true, '->hasHttpHeader() normalizes http header name');

// ->getHttpHeader()
$t->diag('->getHttpHeader()');
$response->setHttpHeader('My-Header', 'foo');
$t->is($response->getHttpHeader('My-Header'), array('foo'), '->getHttpHeader() returns an array with current http header values');
$t->is($response->getHttpHeader('my-header'), array('foo'), '->getHttpHeader() normalizes http header name');

// ->setHttpHeader()
$t->diag('->setHttpHeader()');
$response->setHttpHeader('My-Header', 'foo');
$response->setHttpHeader('My-Header', 'bar', false);
$response->setHttpHeader('my-header', 'foobar', false);
$t->is($response->getHttpHeader('My-Header'), array('foo', 'bar', 'foobar'), '->setHttpHeader() takes a replace argument as its third argument');
$response->setHttpHeader('My-Other-Header', 'foo', false);
$t->is($response->getHttpHeader('My-Other-Header'), array('foo'), '->setHttpHeader() takes a replace argument as its third argument');

$response->setHttpHeader('my-header', 'foo');
$t->is($response->getHttpHeader('My-Header'), array('foo'), '->setHttpHeader() normalizes http header name');

// ->normalizeHeaderName()
$t->diag('->normalizeHeaderName()');
foreach (array(
  array('header', 'Header'),
  array('HEADER', 'Header'),
  array('hEaDeR', 'Header'),
  array('my-header', 'My-Header'),
  array('my_header', 'My-Header'),
  array('MY_HEADER', 'My-Header'),
  array('my-header_is_very-long', 'My-Header-Is-Very-Long'),
) as $test)
{
  $t->is($response->normalizeHeaderName($test[0]), $test[1], '->normalizeHeaderName() normalizes http header name');
}

// ->getContentType() ->setContentType()
$t->diag('->getContentType() ->setContentType()');

sfConfig::set('sf_charset', 'UTF-8');

$t->is($response->getContentType(), 'text/html; charset=UTF-8', '->getContentType() returns a sensible default value');

$response->setContentType('text/xml');
$t->is($response->getContentType(), 'text/xml; charset=UTF-8', '->setContentType() adds a charset if none is given');

$response->setContentType('text/xml; charset=ISO-8859-1');
$t->is($response->getContentType(), 'text/xml; charset=ISO-8859-1', '->setContentType() does nothing if a charset is given');

$response->setContentType('text/xml;charset = ISO-8859-1');
$t->is($response->getContentType(), 'text/xml;charset = ISO-8859-1', '->setContentType() does nothing if a charset is given');

$ct =  $response->getHttpHeader('content-type');
$t->is($response->getContentType(), $ct[0], '->getContentType() is an alias for ->getHttpHeader(\'content-type\')');

$response->setContentType('text/xml');
$response->setContentType('text/html');
$t->is(count($response->getHttpHeader('content-type')), 1, '->setContentType() overrides previous content type if replace is true');

// ->getTitle() ->setTitle()
$t->diag('->getTitle() ->setTitle()');
$t->is($response->getTitle(), '', '->getTitle() returns an empty string by default');
$response->setTitle('my title');
$t->is($response->getTitle(), 'my title', '->setTitle() sets the title');
