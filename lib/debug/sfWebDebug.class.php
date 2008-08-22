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

    $this->dispatcher->connect('view.cache.filter_content', array($this, 'decorateContentWithDebug'));

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
   * Listens to the 'view.cache.filter_content' event to decorate a chunk of HTML with cache information.
   *
   * @param sfEvent $event   A sfEvent instance
   * @param string  $content The HTML content
   *
   * @return string The decorated HTML string
   */
  public function decorateContentWithDebug(sfEvent $event, $content)
  {
    // don't decorate if not html or if content is null
    if (!sfConfig::get('sf_web_debug') || !$content || false === strpos($event['response']->getContentType(), 'html'))
    {
      return $content;
    }

    $viewCacheManager = $event->getSubject();
    sfLoader::loadHelpers(array('Helper', 'Url', 'Asset', 'Tag'));

    $bgColor      = $event['new'] ? '#9ff' : '#ff9';
    $lastModified = $viewCacheManager->getLastModified($event['uri']);
    $id           = md5($event['uri']);

    return '
      <div id="main_'.$id.'" class="sfWebDebugActionCache" style="border: 1px solid #f00">
      <div id="sub_main_'.$id.'" class="sfWebDebugCache" style="background-color: '.$bgColor.'; border-right: 1px solid #f00; border-bottom: 1px solid #f00;">
      <div style="height: 16px; padding: 2px"><a href="#" onclick="sfWebDebugToggle(\'sub_main_info_'.$id.'\'); return false;"><strong>cache information</strong></a>&nbsp;<a href="#" onclick="sfWebDebugToggle(\'sub_main_'.$id.'\'); document.getElementById(\'main_'.$id.'\').style.border = \'none\'; return false;">'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/close.png').'</a>&nbsp;</div>
        <div style="padding: 2px; display: none" id="sub_main_info_'.$id.'">
        [uri]&nbsp;'.htmlspecialchars($event['uri'], ENT_QUOTES, sfConfig::get('sf_charset')).'<br />
        [life&nbsp;time]&nbsp;'.$viewCacheManager->getLifeTime($event['uri']).'&nbsp;seconds<br />
        [last&nbsp;modified]&nbsp;'.(time() - $lastModified).'&nbsp;seconds<br />
        &nbsp;<br />&nbsp;
        </div>
      </div><div>
      '.$content.'
      </div></div>
    ';
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
