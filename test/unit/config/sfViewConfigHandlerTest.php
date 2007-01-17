<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(20, new lime_output_color());

class myViewConfigHandler extends sfViewConfigHandler
{
  public function mergeConfig($config)
  {
    parent::mergeConfig($config);
  }

  public function addHtmlAsset($viewName = '')
  {
    return parent::addHtmlAsset($viewName);
  }
}

$handler = new myViewConfigHandler();

// addHtmlAsset() basic asset addition
$t->diag('addHtmlAsset() basic asset addition');

$handler->mergeConfig(array(
  'myView' => array(
    'stylesheets' => array('foobar'),
  ),
));
$content = <<<EOF
  \$response->addStylesheet('foobar', '', array ());

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() adds stylesheets to the response');

$handler->mergeConfig(array(
  'myView' => array(
    'javascripts' => array('foobar'),
  ),
));
$content = <<<EOF
  \$response->addJavascript('foobar');

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() adds JavaScript to the response');

// Insertion order for stylesheets
$t->diag('addHtmlAsset() insertion order for stylesheets');

$handler->mergeConfig(array(
  'myView' => array(
    'stylesheets' => array('foobar'),
  ),
  'all' => array(
    'stylesheets' => array('all_foobar'),
  ),
));
$content = <<<EOF
  \$response->addStylesheet('all_foobar', '', array ());
  \$response->addStylesheet('foobar', '', array ());

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() adds view-specific stylesheets after application-wide assets');

$handler->mergeConfig(array(
  'all' => array(
    'stylesheets' => array('all_foobar'),
  ),
  'myView' => array(
    'stylesheets' => array('foobar'),
  ),
));
$content = <<<EOF
  \$response->addStylesheet('all_foobar', '', array ());
  \$response->addStylesheet('foobar', '', array ());

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() adds view-specific stylesheets after application-wide assets');

$handler->mergeConfig(array(
  'myView' => array(
    'stylesheets' => array('foobar'),
  ),
  'default' => array(
    'stylesheets' => array('default_foobar'),
  ),
));
$content = <<<EOF
  \$response->addStylesheet('default_foobar', '', array ());
  \$response->addStylesheet('foobar', '', array ());

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() adds view-specific stylesheets after default assets');

$handler->mergeConfig(array(
  'default' => array(
    'stylesheets' => array('default_foobar'),
  ),
  'myView' => array(
    'stylesheets' => array('foobar'),
  ),
));
$content = <<<EOF
  \$response->addStylesheet('default_foobar', '', array ());
  \$response->addStylesheet('foobar', '', array ());

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() adds view-specific stylesheets after default assets');

$handler->mergeConfig(array(
  'default' => array(
    'stylesheets' => array('default_foobar'),
  ),
  'all' => array(
    'stylesheets' => array('all_foobar'),
  ),
));
$content = <<<EOF
  \$response->addStylesheet('default_foobar', '', array ());
  \$response->addStylesheet('all_foobar', '', array ());

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() adds application-specific stylesheets after default assets');

$handler->mergeConfig(array(
  'all' => array(
    'stylesheets' => array('all_foobar'),
  ),
  'default' => array(
    'stylesheets' => array('default_foobar'),
  ),
));
$content = <<<EOF
  \$response->addStylesheet('default_foobar', '', array ());
  \$response->addStylesheet('all_foobar', '', array ());

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() adds application-specific stylesheets after default assets');

// Insertion order for javascripts
$t->diag('addHtmlAsset() insertion order for javascripts');

$handler->mergeConfig(array(
  'myView' => array(
    'javascripts' => array('foobar'),
  ),
  'all' => array(
    'javascripts' => array('all_foobar'),
  ),
));
$content = <<<EOF
  \$response->addJavascript('all_foobar');
  \$response->addJavascript('foobar');

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() adds view-specific javascripts after application-wide assets');

