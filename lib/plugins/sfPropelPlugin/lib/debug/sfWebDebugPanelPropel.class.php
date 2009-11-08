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
        <h3>Propel Version: '.Propel::VERSION.'</h3>
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
    $config    = $this->getPropelConfiguration();
    $outerGlue = $config->getParameter('debugpdo.logging.outerglue', ' | ');
    $innerGlue = $config->getParameter('debugpdo.logging.innerglue', ': ');
    $flagSlow  = $config->getParameter('debugpdo.logging.details.slow.enabled', false);
    $threshold = $config->getParameter('debugpdo.logging.details.slow.threshold', DebugPDO::DEFAULT_SLOW_THRESHOLD);

    $html = array();
    foreach ($this->webDebug->getLogger()->getLogs() as $log)
    {
      if ('sfPropelLogger' != $log['type'])
      {
        continue;
      }

      $details = explode($outerGlue, $log['message']);
      // query is last element of the details
      $query = array_pop($details);

      $slowQuery = false;
      foreach ($details as $detail)
      {
        list($key, $value) = explode($innerGlue, $detail);

        // check for slow query
        if ('time' == $key)
        {
          if ($flagSlow && (float) $value > $threshold)
          {
            $slowQuery = true;
            if ($this->getStatus() > sfLogger::NOTICE)
            {
              $this->setStatus(sfLogger::NOTICE);
            }
          }
        }
      }

      $query = $this->formatSql(htmlspecialchars($query, ENT_QUOTES, sfConfig::get('sf_charset')));
      $backtrace = isset($log['debug_backtrace']) ? '&nbsp;'.$this->getToggleableDebugStack($log['debug_backtrace']) : '';

      $html[] = sprintf('
        <li class="%s">
          <p class="sfWebDebugDatabaseQuery">%s</p>
          <div class="sfWebDebugDatabaseLogInfo">%s%s</div>
        </li>',
        $slowQuery ? 'sfWebDebugWarning' : '',
        $query,
        implode(', ', $details),
        $backtrace
      );
    }

    return $html;
  }

  /**
   * Returns the current PropelConfiguration.
   *
   * @return PropelConfiguration
   */
  protected function getPropelConfiguration()
  {
    return Propel::getConfiguration(PropelConfiguration::TYPE_OBJECT);
  }
}
