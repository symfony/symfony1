<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(dirname(__FILE__).'/..');
define('SF_ROOT_DIR', realpath($_test_dir.'/..'));

// symfony directories
if (isset($sf_symfony_lib_dir) && isset($sf_symfony_data_dir))
{
}
else if (is_readable(SF_ROOT_DIR.'/lib/symfony/symfony.php'))
{
  // directory or symlink exists
  $sf_symfony_lib_dir  = SF_ROOT_DIR.'/lib/symfony';
  $sf_symfony_data_dir = SF_ROOT_DIR.'/data/symfony';
}
else
{
  // PEAR config
  if ((include('symfony/pear.php')) != 'OK')
  {
    throw new Exception('Unable to find symfony libraries');
  }
}

require_once($sf_symfony_lib_dir.'/vendor/lime/lime.php');