$handler->mergeConfig(array(
  'all' => array(
    'javascripts' => array('all_foobar'),
  ),
  'myView' => array(
    'javascripts' => array('foobar'),
  ),
));
$content = <<<EOF
  \$response->addJavascript('all_foobar');
  \$response->addJavascript('foobar');

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() adds view-specific javascripts after application-wide assets');

$handler->mergeConfig(array(
  'myView' => array(
    'javascripts' => array('foobar'),
  ),
  'default' => array(
    'javascripts' => array('default_foobar'),
  ),
));
$content = <<<EOF
  \$response->addJavascript('default_foobar');
  \$response->addJavascript('foobar');

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() adds view-specific javascripts after default assets');

$handler->mergeConfig(array(
  'default' => array(
    'javascripts' => array('default_foobar'),
  ),
  'myView' => array(
    'javascripts' => array('foobar'),
  ),
));
$content = <<<EOF
  \$response->addJavascript('default_foobar');
  \$response->addJavascript('foobar');

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() adds view-specific javascripts after default assets');

$handler->mergeConfig(array(
  'default' => array(
    'javascripts' => array('default_foobar'),
  ),
  'all' => array(
    'javascripts' => array('all_foobar'),
  ),
));
$content = <<<EOF
  \$response->addJavascript('default_foobar');
  \$response->addJavascript('all_foobar');

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() adds application-specific javascripts after default assets');

$handler->mergeConfig(array(
  'all' => array(
    'javascripts' => array('all_foobar'),
  ),
  'default' => array(
    'javascripts' => array('default_foobar'),
  ),
));
$content = <<<EOF
  \$response->addJavascript('default_foobar');
  \$response->addJavascript('all_foobar');

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() adds application-specific javascripts after default assets');

// removal of assets
$t->diag('addHtmlAsset() removal of assets');

$handler->mergeConfig(array(
  'all' => array(
    'stylesheets' => array('all_foo', 'all_bar'),
  ),
  'myView' => array(
    'stylesheets' => array('foobar', '-all_bar'),
  ),
));
$content = <<<EOF
  \$response->addStylesheet('all_foo', '', array ());
  \$response->addStylesheet('foobar', '', array ());

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() supports the - option to remove one stylesheet previously added');

$handler->mergeConfig(array(
  'all' => array(
    'javascripts' => array('all_foo', 'all_bar'),
  ),
  'myView' => array(
    'javascripts' => array('foobar', '-all_bar'),
  ),
));
$content = <<<EOF
  \$response->addJavascript('all_foo');
  \$response->addJavascript('foobar');

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() supports the - option to remove one javascript previously added');

$handler->mergeConfig(array(
  'all' => array(
    'stylesheets' => array('foo', 'bar', '-*', 'baz'),
  ),
));
$content = <<<EOF
  \$response->addStylesheet('baz', '', array ());

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() supports the -* option to remove all stylesheets previously added');

$handler->mergeConfig(array(
  'all' => array(
    'javascripts' => array('foo', 'bar', '-*', 'baz'),
  ),
));
$content = <<<EOF
  \$response->addJavascript('baz');

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() supports the -* option to remove all javascripts previously added');

$handler->mergeConfig(array(
  'all' => array(
    'stylesheets' => array('-*', 'foobar'),
  ),
  'default' => array(
    'stylesheets' => array('default_foo', 'default_bar'),
  ),
));
$content = <<<EOF
  \$response->addStylesheet('foobar', '', array ());

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() supports the -* option to remove all assets previously added');

$handler->mergeConfig(array(
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
  \$response->addStylesheet('bar', '', array ());
  \$response->addJavascript('bar');

EOF;
$t->is(fix_content($handler->addHtmlAsset('myView')), fix_content($content), 'addHtmlAsset() supports the -* option to remove all assets previously added');

function fix_content($content)
{
  return str_replace(array("\r\n", "\n", "\r"), "\n", $content);
}
