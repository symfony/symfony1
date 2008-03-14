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
    $log         = array(),
    $maxPriority = 1000,
    $types       = array(),
    $lastTimeLog = -1;

  /**
   * Logs a message to the web debug toolbar.
   *
   * @param array An array of parameter
   *
   * @see sfWebDebugLogger
   */
  public function log($logEntry)
  {
    // elapsed time
    if ($this->lastTimeLog == -1)
    {
      $this->lastTimeLog = sfConfig::get('sf_timer_start');
    }

    $this->lastTimeLog = microtime(true);

    // update max priority
    if ($logEntry['priority'] < $this->maxPriority)
    {
      $this->maxPriority = $logEntry['priority'];
    }

    // update types
    if (!isset($this->types[$logEntry['type']]))
    {
      $this->types[$logEntry['type']] = 1;
    }
    else
    {
      ++$this->types[$logEntry['type']];
    }

    $this->log[] = $logEntry;
  }

  /**
   * Loads helpers needed for the web debug toolbar.
   */
  protected function loadHelpers()
  {
    sfLoader::loadHelpers(array('Helper', 'Url', 'Asset', 'Tag'));
  }

  /**
   * Formats a log line.
   *
   * @param string The log line to format
   *
   * @return string The formatted log lin
   */
  protected function formatLogLine($logLine)
  {
    static $constants;

    if (!$constants)
    {
      foreach (array('sf_app_dir', 'sf_root_dir', 'sf_symfony_lib_dir') as $constant)
      {
        $constants[realpath(sfConfig::get($constant)).DIRECTORY_SEPARATOR] = $constant.DIRECTORY_SEPARATOR;
      }
    }

    // escape HTML
    $logLine = htmlentities($logLine, ENT_QUOTES, sfConfig::get('sf_charset'));

    // replace constants value with constant name
    $logLine = str_replace(array_keys($constants), array_values($constants), $logLine);

    $logLine = sfToolkit::pregtr($logLine, array('/&quot;(.+?)&quot;/s' => '"<span class="sfWebDebugLogInfo">\\1</span>"',
                                                   '/^(.+?)\(\)\:/S'      => '<span class="sfWebDebugLogInfo">\\1()</span>:',
                                                   '/line (\d+)$/'        => 'line <span class="sfWebDebugLogInfo">\\1</span>'));

    // special formatting for SQL lines
    $logLine = preg_replace('/\b(SELECT|FROM|AS|LIMIT|ASC|COUNT|DESC|WHERE|LEFT JOIN|INNER JOIN|RIGHT JOIN|ORDER BY|GROUP BY|IN|LIKE|DISTINCT|DELETE|INSERT|INTO|VALUES)\b/', '<span class="sfWebDebugLogInfo">\\1</span>', $logLine);

    // remove username/password from DSN
    if (strpos($logLine, 'DSN') !== false)
    {
      $logLine = preg_replace("/=&gt;\s+'?[^'\s,]+'?/", "=&gt; '****'", $logLine);
    }

    return $logLine;
  }

  /**
   * Returns the web debug toolbar as HTML.
   *
   * @return string The web debug toolbar HTML
   */
  public function getResults()
  {
    if (!sfConfig::get('sf_web_debug'))
    {
      return '';
    }

    $this->loadHelpers();

    $result = '';

    // max priority
    $maxPriority = '';
    if (sfConfig::get('sf_logging_enabled'))
    {
      $maxPriority = $this->getPriority($this->maxPriority);
    }

    $logs = '';
    $sqlLogs = array();
    if (sfConfig::get('sf_logging_enabled'))
    {
      $logs = '<table class="sfWebDebugLogs">
        <tr>
          <th>#</th>
          <th>type</th>
          <th>message</th>
        </tr>'."\n";
      $line_nb = 0;
      foreach ($this->log as $logEntry)
      {
        $log = $logEntry['message'];

        $priority = $this->getPriority($logEntry['priority']);

        if (strpos($type = $logEntry['type'], 'sf') === 0)
        {
          $type = substr($type, 2);
        }

        // xdebug information
        $debug_info = '';
        if ($logEntry['debugStack'])
        {
          $debug_info .= '&nbsp;<a href="#" onclick="sfWebDebugToggle(\'debug_'.$line_nb.'\'); return false;">'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/toggle.gif').'</a><div class="sfWebDebugDebugInfo" id="debug_'.$line_nb.'" style="display:none">';
          foreach ($logEntry['debugStack'] as $i => $logLine)
          {
            $debug_info .= '#'.$i.' &raquo; '.$this->formatLogLine($logLine).'<br/>';
          }
          $debug_info .= "</div>\n";
        }

        // format log
        $log = $this->formatLogLine($log);

        // sql queries log
        if (preg_match('/execute(?:Query|Update).+?\:\s+(.+)$/', $log, $match))
        {
          $sqlLogs[] .= $match[1];
        }

        ++$line_nb;
        $logs .= sprintf("<tr class='sfWebDebugLogLine sfWebDebug%s %s'><td class=\"sfWebDebugLogNumber\">%s</td><td class=\"sfWebDebugLogType\">%s&nbsp;%s</td><td>%s%s</td></tr>\n",
          ucfirst($priority),
          $logEntry['type'],
          $line_nb,
          image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/'.$priority.'.png'),
          $type,
          $log,
          $debug_info
        );
      }
      $logs .= '</table>';

      ksort($this->types);
      $types = array();
      foreach ($this->types as $type => $nb)
      {
        $types[] = '<a href="#" onclick="sfWebDebugToggleMessages(\''.$type.'\'); return false;">'.$type.'</a>';
      }
    }

    // ignore cache link
    $cacheLink = '';
    if (sfConfig::get('sf_debug') && sfConfig::get('sf_cache'))
    {
      $selfUrl = $_SERVER['PHP_SELF'].((strpos($_SERVER['PHP_SELF'], '_sf_ignore_cache') === false) ? '?_sf_ignore_cache=1' : '');
      $cacheLink = '<li><a href="'.$selfUrl.'" title="reload and ignore cache">'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/reload.png').'</a></li>';
    }

    // logging information
    $logLink = '';
    if (sfConfig::get('sf_logging_enabled'))
    {
      $logLink = '<li><a href="#" onclick="sfWebDebugShowDetailsFor(\'sfWebDebugLog\'); return false;">'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/comment.png').' logs &amp; msgs</a></li>';
    }

    // database information
    $dbInfo = '';
    $dbInfoDetails = '';
    if ($sqlLogs)
    {
      $dbInfo = '<li><a href="#" onclick="sfWebDebugShowDetailsFor(\'sfWebDebugDatabaseDetails\'); return false;">'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/database.png').' '.count($sqlLogs).'</a></li>';

      $dbInfoDetails = '
        <div id="sfWebDebugDatabaseLogs">
        <ol><li>'.implode("</li>\n<li>", $sqlLogs).'</li></ol>
        </div>
      ';
    }

    // memory used
    $memoryInfo = '';
    if (sfConfig::get('sf_debug') && function_exists('memory_get_usage'))
    {
      $totalMemory = sprintf('%.1f', (memory_get_usage() / 1024));
      $memoryInfo = '<li>'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/memory.png').' '.$totalMemory.' KB</li>';
    }

    // total time elapsed
    $timeInfo = '';
    if (sfConfig::get('sf_debug'))
    {
      $totalTime = (microtime(true) - sfConfig::get('sf_timer_start')) * 1000;
      $totalTime = sprintf(($totalTime <= 1) ? '%.2f' : '%.0f', $totalTime);
      $timeInfo = '<li class="last"><a href="#" onclick="sfWebDebugShowDetailsFor(\'sfWebDebugTimeDetails\'); return false;">'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/time.png').' '.$totalTime.' ms</a></li>';
    }

    // timers
    $timeInfoDetails = '<table class="sfWebDebugLogs" style="width: 300px"><tr><th>type</th><th>calls</th><th>time (ms)</th><th>time (%)</th></tr>';
    foreach (sfTimerManager::getTimers() as $name => $timer)
    {
      $timeInfoDetails .= sprintf('<tr><td class="sfWebDebugLogType">%s</td><td class="sfWebDebugLogNumber" style="text-align: right">%d</td><td style="text-align: right">%.2f</td><td style="text-align: right">%d</td></tr>', $name, $timer->getCalls(), $timer->getElapsedTime() * 1000, $timer->getElapsedTime() * 1000 * 100 / $totalTime );
    }
    $timeInfoDetails .= '</table>';

    // logs
    $logInfo = '';
    if (sfConfig::get('sf_logging_enabled'))
    {
      $logInfo .= '
        <ul id="sfWebDebugLogMenu">
          <li><a href="#" onclick="sfWebDebugToggleAllLogLines(true, \'sfWebDebugLogLine\'); return false;">[all]</a></li>
          <li><a href="#" onclick="sfWebDebugToggleAllLogLines(false, \'sfWebDebugLogLine\'); return false;">[none]</a></li>
          <li><a href="#" onclick="sfWebDebugShowOnlyLogLines(\'info\'); return false;">'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/info.png').'</a></li>
          <li><a href="#" onclick="sfWebDebugShowOnlyLogLines(\'warning\'); return false;">'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/warning.png').'</a></li>
          <li><a href="#" onclick="sfWebDebugShowOnlyLogLines(\'error\'); return false;">'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/error.png').'</a></li>
          <li>'.implode("</li>\n<li>", $types).'</li>
        </ul>
        <div id="sfWebDebugLogLines">'.$logs.'</div>
      ';
    }

    $result .= '
    <div id="sfWebDebug">
      <div id="sfWebDebugBar" class="sfWebDebug'.ucfirst($maxPriority).'">
        <a href="#" onclick="sfWebDebugToggleMenu(); return false;">'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/sf.png').'</a>
        <ul id="sfWebDebugDetails" class="menu">
          <li>'.SYMFONY_VERSION.'</li>
          <li><a href="#" onclick="sfWebDebugShowDetailsFor(\'sfWebDebugConfig\'); return false;">'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/config.png').' vars &amp; config</a></li>
          '.$cacheLink.'
          '.$logLink.'
          '.$dbInfo.'
          '.$memoryInfo.'
          '.$timeInfo.'
        </ul>
        <a href="#" onclick="document.getElementById(\'sfWebDebug\').style.display=\'none\'; return false;">'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/close.png').'</a>
      </div>

      <div id="sfWebDebugLog" class="sfWebDebugTop" style="display: none"><h1>Log and debug messages</h1>'.$logInfo.'</div>
      <div id="sfWebDebugConfig" class="sfWebDebugTop" style="display: none"><h1>Configuration and request variables</h1>'.$this->getCurrentConfigAsHtml().'</div>
      <div id="sfWebDebugDatabaseDetails" class="sfWebDebugTop" style="display: none"><h1>SQL queries</h1>'.$dbInfoDetails.'</div>
      <div id="sfWebDebugTimeDetails" class="sfWebDebugTop" style="display: none"><h1>Timers</h1>'.$timeInfoDetails.'</div>

      </div>
    ';

    return $result;
  }

  /**
   * Returns the current configuration as HTML.
   *
   * @return string The current configuration as HTML
   */
  protected function getCurrentConfigAsHtml()
  {
    $config = array(
      'debug'        => sfConfig::get('sf_debug')           ? 'on' : 'off',
      'xdebug'       => extension_loaded('xdebug')          ? 'on' : 'off',
      'logging'      => sfConfig::get('sf_logging_enabled') ? 'on' : 'off',
      'cache'        => sfConfig::get('sf_cache')           ? 'on' : 'off',
      'compression'  => sfConfig::get('sf_compressed')      ? 'on' : 'off',
      'syck'         => extension_loaded('syck')            ? 'on' : 'off',
      'eaccelerator' => extension_loaded('eaccelerator') && ini_get('eaccelerator.enable') ? 'on' : 'off',
      'apc'          => extension_loaded('apc') && ini_get('apc.enabled')                  ? 'on' : 'off',
      'xcache'       => extension_loaded('xcache') && ini_get('xcache.cacher')             ? 'on' : 'off',
    );

    $result = '<ul id="sfWebDebugConfigSummary">';
    foreach ($config as $key => $value)
    {
      $result .= '<li class="is'.$value.($key == 'xcache' ? ' last' : '').'">'.$key.'</li>';
    }
    $result .= '</ul>';

    $context = sfContext::getInstance();
    $result .= $this->formatArrayAsHtml('request',  sfDebug::requestAsArray($context->getRequest()));
    $result .= $this->formatArrayAsHtml('response', sfDebug::responseAsArray($context->getResponse()));
    $result .= $this->formatArrayAsHtml('settings', sfDebug::settingsAsArray());
    $result .= $this->formatArrayAsHtml('globals',  sfDebug::globalsAsArray());
    $result .= $this->formatArrayAsHtml('php',      sfDebug::phpInfoAsArray());
    $result .= $this->formatArrayAsHtml('symfony',  sfDebug::symfonyInfoAsArray());

    return $result;
  }

  /**
   * Converts an array to HTML.
   *
   * @param string The identifier to use
   * @param array  The array of values
   *
   * @return string An HTML string
   */
  protected function formatArrayAsHtml($id, $values)
  {
    $id = ucfirst(strtolower($id));
    $content = '
    <h2>'.$id.' <a href="#" onclick="sfWebDebugToggle(\'sfWebDebug'.$id.'\'); return false;">'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/toggle.gif').'</a></h2>
    <div id="sfWebDebug'.$id.'" style="display: none"><pre>'.htmlentities(@sfYaml::dump($values), ENT_QUOTES, sfConfig::get('sf_charset')).'</pre></div>
    ';

    return $content;
  }

  /**
   * Decorates a chunk of HTML with cache information.
   *
   * @param string  The internalUri representing the content
   * @param string  The HTML content
   * @param boolean true if the content is new in the cache, false otherwise
   *
   * @return string The decorated HTML string
   */
  static public function decorateContentWithDebug($internalUri, $content, $new = false)
  {
    $context = sfContext::getInstance();

    // don't decorate if not html or if content is null
    if (!sfConfig::get('sf_web_debug') || !$content || false === strpos($context->getResponse()->getContentType(), 'html'))
    {
      return $content;
    }

    $cache = $context->getViewCacheManager();
    sfLoader::loadHelpers(array('Helper', 'Url', 'Asset', 'Tag'));

    $bgColor      = $new ? '#9ff' : '#ff9';
    $lastModified = $cache->getLastModified($internalUri);
    $id           = md5($internalUri);

    return '
      <div id="main_'.$id.'" class="sfWebDebugActionCache" style="border: 1px solid #f00">
      <div id="sub_main_'.$id.'" class="sfWebDebugCache" style="background-color: '.$bgColor.'; border-right: 1px solid #f00; border-bottom: 1px solid #f00;">
      <div style="height: 16px; padding: 2px"><a href="#" onclick="sfWebDebugToggle(\''.$id.'\'); return false;"><strong>cache information</strong></a>&nbsp;<a href="#" onclick="sfWebDebugToggle(\'sub_main_'.$id.'\'); document.getElementById(\'main_'.$id.'\').style.border = \'none\'; return false;">'.image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/close.png').'</a>&nbsp;</div>
        <div style="padding: 2px; display: none" id="sub_main_info_'.$id.'">
        [uri]&nbsp;'.htmlentities($internalUri, ENT_QUOTES, sfConfig::get('sf_charset')).'<br />
        [life&nbsp;time]&nbsp;'.$cache->getLifeTime($internalUri).'&nbsp;seconds<br />
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
   * @param integer The priority value
   *
   * @return string The priority as a string
   */
  protected function getPriority($value)
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
