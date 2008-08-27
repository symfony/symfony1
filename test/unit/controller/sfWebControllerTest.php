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

$t = new lime_test(26, new lime_output_color());

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/index.php';
sfConfig::set('sf_url_format', 'PATH');
sfConfig::set('sf_max_forwards', 10);
$context = sfContext::getInstance(array(
  'routing'  => 'sfNoRouting',
  'request'  => 'sfWebRequest',
  'response' => 'sfWebResponse',
));

$controller = new sfFrontWebController($context, null);

$tests = array(
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
  'module/action?id=12&' => array(
    '',
    array(
      'module' => 'module',
      'action' => 'action',
      'id'     => '12&',
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
  'module/action?id=12&test=4&5&6&7&&toto=9' => array(
    '',
    array(
      'module' => 'module',
      'action' => 'action',
      'id'     => 12,
      'test'   => '4&5&6&7&',
      'toto'   => 9,
    ),
  ),
  'module/action?test=value1&value2&toto=9' => array(
    '',
    array(
      'module' => 'module',
      'action' => 'action',
      'test'   => 'value1&value2',
      'toto'   => 9,
    ),
  ),
  'module/action?test=value1&value2' => array(
    '',
    array(
      'module' => 'module',
      'action' => 'action',
      'test'   => 'value1&value2',
    ),
  ),
  'module/action?test=value1=value2&toto=9' => array(
    '',
    array(
      'module' => 'module',
      'action' => 'action',
      'test'   => 'value1=value2',
      'toto'   => 9,
    ),
  ),
  'module/action?test=value1=value2' => array(
    '',
    array(
      'module' => 'module',
      'action' => 'action',
      'test'   => 'value1=value2',
    ),
  ),
  'module/action?test=4&5&6&7&&toto=9&id=' => array(
    '',
    array(
      'module' => 'module',
      'action' => 'action',
      'test'   => '4&5&6&7&',
      'toto'   => 9,
      'id'     => '',
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
  '@test?id=foo%26bar&foo=bar%3Dfoo' => array(
    'test',
    array(
      'id' => 'foo&bar',
      'foo' => 'bar=foo',
    ),
  ),
);

// ->convertUrlStringToParameters()
$t->diag('->convertUrlStringToParameters()');
foreach ($tests as $url => $result)
{
  $t->is($controller->convertUrlStringToParameters($url), $result, sprintf('->convertUrlStringToParameters() converts a symfony internal URI to an array of parameters (%s)', $url));
}

try
{
  $controller->convertUrlStringToParameters('@test?foobar');
  $t->fail('->convertUrlStringToParameters() throw a sfParseException if it cannot parse the query string');
}
catch (sfParseException $e)
{
  $t->pass('->convertUrlStringToParameters() throw a sfParseException if it cannot parse the query string');
}

// ->redirect()
$t->diag('->redirect()');
sfConfig::set('sf_test', true);
sfConfig::set('sf_charset', 'utf-8');
ob_start();
$controller->redirect('module/action?id=1#photos');
$content = ob_get_clean();
$t->like($content, '~http\://localhost/index.php/\?action=action&amp;module=module&amp;id=1#photos~', '->redirect() adds a refresh meta in the content');
$t->like($context->getResponse()->getHttpHeader('Location'), '~http\://localhost/index.php/\?action=action&module=module&id=1#photos~', '->redirect() adds a Location HTTP header');

// ->genUrl()
$t->diag('->genUrl()');
$t->is($controller->genUrl('module/action?id=4'), $controller->genUrl(array('action' => 'action', 'module' => 'module', 'id' => 4)), '->genUrl() accepts a string or an array as its first argument');

sfConfig::set('sf_relative_url_root', null);
sfConfig::set('sf_no_script_name', true);
$referenceUrl = $controller->genUrl('module/action');
$referenceRootUrl = $controller->genUrl('@test');


// ->genUrl() with no sf_relative_url_root
sfConfig::set('sf_relative_url_root', null);

sfConfig::set('sf_no_script_name', true);
$t->is($controller->genUrl('module/action'), $referenceUrl, '->genUrl() with no relative_url_root and no_script_name==true');
$t->is($controller->genUrl('@test'), $referenceRootUrl, '->genUrl() with no relative_url_root and no_script_name==true (root url)');

sfConfig::set('sf_no_script_name', false);
$t->is($controller->genUrl('module/action'), $_SERVER['SCRIPT_NAME'].$referenceUrl, '->genUrl() with no relative_url_root and no_script_name==false');
$t->is($controller->genUrl('@test'), $_SERVER['SCRIPT_NAME'].$referenceRootUrl, '->genUrl() with no relative_url_root and no_script_name==false (root url)');


// ->genUrl() with sf_relative_url_root to something
sfConfig::set('sf_relative_url_root', $relativeUrlRoot='/webroot');
$context->getRequest()->setRelativeUrlRoot($relativeUrlRoot);

sfConfig::set('sf_no_script_name', true);
$t->is($controller->genUrl('module/action'), $relativeUrlRoot.$referenceUrl, '->genUrl() with a relative_url_root set and no_script_name==true');
$t->is($controller->genUrl('@test'), $relativeUrlRoot.$referenceRootUrl, '->genUrl() with a relative_url_root set and no_script_name==true (root url)');

sfConfig::set('sf_no_script_name', false);
$t->is($controller->genUrl('module/action'), $relativeUrlRoot.$_SERVER['SCRIPT_NAME'].$referenceUrl, '->genUrl() with a relative_url_root set and no_script_name==false');
$t->is($controller->genUrl('@test'), $relativeUrlRoot.$_SERVER['SCRIPT_NAME'].$referenceRootUrl, '->genUrl() with a relative_url_root set and no_script_name==false (root url)');
