<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelPropel adds a panel to the web debug toolbar with Propel information.
 *
 * @package    symfony
 * @subpackage debug
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWebDebugPanelPropel extends sfWebDebugPanel
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

  public function getLinkText()
  {
    if ($sqlLogs = $this->getSqlLogs())
    {
      return '<img src="'.$this->webDebug->getOption('image_root_path').'/database.png" /> '.count($sqlLogs);
    }
  }

  public function getPanelContent()
  {
    return '
      <div id="sfWebDebugDatabaseLogs">
      <ol><li>'.implode("</li>\n<li>", $this->getSqlLogs()).'</li></ol>
      </div>
    ';
  }

  public function getTitle()
  {
    return 'SQL queries';
  }

  public function filterLogs(sfEvent $event, $logs)
  {
    $newLogs = array();
    foreach ($logs as $log)
    {
      if ('sfPropelLogger' != $log['type'])
      {
        $newLogs[] = $log;
      }
    }

    return $newLogs;
  }

  protected function getSqlLogs()
  {
    $logs = array();
    foreach ($this->webDebug->getLogger()->getLogs() as $log)
    {
      if (preg_match('/\b(SELECT|INSERT|UPDATE|DELETE)\b/', $log['message'], $match))
      {
        $logs[] = $log['message'];
      }
    }

    return $logs;
  }
}
