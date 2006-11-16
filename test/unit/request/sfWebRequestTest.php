<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(13, new lime_output_color());

class myRequest extends sfWebRequest
{
  public $languages = null;
  public $charsets = null;
  public $acceptableContentTypes = null;
}

$context = new sfContext();
$request = sfRequest::newInstance('myRequest');

// ->getLanguages()
$t->diag('->getLanguages()');

$t->is($request->getLanguages(), array(), '->getLanguages() returns an empty array if the client do not send an ACCEPT_LANGUAGE header');

$request->languages = null;
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
$t->is($request->getLanguages(), array(), '->getLanguages() returns an empty array if the client send an empty ACCEPT_LANGUAGE header');

$request->languages = null;
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-us,en;q=0.5,fr;q=0.3';
$t->is($request->getLanguages(), array('en_US', 'en', 'fr'), '->getLanguages() returns an array with all accepted languages');

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
