<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfPropelBaseTask.class.php');

/**
 * Creates database for current model.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelBuildDbTask extends sfPropelBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->aliases = array('propel-build-db');
    $this->namespace = 'propel';
    $this->name = 'build-db';
    $this->briefDescription = 'Creates database for current model';

    $this->detailedDescription = <<<EOF
The [propel:build-db|INFO] task creates the database:

  [./symfony propel:build-db|INFO]

The task read connection information in [config/propel.ini|COMMENT]:

  [propel.database.createUrl = mysql://username@password:hostname/|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->callPhing('create-db', self::DO_NOT_CHECK_SCHEMA);
  }
}
