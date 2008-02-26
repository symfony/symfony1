<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!isset($root_dir))
{
  $root_dir = realpath(dirname(__FILE__).sprintf('/../%s/fixtures', isset($type) ? $type : 'functional'));
}

$class = $app.'Configuration';
require $root_dir.'/lib/'.$class.'.class.php';
$configuration = new $class('test', isset($debug) ? $debug : true);
sfContext::createInstance($configuration);

// remove all cache
sfToolkit::clearDirectory(sfConfig::get('sf_app_cache_dir'));

return true;
