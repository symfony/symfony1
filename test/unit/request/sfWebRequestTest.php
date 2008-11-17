<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(36, new lime_output_color());

class myRequest extends sfWebRequest
{
  public $languages = null;
  public $charsets = null;
  public $acceptableContentTypes = null;
}

$dispatcher = new sfEventDispatcher();
$request = new myRequest($dispatcher);

// ->getLanguages()
$t->diag('->getLanguages()');

$t->is($request->getLanguages(), array(), '->getLanguages() returns an empty array if the client do not send an ACCEPT_LANGUAGE header');

$request->languages = null;
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
$t->is($request->getLanguages(), array(), '->getLanguages() returns an empty array if the client send an empty ACCEPT_LANGUAGE header');

$request->languages = null;
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-us,en;q=0.5,fr;q=0.3';
$t->is($request->getLanguages(), array('en_US', 'en', 'fr'), '->getLanguages() returns an array with all accepted languages');

// ->getPreferredCulture()
$t->diag('->getPreferredCulture()');
$request->languages = null;
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
$t->is($request->getPreferredCulture(array('fr', 'en')), 'fr', '->getPreferredCulture() returns the first given culture if the client do not send an ACCEPT_LANGUAGE header');

$request->languages = null;
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-us,en;q=0.5,fr;q=0.3';
$t->is($request->getPreferredCulture(array('fr', 'en')), 'en', '->getPreferredCulture() returns the preferred culture');

$request->languages = null;
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-us,en;q=0.5,fr';
$t->is($request->getPreferredCulture(array('fr', 'en')), 'fr', '->getPreferredCulture() returns the preferred culture');

// ->getCharsets()
$t->diag('->getCharsets()');

$t->is($request->getCharsets(), array(), '->getCharsets() returns an empty array if the client do not send an ACCEPT_CHARSET header');

$request->charsets = null;
$_SERVER['HTTP_ACCEPT_CHARSET'] = '';
$t->is($request->getCharsets(), array(), '->getCharsets() returns an empty array if the client send an empty ACCEPT_CHARSET header');

$request->charsets = null;
$_SERVER['HTTP_ACCEPT_CHARSET'] = 'ISO-8859-1,utf-8;q=0.7,*;q=0.3';
$t->is($request->getCharsets(), array('ISO-8859-1', 'utf-8', '*'), '->getCharsets() returns an array with all accepted charsets');

// ->getAcceptableContentTypes()
$t->diag('->getAcceptableContentTypes()');

$t->is($request->getAcceptableContentTypes(), array(), '->getAcceptableContentTypes() returns an empty array if the client do not send an ACCEPT header');

$request->acceptableContentTypes = null;
$_SERVER['HTTP_ACCEPT'] = '';
$t->is($request->getAcceptableContentTypes(), array(), '->getAcceptableContentTypes() returns an empty array if the client send an empty ACCEPT header');

$request->acceptableContentTypes = null;
$_SERVER['HTTP_ACCEPT'] = 'text/xml,application/xhtml+xml,application/xml,text/html;q=0.9,text/plain;q=0.8,*/*;q=0.5';
$t->is($request->getAcceptableContentTypes(), array('text/xml', 'application/xml', 'application/xhtml+xml', 'text/html', 'text/plain', '*/*'), '->getAcceptableContentTypes() returns an array with all accepted content types');

// ->splitHttpAcceptHeader()
$t->diag('->splitHttpAcceptHeader()');

$t->is($request->splitHttpAcceptHeader(''), array(), '->splitHttpAcceptHeader() returns an empty array if the header is empty');
$t->is($request->splitHttpAcceptHeader('a,b,c'), array('c', 'b', 'a'), '->splitHttpAcceptHeader() returns an array of values');
$t->is($request->splitHttpAcceptHeader('a,b;q=0.7,c;q=0.3'), array('a', 'b', 'c'), '->splitHttpAcceptHeader() strips the q value');
$t->is($request->splitHttpAcceptHeader('a;q=0.1,b,c;q=0.3'), array('b', 'c', 'a'), '->splitHttpAcceptHeader() sorts values by the q value');

