<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebug creates debug information for easy debugging in the browser.
 *
 * @package    symfony
 * @subpackage debug
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWebDebug
{
  protected
    $dispatcher = null,
    $logger     = null,
    $panels     = array();

  public function __construct(sfEventDispatcher $dispatcher, sfVarLogger $logger)
  {
    $this->dispatcher = $dispatcher;
    $this->logger = $logger;

    $this->configure();

    $this->dispatcher->notify(new sfEvent($this, 'debug.web.load_panels'));
  }

  public function configure()
  {
    $this->setPanel('symfony_version', new sfWebDebugPanelSymfonyVersion($this));
    $this->setPanel('cache', new sfWebDebugPanelCache($this));
    $this->setPanel('config', new sfWebDebugPanelConfig($this));
    $this->setPanel('logs', new sfWebDebugPanelLogs($this));
    $this->setPanel('time', new sfWebDebugPanelTimer($this));
    $this->setPanel('memory', new sfWebDebugPanelMemory($this));
    $this->setPanel('db', new sfWebDebugPanelPropel($this));
  }

  public function getLogger()
  {
    return $this->logger;
  }

  public function getEventDispatcher()
  {
    return $this->dispatcher;
  }

  public function getPanels()
  {
    return $this->panels;
  }

  public function setPanel($name, sfWebDebugPanel $panel)
  {
    $this->panels[$name] = $panel;
  }

  public function removePanel($name)
  {
    unset($this->panels[$name]);
  }

  /**
   * Returns the web debug toolbar as HTML.
   *
   * @return string The web debug toolbar HTML
   */
  public function asHtml()
  {
    $this->loadHelpers();

    $links  = array();
    $panels = array();
    foreach ($this->panels as $name => $panel)
    {
      if ($link = $panel->getLinkText())
      {
        if ($content = $panel->getPanelContent() || $panel->getLinkUrl())
        {
          $id = sprintf('sfWebDebug%sDetails', $name);
          $links[]  = sprintf('<li><a title="%s" alt="%s" href="%s"%s>%s</a></li>',
            $panel->getTitle(),
            $panel->getTitle(),
            $panel->getLinkUrl() ? $panel->getLinkUrl() : '#',
            $panel->getLinkUrl() ? '' : ' onclick="sfWebDebugShowDetailsFor(\''.$id.'\'); return false;"',
            $link
          );
          $panels[] = sprintf('<div id="%s" class="sfWebDebugTop" style="display: none"><h1>%s</h1>%s</div>',
            $id,
            $panel->getTitle(),
            $panel->getPanelContent()
          );
        }
        else
        {
          $links[] = sprintf('<li>%s</li>', $link);
        }
      }
    }

    return '
      <div id="sfWebDebug">
        <div id="sfWebDebugBar" class="sfWebDebug'.ucfirst($this->getPriority($this->logger->getHighestPriority())).'">
          <a href="#" onclick="sfWebDebugToggleMenu(); return false;">'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/sf.png').'</a>

          <ul id="sfWebDebugDetails" class="menu">
            '.implode("\n", $links).'
          </ul>
          <a href="#" onclick="document.getElementById(\'sfWebDebug\').style.display=\'none\'; return false;">'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/close.png').'</a>
        </div>

        '.implode("\n", $panels).'
      </div>
    ';
  }

  /**
   * Loads helpers needed for the web debug toolbar.
   */
  protected function loadHelpers()
  {
    sfLoader::loadHelpers(array('Helper', 'Url', 'Asset', 'Tag'));
  }

  /**
   * Converts a priority value to a string.
   *
   * @param integer $value The priority value
   *
   * @return string The priority as a string
   */
  public function getPriority($value)
  {
    if ($value >= sfLogger::INFO)
    {
      return 'info';
    }
    else if ($value >= sfLogger::WARNING)
    {
      return 'warning';
    }
    else
    {
      return 'error';
    }
  }
}
