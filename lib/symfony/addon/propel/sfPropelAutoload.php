<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony.runtime.addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
require_once 'propel/Propel.php';


// check orm configuration
$orm_config = sfConfig::get('sf_app_config_dir_name').'/orm.yml';
sfConfigCache::checkConfig($orm_config);

if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_active'))
{
  // register debug driver
  require_once 'creole/Creole.php';
  Creole::registerDriver('*', 'symfony.addon.creole.drivers.sfDebugConnection');

  // register our logger
  require_once 'symfony/addon/creole/drivers/sfDebugConnection.php';
  sfDebugConnection::setLogger(sfLogger::getInstance());
}

// propel initialization
Propel::init(sfConfigCache::getCacheName($orm_config));

?>