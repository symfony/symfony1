<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelDoctrine adds a panel to the web debug toolbar with Doctrine information.
 *
 * @package    symfony
 * @subpackage debug
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfWebDebugPanelDoctrine.class.php 11205 2008-08-27 16:24:17Z fabien $
 */
class sfWebDebugPanelDoctrine extends sfWebDebugPanel
{
  /**
   * Constructor.
   *
   * @param sfWebDebug $webDebug The web debut toolbar instance
   */
  public function __construct(sfWebDebug $webDebug)
  {
    parent::__construct($webDebug);

    $this->webDebug->getEventDispatcher()->connect('debug.web.filter_logs', array($this, 'filterLogs'));
  }

  /**
   * Get the title/icon for the panel
   *
   * @return string $html
   */
  public function getTitle()
  {
    if ($events = $this->getDoctrineEvents())
    {
      return '<img src="'.$this->webDebug->getOption('image_root_path').'/database.png" alt="SQL queries" /> '.count($events);
    }
  }

  /**
   * Get the verbal title of the panel
   *
   * @return string $title
   */
  public function getPanelTitle()
  {
    return 'SQL queries';
  }

  /**
   * Get the html content of the panel
   *
   * @return string $html
   */
  public function getPanelContent()
  {
    return '
      <div id="sfWebDebugDatabaseLogs">
      <ol><li>'.implode("</li>\n<li>", $this->getSqlLogs()).'</li></ol>
      </div>
    ';
  }
  
  /**
   * Filters out Doctrine log entries.
   *
   * @param  sfEvent $event
   * @param  array   $logs
   *
   * @return array
   */
  public function filterLogs(sfEvent $event, $logs)
  {
    $newLogs = array();
    foreach ($logs as $log)
    {
      $r = new ReflectionClass($log['type']);
      if (!$r->isSubclassOf('Doctrine_Connection') && !$r->implementsInterface('Doctrine_Adapter_Statement_Interface'))
      {
        $newLogs[] = $log;
      }
    }

    return $newLogs;
  }

  /**
   * Hook to allow the loading of the Doctrine webdebug toolbar with the rest of the panels
   *
   * @param sfEvent $event 
   * @return void
   */
  static public function listenToAddPanelEvent(sfEvent $event)
  {
    $event->getSubject()->setPanel('db', new self($event->getSubject()));
  }

  /**
   * Returns an array of Doctrine query events.
   * 
   * @return array
   */
  protected function getDoctrineEvents()
  {
    $databaseManager = sfContext::getInstance()->getDatabaseManager();

    $events = array();
    foreach ($databaseManager->getNames() as $name)
    {
      $database = $databaseManager->getDatabase($name);
      if ($database instanceof sfDoctrineDatabase && $profiler = $database->getProfiler())
      {
        foreach ($profiler->getQueryExecutionEvents() as $event)
        {
          $events[$event->getSequence()] = $event;
        }
      }
    }

    // sequence events
    ksort($events);

    return $events;
  }

  /**
   * Builds the sql logs and returns them as an array.
   *
   * @return array
   */
  protected function getSqlLogs()
  {
    $logs = $this->webDebug->getLogger()->getLogs();

    $html = array();
    foreach ($this->getDoctrineEvents() as $i => $event)
    {
      $conn = $event->getInvoker() instanceof Doctrine_Connection ? $event->getInvoker() : $event->getInvoker()->getConnection();
      $params = sfDoctrineConnectionProfiler::fixParams($event->getParams());
      $sql = $this->formatSql($event->getQuery());

      // interpolate parameters
      foreach ($params as $param)
      {
        $sql = join(var_export($param, true), explode('?', $sql, 2));
      }

      // add meta info
      $query = sprintf('<span class="sfWebDebugDatabaseQuery">%s</span><br/><span class="sfWebDebugDatabaseLogInfo">%ss, "%s" connection</span>', $sql, number_format($event->getElapsedSecs(), 2), $conn->getName());

      // add backtrace
      foreach ($logs as $i => $log)
      {
        if (!$log['debug_backtrace'])
        {
          // backtrace disabled
          break;
        }

        if (false !== strpos($log['message'], $event->getQuery()))
        {
          // assume queries are being requested in order
          unset($logs[$i]);
          $query .= '&nbsp;'.$this->getToggleableDebugStack($log['debug_backtrace']);
          break;
        }
      }

      $html[] = $query;
    }

    return $html;
  }
}
