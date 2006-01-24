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
  static $config = array();

  public function initialize ($parameters = null, $datasource = 'symfony')
  {
    parent::initialize($parameters);

    $this->addConfig($datasource);

    $is_default = $this->getParameter('is_default', false);

    // first defined if none listed as default
    if ($is_default || count(self::$config['propel']['datasources']) == 1)
    {
      $this->setDefaultConfig($datasource);
    }
  }

  public function setDefaultConfig ($datasource) {
    self::$config['propel']['datasources']['default'] = $this->getParameter('datasource', $datasource);
  }

  public function addConfig ($datasource)
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

    $datasource = $this->getParameter('datasource', $datasource);
    self::$config['propel']['datasources'][$datasource] =
      array(
        'adapter' =>    $this->getParameter('phptype'),
        'connection' =>
        array(
          'phptype'  => $this->getParameter('phptype'),
          'hostspec' => $this->getParameter('hostspec'),
          'database' => $this->getParameter('database'),
          'username' => $this->getParameter('username'),
          'password' => $this->getParameter('password'),
        ),
      );
  }

  public static function getConfiguration ()
  {
    return self::$config;
  }
}

?>