<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

error_reporting(error_reporting() & ~E_STRICT);

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

class mySymfonyPluginManager extends sfSymfonyPluginManager
{
  public function getRelativePath($from, $to, $topLevel)
  {
    return parent::getRelativePath($from, $to, $topLevel);
  }
}

$t = new lime_test(2, new lime_output_color());

$options = array(
  'sf_root_dir'        => DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'sfproject',
  'cache_dir'          => DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'sfproject'.DIRECTORY_SEPARATOR.'cache',
  'plugin_dir'         => DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'sfproject'.DIRECTORY_SEPARATOR.'plugins',
  'web_dir'            => DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'sfproject'.DIRECTORY_SEPARATOR.'web'
);

$dispatcher = new sfEventDispatcher();
$t->diag('->initialize()');
$environment = new sfPearEnvironment($dispatcher, $options);
$pluginManager = new mySymfonyPluginManager($dispatcher, $environment);
$t->is($pluginManager->getEnvironment(), $environment, '->initialize() takes a sfPearEnvironment as its second argument');

$t->diag('sfPluginManager calculates relative pathes');
$source = $options['sf_root_dir'].'webdir'.DIRECTORY_SEPARATOR.'myplugin';
$target = $options['sf_root_dir'].'plugin'.DIRECTORY_SEPARATOR.'myplugin'.DIRECTORY_SEPARATOR.'web';
$t->is($pluginManager->getRelativePath($source, $target, $options['sf_root_dir']), '..'.DIRECTORY_SEPARATOR.'plugin'.DIRECTORY_SEPARATOR.'myplugin'.DIRECTORY_SEPARATOR.'web', '->getRelativePath() correctly calculates the relative path');