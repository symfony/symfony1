<?php

/**
 * Connection profiler.
 * 
 * @package    sfDoctrinePlugin
 * @subpackage database
 * @author     Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfDoctrineConnectionProfiler extends Doctrine_Connection_Profiler
{
  protected
    $dispatcher = null,
    $options    = array();

  /**
   * Constructor.
   * 
   * Available options:
   * 
   *  * logging: Whether to notify query logging events (defaults to false)
   * 
   * @param sfEventDispatcher $dispatcher
   * @param array             $options
   */
  public function __construct(sfEventDispatcher $dispatcher, $options = array())
  {
    $this->dispatcher = $dispatcher;
    $this->options = array_merge(array('logging' => false), $options);
  }

  /**
   * Logs time and a connection query on behalf of the connection.
   * 
   * @param Doctrine_Event $event
   */
  public function preQuery(Doctrine_Event $event)
  {
    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($event->getInvoker(), 'application.log', array(sprintf('query : %s - (%s)', $event->getQuery(), join(', ', self::fixParams($event->getParams()))))));
    }

    sfTimerManager::getTimer('Database (Doctrine)');

    parent::preQuery($event);
  }

  /**
   * Logs to the timer.
   * 
   * @param Doctrine_Event $event
   */
  public function postQuery(Doctrine_Event $event)
  {
    sfTimerManager::getTimer('Database (Doctrine)')->addTime();

    parent::postQuery($event);
  }

  /**
   * Logs a connection exec on behalf of the connection.
   * 
   * @param Doctrine_Event $event
   */
  public function preExec(Doctrine_Event $event)
  {
    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($event->getInvoker(), 'application.log', array(sprintf('exec : %s - (%s)', $event->getQuery(), join(', ', self::fixParams($event->getParams()))))));
    }

    sfTimerManager::getTimer('Database (Doctrine)');

    parent::preExec($event);
  }

  /**
   * Logs to the timer.
   * 
   * @param Doctrine_Event $event
   */
  public function postExec(Doctrine_Event $event)
  {
    sfTimerManager::getTimer('Database (Doctrine)')->addTime();

    parent::postExec($event);
  }

  /**
   * Logs a statement execute on behalf of the statement.
   * 
   * @param Doctrine_Event $event
   */
  public function preStmtExecute(Doctrine_Event $event)
  {
    if ($this->options['logging'])
    {
      $this->dispatcher->notify(new sfEvent($event->getInvoker(), 'application.log', array(sprintf('execute : %s - (%s)', $event->getQuery(), join(', ', self::fixParams($event->getParams()))))));
    }

    sfTimerManager::getTimer('Database (Doctrine)');

    parent::preStmtExecute($event);
  }

  /**
   * Logs to the timer.
   * 
   * @param Doctrine_Event $event
   */
  public function postStmtExecute(Doctrine_Event $event)
  {
    sfTimerManager::getTimer('Database (Doctrine)')->addTime();

    parent::postStmtExecute($event);
  }

  /**
   * Returns events having to do with query execution.
   *
   * @return array
   */
  public function getQueryExecutionEvents()
  {
    $events = array();
    foreach ($this as $event)
    {
      if (in_array($event->getCode(), array(Doctrine_Event::CONN_QUERY, Doctrine_Event::CONN_EXEC, Doctrine_Event::STMT_EXECUTE)))
      {
        $events[] = $event;
      }
    }

    return $events;
  }

  /**
   * Fixes query parameters for logging.
   * 
   * @param  array $params
   * 
   * @return array
   */
  static public function fixParams($params)
  {
    foreach ($params as $key => $param)
    {
      if (strlen($param) >= 255)
      {
        $params[$key] = '['.number_format(strlen($param) / 1024, 2).'Kb]';
      }
    }

    return $params;
  }
}
