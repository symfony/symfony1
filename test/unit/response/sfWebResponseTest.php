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
require_once($_test_dir.'/unit/sfContextMock.class.php');
require_once($_test_dir.'/unit/bootstrap.php');

$t = new lime_test(5, new lime_output_color());

$context = new sfContext();
$response = sfResponse::newInstance('sfWebResponse');
$response->initialize($context);

sfConfig::set('sf_charset', 'UTF-8');

// ->getContentType() ->setContentType()
$t->diag('->getContentType() ->setContentType()');
$t->is($response->getContentType(), 'text/html; charset=UTF-8', '->getContentType() returns a sensible default value');

$response->setContentType('text/xml');
$t->is($response->getContentType(), 'text/xml; charset=UTF-8', '->setContentType() adds a charset if none is given');

$response->setContentType('text/xml; charset=ISO-8859-1');
$t->is($response->getContentType(), 'text/xml; charset=ISO-8859-1', '->setContentType() does nothing if a charset is given');

$response->setContentType('text/xml;charset = ISO-8859-1');
$t->is($response->getContentType(), 'text/xml;charset = ISO-8859-1', '->setContentType() does nothing if a charset is given');

$response->setContentType('text/xml');
$response->setContentType('text/html');
$t->is(count($response->getHttpHeader('content-type')), 1, '->setContentType() overrides previous content type if replace is true');
