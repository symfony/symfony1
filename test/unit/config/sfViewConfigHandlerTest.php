<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(1, new lime_output_color());

class myViewConfigHandler extends sfViewConfigHandler
{
  public function setConfig($config)
  {
    $this->yamlConfig = $config;
  }

  public function addHtmlAsset($viewName = '')
  {
    return parent::addHtmlAsset($viewName);
  }
}

$handler = new myViewConfigHandler();
$handler->setConfig(array(
  'myView' => array(
    'stylesheets' => array('foobar', '-*', 'bar'),
    'javascripts' => array('foobar', '-*', 'bar'),
  ),
  'all' => array(
    'stylesheets' => array('all_foo', 'all_foofoo', 'all_barbar'),
    'javascripts' => array('all_foo', 'all_foofoo', 'all_barbar'),
  ),
  'default' => array(
    'stylesheets' => array('default_foo', 'default_foofoo', 'default_barbar'),
    'javascripts' => array('default_foo', 'default_foofoo', 'default_barbar'),
  ),
));
$content = <<<EOF
  \$response->addStylesheet('foobar', '', array ());
  \$response->addStylesheet('bar', '', array ());
  \$response->addJavascript('foobar');
  \$response->addJavascript('bar');

EOF;
$t->is($handler->addHtmlAsset('myView'), $content, '');
