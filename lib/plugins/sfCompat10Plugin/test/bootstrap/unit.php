<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(dirname(__FILE__).'/..');
require_once($_test_dir.'/../../../vendor/lime/lime.php');
require_once($_test_dir.'/../../../config/sfConfig.class.php');
sfConfig::set('sf_symfony_lib_dir', realpath($_test_dir.'/../lib'));
sfConfig::set('sf_symfony_data_dir', realpath($_test_dir.'/../data'));

require_once(dirname(__FILE__).'/../../../../autoload/sfSimpleAutoload.class.php');
require_once(dirname(__FILE__).'/../../../../util/sfToolkit.class.php');
$autoload = sfSimpleAutoload::getInstance(sfToolkit::getTmpDir().DIRECTORY_SEPARATOR.sprintf('sf_autoload_unit_%s.data', md5(__FILE__)));
$autoload->addDirectory(realpath(dirname(__FILE__).'/../../../..'));
$autoload->addDirectory(realpath(dirname(__FILE__).'/../../lib'));
$autoload->addFile(dirname(__FILE__).'/../../../../exception/sfValidatorException.class.php');
$autoload->register();

sfConfig::set('sf_test_cache_dir', sfToolkit::getTmpDir());

class sfException extends Exception
{
  private $name = null;

  protected function setName($name)
  {
    $this->name = $name;
  }

  public function getName()
  {
    return $this->name;
  }
}
