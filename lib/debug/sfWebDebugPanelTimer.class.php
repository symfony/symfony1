<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelTimer adds a panel to the web debug toolbar with timer information.
 *
 * @package    symfony
 * @subpackage debug
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWebDebugPanelTimer extends sfWebDebugPanel
{
  public function getLinkText()
  {
    return '<img src="'.$this->webDebug->getOption('image_root_path').'/time.png" /> '.$this->getTotalTime().' ms';
  }

  public function getPanelContent()
  {
    $panel = '<table class="sfWebDebugLogs" style="width: 300px"><tr><th>type</th><th>calls</th><th>time (ms)</th><th>time (%)</th></tr>';
    foreach (sfTimerManager::getTimers() as $name => $timer)
    {
      $panel .= sprintf('<tr><td class="sfWebDebugLogType">%s</td><td class="sfWebDebugLogNumber" style="text-align: right">%d</td><td style="text-align: right">%.2f</td><td style="text-align: right">%d</td></tr>', $name, $timer->getCalls(), $timer->getElapsedTime() * 1000, $timer->getElapsedTime() * 1000 * 100 / $this->getTotalTime());
    }
    $panel .= '</table>';

    return $panel;
  }

  public function getTitle()
  {
    return 'Timers';
  }

  protected function getTotalTime()
  {
    return sfConfig::get('sf_debug') ? sprintf('%.0f', (microtime(true) - sfConfig::get('sf_timer_start')) * 1000) : 0;;
  }
}
