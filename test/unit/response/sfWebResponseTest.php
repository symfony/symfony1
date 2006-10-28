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

$t = new lime_test(59, new lime_output_color());

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
$t->is($response->getHttpHeader('My-Header'), 'foo', '->getHttpHeader() returns the current http header values');
$t->is($response->getHttpHeader('my-header'), 'foo', '->getHttpHeader() normalizes http header name');

// ->setHttpHeader()
$t->diag('->setHttpHeader()');
$response->setHttpHeader('My-Header', 'foo');
$response->setHttpHeader('My-Header', 'bar', false);
$response->setHttpHeader('my-header', 'foobar', false);
$t->is($response->getHttpHeader('My-Header'), 'foo, bar, foobar', '->setHttpHeader() takes a replace argument as its third argument');
$response->setHttpHeader('My-Other-Header', 'foo', false);
$t->is($response->getHttpHeader('My-Other-Header'), 'foo', '->setHttpHeader() takes a replace argument as its third argument');

$response->setHttpHeader('my-header', 'foo');
$t->is($response->getHttpHeader('My-Header'), 'foo', '->setHttpHeader() normalizes http header name');

// ->clearHttpHeaders()
$t->diag('->clearHttpHeaders()');
$response->setHttpHeader('my-header', 'foo');
$response->clearHttpHeaders();
$t->is($response->getHttpHeader('My-Header'), '', '->clearHttpHeaders() clears all current http headers');

// ->getHttpHeaders()
$t->diag('->getHttpHeaders()');
$response->clearHttpHeaders();
$response->setHttpHeader('my-header', 'foo');
$response->setHttpHeader('my-header', 'bar', false);
$response->setHttpHeader('another', 'foo');
$t->is($response->getHttpHeaders(), array('My-Header' => 'foo, bar', 'Another' => 'foo'), '->getHttpHeaders() return all current response http headers');

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

$t->is($response->getContentType(), $response->getHttpHeader('content-type'), '->getContentType() is an alias for ->getHttpHeader(\'content-type\')');

$response->setContentType('text/xml');
$response->setContentType('text/html');
$t->is(count($response->getHttpHeader('content-type')), 1, '->setContentType() overrides previous content type if replace is true');

// ->getTitle() ->setTitle()
$t->diag('->getTitle() ->setTitle()');
$t->is($response->getTitle(), '', '->getTitle() returns an empty string by default');
$response->setTitle('my title');
$t->is($response->getTitle(), 'my title', '->setTitle() sets the title');

// ->addHttpMeta()
$t->diag('->addHttpMeta()');
$response->clearHttpHeaders();
$response->addHttpMeta('My-Header', 'foo');
$response->addHttpMeta('My-Header', 'bar', false);
$response->addHttpMeta('my-header', 'foobar', false);
$metas = $response->getHttpMetas();
$t->is($metas['My-Header'], 'foo, bar, foobar', '->addHttpMeta() takes a replace argument as its third argument');
$t->is($response->getHttpHeader('My-Header'), 'foo, bar, foobar', '->addHttpMeta() also sets the corresponding http header');
$response->addHttpMeta('My-Other-Header', 'foo', false);
$metas = $response->getHttpMetas();
$t->is($metas['My-Other-Header'], 'foo', '->addHttpMeta() takes a replace argument as its third argument');
$response->addHttpMeta('my-header', 'foo');
$metas = $response->getHttpMetas();
$t->is($metas['My-Header'], 'foo', '->addHttpMeta() normalizes http header name');

// ->addVaryHttpHeader()
$t->diag('->addVaryHttpHeader()');
$response->clearHttpHeaders();
$response->addVaryHttpHeader('Cookie');
$t->is($response->getHttpHeader('Vary'), 'Cookie', '->addVaryHttpHeader() adds a new Vary header');
$response->addVaryHttpHeader('Cookie');
$t->is($response->getHttpHeader('Vary'), 'Cookie', '->addVaryHttpHeader() does not add the same header twice');
$response->addVaryHttpHeader('Accept-Language');
$t->is($response->getHttpHeader('Vary'), 'Cookie, Accept-Language', '->addVaryHttpHeader() respects ordering');

