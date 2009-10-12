<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
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
   * Get the title/icon for the panel
   *
   * @return string $html
   */
  public function getTitle()
  {
    if ($sqlLogs = $this->getSqlLogs())
    {
      return '<img src="'.$this->webDebug->getOption('image_root_path').'/database.png" alt="SQL queries" /> '.count($sqlLogs);
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
        <ol>'.implode("\n", $this->getSqlLogs()).'</ol>
      </div>
    ';
  }

  /**
   * Listens to debug.web.load_panels and adds this panel.
   */
  static public function listenToAddPanelEvent(sfEvent $event)
  {
    $event->getSubject()->setPanel('db', new self($event->getSubject()));
  }

  /**
   * Builds the sql logs and returns them as an array.
   *
   * @return array
   */
  protected function getSqlLogs()
  {
    $threshold = $this->getSlowQueryThreshold();

    $html = array();
    foreach ($this->webDebug->getLogger()->getLogs() as $log)
    {
      if ('sfPropelLogger' != $log['type'])
      {
        continue;
      }

      // parse log message for details
      $details = array();
      $class = '';

      $parts = explode(' | ', $log['message']);
      foreach ($parts as $i => $part)
      {
        if (preg_match('/^(\w+):\s+(.*)/', $part, $match))
        {
          $details[] = $part;
          unset($parts[$i]);

          // check for slow query
          if ('time' == $match[1] && (float) $match[2] > $threshold)
          {
            $class = 'sfWebDebugWarning';
            if ($this->getStatus() > sfLogger::NOTICE)
            {
              $this->setStatus(sfLogger::NOTICE);
            }
          }
        }
      }
      $query = join(' | ', $parts);

      $html[] = sprintf('
        <li class="%s">
          <p class="sfWebDebugDatabaseQuery">%s</p>
          <div class="sfWebDebugDatabaseLogInfo">%s%s</div>
        </li>',
        $class,
        $this->formatSql(htmlspecialchars($query, ENT_QUOTES, sfConfig::get('sf_charset'))),
        implode(', ', $details),
        count($log['debug_backtrace']) ? '&nbsp;'.$this->getToggleableDebugStack($log['debug_backtrace']) : ''
      );
    }

    return $html;
  }

  /**
   * Returns the slow query threshold.
   * 
   * @return integer|null
   */
  protected function getSlowQueryThreshold()
  {
    return Propel::getConfiguration(PropelConfiguration::TYPE_OBJECT)->getParameter('debugpdo.logging.details.slow.threshold');
  }
}
