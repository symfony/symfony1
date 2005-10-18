<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@gmail.com>
 * (c) 2004, 2005 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage core
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @version    SVN: $Id$
 */

/**
 * sfContext provides information about the current application context, such as
 * the module and action names and the module directory. References to the
 * current controller, request, and user implementation instances are also
 * provided.
 *
 * @package    symfony
 * @subpackage core
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfContext
{
  private
    $actionStack       = null,
    $controller        = null,
    $databaseManager   = null,
    $request           = null,
    $storage           = null,
    $securityFilter    = null,
    $viewCacheManager  = null,
    $logger            = null,
    $user              = null;

  private static
    $instance          = null;

  /**
   * Removes current sfContext instance
   *
   * This method only exists for testing purpose. Don't use it in your application code.
   */
  public static function removeInstance()
  {
    self::$instance = null;
  }

  private function initialize()
  {
    if (SF_LOGGING_ACTIVE)
    {
      $this->logger = sfLogger::getInstance();
    }

    if (SF_LOGGING_ACTIVE) $this->logger->info('{sfContext} initialization');

    if (SF_USE_DATABASE)
    {
      // setup our database connections
      $this->databaseManager = new sfDatabaseManager();
      $this->databaseManager->initialize();
    }

    if (SF_CACHE)
    {
      $this->viewCacheManager = new sfViewCacheManager();
    }

    // create a new action stack
    $this->actionStack = new sfActionStack();

    // include the factories configuration
    require(sfConfigCache::checkConfig(SF_APP_CONFIG_DIR_NAME.'/factories.yml'));

    if (SF_CACHE)
    {
      $this->viewCacheManager->initialize($this);
    }

    // register our shutdown function
    register_shutdown_function(array($this, 'shutdown'));
  }

  /**
   * Retrieve the singleton instance of this class.
   *
   * @return sfContext A sfController implementation instance.
   */
  public static function getInstance()
  {
    if (!isset(self::$instance))
    {
      $class = __CLASS__;
      self::$instance = new $class();
      self::$instance->initialize();
    }

    return self::$instance;
  }

  /**
   * Retrieve the action name for this context.
   *
   * @return string The currently executing action name, if one is set,
   *                otherwise null.
   */
  public function getActionName ()
  {
    // get the last action stack entry
    $actionEntry = $this->actionStack->getLastEntry();

    return $actionEntry->getActionName();
  }


  /**
   * Retrieve the ActionStack.
   *
   * @return sfActionStack the sfActionStack instance
   */
  public function getActionStack()
  {
    return $this->actionStack;
  }

  /**
   * Retrieve the controller.
   *
   * @return sfController The current sfController implementation instance.
   */
   public function getController ()
   {
     return $this->controller;
   }

   public function getLogger ()
   {
     return $this->logger;
   }

  /**
   * Retrieve a database connection from the database manager.
   *
   * This is a shortcut to manually getting a connection from an existing
   * database implementation instance.
   *
   * If the SF_USE_DATABASE setting is off, this will return null.
   *
   * @param name A database name.
   *
   * @return mixed A Database instance.
   *
   * @throws <b>sfDatabaseException</b> If the requested database name does not exist.
   */
  public function getDatabaseConnection ($name = 'default')
  {
    if ($this->databaseManager != null)
      return $this->databaseManager->getDatabase($name)->getConnection();

    return null;
  }

  /**
   * Retrieve the database manager.
   *
   * @return DatabaseManager The current DatabaseManager instance.
   */
  public function getDatabaseManager ()
  {
    return $this->databaseManager;
  }

  /**
   * Retrieve the module directory for this context.
   *
   * @return string An absolute filesystem path to the directory of the
   *                currently executing module, if one is set, otherwise null.
   */
  public function getModuleDirectory ()
  {
    // get the last action stack entry
    $actionEntry = $this->actionStack->getLastEntry();

    return SF_APP_MODULE_DIR.'/'.$actionEntry->getModuleName();
  }

  /**
   * Retrieve the module name for this context.
   *
   * @return string The currently executing module name, if one is set,
   *                otherwise null.
   */
  public function getModuleName ()
  {
    // get the last action stack entry
    $actionEntry = $this->actionStack->getLastEntry();

    return $actionEntry->getModuleName();
  }

  /**
   * Retrieve the request.
   *
   * @return Request The current Request implementation instance.
   */
  public function getRequest ()
  {
    return $this->request;
  }

  /**
   * Retrieve the storage.
   *
   * @return Storage The current Storage implementation instance.
   */
  public function getStorage ()
  {
    return $this->storage;
  }

  /**
   * Retrieve the securityFilter
   *
   * @return SecurityFilter The current SecurityFilter implementation instance.
   */
  public function getSecurityFilter ()
  {
    return $this->securityFilter;
  }

  /**
   * Retrieve the securityFilter
   *
   * @return SecurityFilter The current SecurityFilter implementation instance.
   */
  public function getViewCacheManager ()
  {
    return $this->viewCacheManager;
  }

  /**
   * Retrieve the user.
   *
   * @return User The current User implementation instance.
   */
  public function getUser ()
  {
    return $this->user;
  }

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   */
  public function shutdown ()
  {
    // shutdown all factories
    $this->getUser()->shutdown();
    $this->getStorage()->shutdown();
    $this->getRequest()->shutdown();

    if (SF_USE_DATABASE)
    {
      $this->getDatabaseManager()->shutdown();
    }

    if (SF_CACHE)
    {
      $this->getViewCacheManager()->shutdown();
    }
  }
}

?>