// ->addCacheControlHttpHeader()
$t->diag('->addCacheControlHttpHeader()');
$response->clearHttpHeaders();
$response->addCacheControlHttpHeader('max-age', 0);
$t->is($response->getHttpHeader('Cache-Control'), 'max-age=0', '->addCacheControlHttpHeader() adds a new Cache-Control header');
$response->addCacheControlHttpHeader('max-age', 12);
$t->is($response->getHttpHeader('Cache-Control'), 'max-age=12', '->addCacheControlHttpHeader() does not add the same header twice');
$response->addCacheControlHttpHeader('no-cache');
$t->is($response->getHttpHeader('Cache-Control'), 'max-age=12, no-cache', '->addCacheControlHttpHeader() respects ordering');

// ->mergeProperties()
$t->diag('->mergeProperties()');
$response1 = sfResponse::newInstance('myWebResponse');
$response1->initialize($context);
$response2 = sfResponse::newInstance('myWebResponse');
$response2->initialize($context);

$response1->setHttpHeader('symfony', 'foo');
$response1->setContentType('text/plain');
$response1->setTitle('My title');

$response2->mergeProperties($response1);
$t->is($response1->getHttpHeader('symfony'), $response2->getHttpHeader('symfony'), '->mergerProperties() merges http headers');
$t->is($response1->getContentType(), $response2->getContentType(), '->mergerProperties() merges content type');
$t->is($response1->getTitle(), $response2->getTitle(), '->mergerProperties() merges titles');

// ->addStylesheet()
$t->diag('->addStylesheet()');
$response = sfResponse::newInstance('myWebResponse');
$response->initialize($context);
$response->addStylesheet('test');
$t->ok($response->getParameterHolder()->has('test', 'helper/asset/auto/stylesheet'), '->addStylesheet() adds a new stylesheet for the response');
$response->addStylesheet('foo', '');
$t->ok($response->getParameterHolder()->has('foo', 'helper/asset/auto/stylesheet'), '->addStylesheet() adds a new stylesheet for the response');
$response->addStylesheet('first', 'first');
$t->ok($response->getParameterHolder()->has('first', 'helper/asset/auto/stylesheet/first'), '->addStylesheet() takes a position as its second argument');
$response->addStylesheet('last', 'last');
$t->ok($response->getParameterHolder()->has('last', 'helper/asset/auto/stylesheet/last'), '->addStylesheet() takes a position as its second argument');
$response->addStylesheet('bar', '', array('media' => 'print'));
$t->is($response->getParameterHolder()->get('bar', null, 'helper/asset/auto/stylesheet'), array('media' => 'print'), '->addStylesheet() takes an array of parameters as its third argument');

// ->getStylesheets()
$t->diag('->getStylesheets()');
$t->is($response->getStylesheets(), array('test' => array(), 'foo' => array(), 'bar' => array('media' => 'print')), '->getStylesheets() returns all current registered stylesheets');
$t->is($response->getStylesheets('first'), array('first' => array()), '->getStylesheets() takes a position as its first argument');
$t->is($response->getStylesheets('last'), array('last' => array()), '->getStylesheets() takes a position as its first argument');

// ->addJavascript()
$t->diag('->addJavascript()');
$response = sfResponse::newInstance('myWebResponse');
$response->initialize($context);
$response->addJavascript('test');
$t->ok($response->getParameterHolder()->has('test', 'helper/asset/auto/javascript'), '->addJavascript() adds a new javascript for the response');
$response->addJavascript('foo', '');
$t->ok($response->getParameterHolder()->has('foo', 'helper/asset/auto/javascript'), '->addJavascript() adds a new javascript for the response');
$response->addJavascript('first', 'first');
$t->ok($response->getParameterHolder()->has('first', 'helper/asset/auto/javascript/first'), '->addJavascript() takes a position as its second argument');
$response->addJavascript('last', 'last');
$t->ok($response->getParameterHolder()->has('last', 'helper/asset/auto/javascript/last'), '->addJavascript() takes a position as its second argument');

// ->getJavascripts()
$t->diag('->getJavascripts()');
$t->is($response->getJavascripts(), array('test' => 'test', 'foo' => 'foo'), '->getJavascripts() returns all current registered javascripts');
$t->is($response->getJavascripts('first'), array('first' => 'first'), '->getJavascripts() takes a position as its first argument');
$t->is($response->getJavascripts('last'), array('last' => 'last'), '->getJavascripts() takes a position as its first argument');

// ->setCookie() ->getCookies()
$t->diag('->setCookie() ->getCookies()');
$response->setCookie('foo', 'bar');
$t->is($response->getCookies(), array('foo' => array('name' => 'foo', 'value' => 'bar', 'expire' => null, 'path' => '/', 'domain' => '', 'secure' => false, 'httpOnly' => false)), '->setCookie() adds a cookie for the response');