// ->getRequestFormat() ->setRequestFormat()
$t->diag('->getRequestFormat() ->setRequestFormat()');

$t->ok(is_null($request->getRequestFormat()), '->getRequestFormat() returns null if the format is not defined in the request');
$request->setParameter('sf_format', 'js');
$t->is($request->getRequestFormat(), 'js', '->getRequestFormat() returns the request format');

$request->setRequestFormat('css');
$t->is($request->getRequestFormat(), 'css', '->setRequestFormat() sets the request format');

// ->getFormat() ->setFormat()
$t->diag('->getFormat() ->setFormat()');
$request->setFormat('js', 'application/x-javascript');
$t->is($request->getFormat('application/x-javascript'), 'js', '->getFormat() returns the format for the given mime type');
$request->setFormat('js', array('application/x-javascript', 'text/js'));
$t->is($request->getFormat('text/js'), 'js', '->setFormat() can take an array of mime types');
$t->is($request->getFormat('foo/bar'), null, '->getFormat() returns null if the mime type does not exist');

// ->getMimeType()
$t->diag('->getMimeType()');
$t->is($request->getMimeType('js'), 'application/x-javascript', '->getMimeType() returns the first mime type for the given format');
$t->is($request->getMimeType('foo'), null, '->getMimeType() returns null if the format does not exist');

// ->getUriPrefix()
$t->diag('->getUriPrefix()');
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['HTTP_HOST'] = 'symfony-project.org:80';
$t->is($request->getUriPrefix(), 'http://symfony-project.org', '->getUriPrefix() returns no port for standard http port');
$_SERVER['HTTP_HOST'] = 'symfony-project.org';
$t->is($request->getUriPrefix(), 'http://symfony-project.org', '->getUriPrefix() works fine with no port in HTTP_HOST');
$_SERVER['HTTP_HOST'] = 'symfony-project.org:8088';
$t->is($request->getUriPrefix(), 'http://symfony-project.org:8088', '->getUriPrefix() works for nonstandard http ports');

$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = '443';
$_SERVER['HTTP_HOST'] = 'symfony-project.org:443';
$t->is($request->getUriPrefix(), 'https://symfony-project.org', '->getUriPrefix() returns no port for standard https port');
$_SERVER['HTTP_HOST'] = 'symfony-project.org';
$t->is($request->getUriPrefix(), 'https://symfony-project.org', '->getUriPrefix() works fine with no port in HTTP_HOST');
$_SERVER['HTTP_HOST'] = 'symfony-project.org:8043';
$t->is($request->getUriPrefix(), 'https://symfony-project.org:8043', '->getUriPrefix() works for nonstandard https ports');

// ->getRemoteAddress()
$t->diag('->getRemoteAddress()');
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$t->is($request->getRemoteAddress(), '127.0.0.1', '->getRemoteAddress() returns the remote address');

// ->getForwardedFor()
$t->diag('->getForwardedFor()');
$t->is($request->getForwardedFor(), null, '->getForwardedFor() returns null if the request was not forwarded.');
$_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1, 10.0.0.2';
$t->is_deeply($request->getForwardedFor(), array('10.0.0.1', '10.0.0.2'), '->getForwardedFor() returns the value from HTTP_X_FORWARDED_FOR');

// methods
$t->diag('methods');
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['sf_method'] = 'PUT';
$request = new myRequest($dispatcher);
$t->is($request->getMethod(), 'PUT', '->getMethod() returns the "sf_method" parameter value if it exists and if the method is POST');

$_SERVER['REQUEST_METHOD'] = 'GET';
$_POST['sf_method'] = 'PUT';
$request = new myRequest($dispatcher);
$t->is($request->getMethod(), 'GET', '->getMethod() returns the "sf_method" parameter value if it exists and if the method is POST');

$_SERVER['REQUEST_METHOD'] = 'POST';
unset($_POST['sf_method']);
$request = new myRequest($dispatcher);
$t->is($request->getMethod(), 'POST', '->getMethod() returns the "sf_method" parameter value if it exists and if the method is POST');
