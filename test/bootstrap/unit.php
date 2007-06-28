<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(dirname(__FILE__).'/..');
require_once($_test_dir.'/../lib/vendor/lime/lime.php');
require_once($_test_dir.'/../lib/config/sfConfig.class.php');
sfConfig::set('sf_symfony_lib_dir', realpath($_test_dir.'/../lib'));
sfConfig::set('sf_symfony_data_dir', realpath($_test_dir.'/../data'));

require_once(dirname(__FILE__).'/testAutoloader.class.php');

ini_set('unserialize_callback_func', 'spl_autoload_call');
testAutoloader::initialize();
spl_autoload_register(array('testAutoloader', 'autoload'));

class sfException extends Exception
{
  private $name = null;

  protected function setName ($name)
  {
    $this->name = $name;
  }

  public function getName ()
  {
    return $this->name;
  }
}
