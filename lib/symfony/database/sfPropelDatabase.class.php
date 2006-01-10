<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A symfony database driver for Propel, derived from the native Creole driver. 
 * 
 * <b>Optional parameters:</b>
 *
 * # <b>datasource</b>     - [symfony] - datasource to use for the connection
 * # <b>is_default</b>     - [false]   - use as default if multiple connections
 *                                       are specified. The parameters 
 *                                       that has been flagged using this param
 *                                       is be used when Propel is initialized
 *                                       via sfPropelAutoload.
 *
 * @package    symfony
 * @subpackage database
 * 
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelDatabase extends sfCreoleDatabase
{
  static $defaultConfig = array();

  public function initialize ($parameters = null)
  {
    parent::initialize($parameters);

    $is_default = $this->getParameter('is_default', true);

    if ($is_default)
    {
      $this->setDefaultConfig();
    }
  }

  public function setDefaultConfig ()
  {
    $dsn = $this->getParameter('dsn');

    if ($dsn)
    {
      require_once('creole/Creole.php');
      $params = Creole::parseDSN($dsn);

      $this->setParameter('phptype',  $params['phptype']);
      $this->setParameter('hostspec', $params['hostspec']);
      $this->setParameter('database', $params['database']);
      $this->setParameter('username', $params['username']);
      $this->setParameter('password', $params['password']);
    }

    self::$defaultConfig = array (
      'propel' =>
      array (
        'datasources' =>
        array (
          $this->getParameter('datasource', 'symfony') =>
          array (
            'adapter' =>    $this->getParameter('phptype'),
            'connection' =>
            array (
              'phptype'  => $this->getParameter('phptype'),
              'hostspec' => $this->getParameter('hostspec'),
              'database' => $this->getParameter('database'),
              'username' => $this->getParameter('username'),
              'password' => $this->getParameter('password'),
            ),
          ),
          'default' => $this->getParameter('datasource', 'symfony'),
        ),
      ),
    );
  }

  public static function getDefaultConfiguration ()
  {
    return self::$defaultConfig;
  }
}

?>