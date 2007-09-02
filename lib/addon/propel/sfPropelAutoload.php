<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
require_once 'propel/Propel.php';

if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled'))
{
  // register debug driver
  require_once 'creole/Creole.php';
  Creole::registerDriver('*', 'symfony.addon.creole.drivers.sfDebugConnection');

  // register our logger
  require_once(sfConfig::get('sf_symfony_lib_dir').'/addon/creole/drivers/sfDebugConnection.php');
  sfDebugConnection::setDispatcher(sfContext::getInstance()->getEventDispatcher());
}

// propel initialization
Propel::setConfiguration(sfPropelDatabase::getConfiguration());
Propel::initialize();
