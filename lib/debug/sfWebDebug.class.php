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
  private
    $log             = array(),
    $short_log       = array(),
    $max_priority    = 1000,
    $types           = array(),
    $last_time_log   = -1,
    $base_image_path = '/sf/images/sf_web_debug';

  private static
    $instance        = null;

  protected
    $context         = null;

  public function initialize()
  {
  }

  /**
   * Retrieve the singleton instance of this class.
   *
   * @return sfWebDebug A sfWebDebug implementation instance.
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
   * Removes current sfWebDebug instance
   *
   * This method only exists for testing purpose. Don't use it in your application code.
   */
  public static function removeInstance()
  {
    self::$instance = null;
  }

  public function registerAssets()
  {
    if (!$this->context)
    {
      $this->context = sfContext::getInstance();
    }

    // register our css and js
    $this->context->getResponse()->addJavascript('/sf/js/sf_web_debug/main');
    $this->context->getResponse()->addStylesheet('/sf/css/sf_web_debug/main');
  }

  public function logShortMessage($message)
  {
    $this->short_log[] = $message;
  }

  public function log($logEntry)
  {
    // elapsed time
    if ($this->last_time_log == -1)
    {
      $this->last_time_log = sfConfig::get('sf_timer_start');
    }

    $logEntry->setElapsedTime(sprintf('%.0f', (microtime(true) - $this->last_time_log) * 1000));
    $this->last_time_log = microtime(true);

    // update max priority
    if ($logEntry->getPriority() < $this->max_priority)
    {
      $this->max_priority = $logEntry->getPriority();
    }

    // update types
    if (!isset($this->types[$logEntry->getType()]))
    {
      $this->types[$logEntry->getType()] = 1;
    }
    else
    {
      ++$this->types[$logEntry->getType()];
    }

    $this->log[] = $logEntry;
  }

  private function loadHelpers()
  {
    // require needed helpers
    foreach (array('Helper', 'Url', 'Asset', 'Tag', 'Javascript') as $helperName)
    {
      include_once(sfConfig::get('sf_symfony_lib_dir').'/helper/'.$helperName.'Helper.php');
    }
  }

  private function formatLogLine($type, $log_line)
  {
    static $constants;
    if (!$constants) {
      foreach (array('sf_app_dir', 'sf_root_dir', 'sf_symfony_lib_dir', 'sf_symfony_data_dir') as $constant)
      {
        $constants[realpath(sfConfig::get($constant)).DIRECTORY_SEPARATOR] = $constant.DIRECTORY_SEPARATOR;
      }
    }

    // escape HTML
    $log_line = htmlentities($log_line);

    // replace constants value with constant name
    $log_line = strtr($log_line, $constants);

    $log_line = sfToolkit::pregtr($log_line, array('/&quot;(.+?)&quot;/s' => '"<span class="sfWebDebugLogInfo">\\1</span>"',
                                                   '/^(.+?)\(\)\:/S'      => '<span class="sfWebDebugLogInfo">\\1()</span>:',
                                                   '/line (\d+)$/'        => 'line <span class="sfWebDebugLogInfo">\\1</span>'));

    // special formatting for creole/SQL lines
    if (strtolower($type) == 'creole')
    {
      $log_line = preg_replace('/\b(SELECT|FROM|AS|LIMIT|ASC|COUNT|DESC|WHERE|LEFT JOIN|INNER JOIN|RIGHT JOIN|ORDER BY|GROUP BY|IN|LIKE|DISTINCT)\b/', '<span class="sfWebDebugLogInfo">\\1</span>', $log_line);

      // remove username/password from DSN
      if (strpos($log_line, 'DSN') !== false)
      {
        $log_line = preg_replace("/=&gt;\s+'?[^'\s,]+'?/", "=&gt; '****'", $log_line);
      }
    }

    return $log_line;
  }

  public function getResults()
  {
    if (!sfConfig::get('sf_web_debug'))
    {
      return '';
    }

    $this->loadHelpers();

    $result = '';

    // max priority
    $max_priority = '';
    if ($sf_logging_active = sfConfig::get('sf_logging_active'))
    {
      $max_priority = $this->getPriority($this->max_priority);
    }

    $logs = '';
    $sql_logs = array();
    if ($sf_logging_active)
    {
      $logs = '<table id="sfWebDebugLogs">
        <tr>
          <th>#</th>
          <th>&nbsp;</th>
          <th>ms</th>
          <th>type</th>
          <th>message</th>
        </tr>'."\n";
      $line_nb = 0;
      foreach($this->log as $logEntry)
      {
        $log = $logEntry->getMessage();

        $priority = $this->getPriority($logEntry->getPriority());

        if (strpos($type = $logEntry->getType(), 'sf') === 0)
        {
          $type = substr($type, 2);
        }

        // xdebug information
        $debug_info = '';
        if ($logEntry->getDebugStack())
        {
          $debug_info .= '&nbsp;<a href="#" onclick="sfWebDebugToggle(\'debug_'.$line_nb.'\'); return false;">'.image_tag($this->base_image_path.'/toggle.gif').'</a><div class="sfWebDebugDebugInfo" id="debug_'.$line_nb.'" style="display:none">';
          foreach ($logEntry->getDebugStack() as $i => $log_line)
          {
            $debug_info .= '#'.$i.' &raquo; '.$this->formatLogLine($type, $log_line).'<br/>';
          }
          $debug_info .= "</div>\n";
        }

        // format log
        $log = $this->formatLogLine($type, $log);

        // sql queries log
        if (preg_match('/executeQuery.+?\:\s+(.+)$/', $log, $match))
        {
          $sql_logs[] .= $match[1]."\n";
        }

        ++$line_nb;
        $logs .= sprintf("<tr class='sfWebDebugLogLine sfWebDebug%s %s'><td>%s</td><td>%s</td><td>+%s&nbsp;</td><td><span class=\"sfWebDebugLogType\">%s</span></td><td>%s%s</td></tr>\n", ucfirst($priority), $logEntry->getType(), $line_nb, image_tag($this->base_image_path.'/'.$priority.'.png'), $logEntry->getElapsedTime(), $type, $log, $debug_info);
      }
      $logs .= '</table>';

      ksort($this->types);
      $types = array();
      foreach ($this->types as $type => $nb)
      {
        $types[] = '<a id="sfWebDebug'.$type.'" href="#" onclick="sfWebDebugToggleMessages(\''.$type.'\'); return false;">'.$type.'</a>';
      }
    }

    // ignore cache link
    $cacheLink = '';
    if (sfConfig::get('sf_debug') && sfConfig::get('sf_cache'))
    {
      $self_url = $_SERVER['PHP_SELF'].((strpos($_SERVER['PHP_SELF'], 'sf_ignore_cache') === false) ? '?sf_ignore_cache=1' : '');
      $cacheLink = '<a href="'.$self_url.'" title="reload and ignore cache"><img src="'.$this->base_image_path.'/reload.png" /></a>';
    }

    // logging information
    $logLink = '';
    if (sfConfig::get('sf_logging_active'))
    {
      $logLink = '<li><a href="#" onclick="document.getElementById(\'sfWebDebugConfig\').style.display=\'none\';document.getElementById(\'sfWebDebugDatabaseDetails\').style.display=\'none\';sfWebDebugToggle(\'sfWebDebugLog\'); return false;"><img src="'.$this->base_image_path.'/comment.png" /> logs &amp; msgs</a></li>';
    }

    // database information
    $dbInfo = '';
    $dbInfoDetails = '';
    if (null !== ($nb = $this->getDatabaseRequestNumber()))
    {
      $dbInfo = '<li><a href="#" onclick="document.getElementById(\'sfWebDebugConfig\').style.display=\'none\';document.getElementById(\'sfWebDebugLog\').style.display=\'none\';sfWebDebugToggle(\'sfWebDebugDatabaseDetails\'); return false;"><img src="'.$this->base_image_path.'/database.png" /> '.$nb.'</a></li>';

      $dbInfoDetails = '
        <div id="sfWebDebugDatabaseDetails">
        <ol><li>'.implode('</li><li>', $sql_logs).'</li></ol>
        </div>
      ';
    }

    // memory used
    $memoryInfo = '';
    if (sfConfig::get('sf_debug') && function_exists('memory_get_usage'))
    {
      $total_memory = sprintf('%.1f', (memory_get_usage() / 1024));
      $memoryInfo = '<li><img src="'.$this->base_image_path.'/memory.png" /> '.$total_memory.' KB</li>';
    }

    // total time elapsed
    $timeInfo = '';
    if (sfConfig::get('sf_debug'))
    {
      $total_time = (microtime(true) - sfConfig::get('sf_timer_start')) * 1000;
      $total_time = sprintf(($total_time <= 1) ? '%.2f' : '%.0f', $total_time);
      $timeInfo = '<li class="last"><img src="'.$this->base_image_path.'/time.png" /> '.$total_time.' ms</li>';
    }

    // short log messages
    $short_messages = '';
    if ($this->short_log)
    {
      $short_messages = '<ul id="sfWebDebugShortMessages"><li>&raquo;&nbsp;'.implode('</li><li>&raquo&nbsp;', $this->short_log).'</li></ul>';
    }

    // logs
    $logInfo = '';
    if ($sf_logging_active)
    {
      $logInfo = $short_messages.'
        <ul id="sfWebDebugLogMenu">
          <li><a href="#" onclick="sfWebDebugToggleAllLogLines(true, \'sfWebDebugLogLine\'); return false;">[all]</a></li>
          <li><a href="#" onclick="sfWebDebugToggleAllLogLines(false, \'sfWebDebugLogLine\'); return false;">[none]</a></li>
          <li><a href="#" onclick="sfWebDebugShowOnlyLogLines(\'info\'); return false;"><img src="'.$this->base_image_path.'/info.png" /></a></li>
          <li><a href="#" onclick="sfWebDebugShowOnlyLogLines(\'warning\'); return false;"><img src="'.$this->base_image_path.'/warning.png" /></a></li>
          <li><a href="#" onclick="sfWebDebugShowOnlyLogLines(\'error\'); return false;"><img src="'.$this->base_image_path.'/error.png" /></a></li>
          <li>'.implode("</li>\n<li>", $types).'</li>
        </ul>
        <div id="sfWebDebugLog">'.$logs.'</div>
      ';
    }

    $result .= '
    <div id="sfWebDebug">
      <div id="sfWebDebugBar" class="sfWebDebug'.ucfirst($max_priority).'">
        <a href="#" onclick="sfWebDebugToggleMenu(); return false;"><img src="'.$this->base_image_path.'/sf.png" /></a>
        <ul id="sfWebDebugDetails" class="menu">
          <li><a href="#" onclick="document.getElementById(\'sfWebDebugLog\').style.display=\'none\';document.getElementById(\'sfWebDebugDatabaseDetails\').style.display=\'none\';sfWebDebugToggle(\'sfWebDebugConfig\'); return false;"><img src="'.$this->base_image_path.'/config.png" /> vars &amp; config</a></li>
          '.$cacheLink.'
          '.$logLink.'
          '.$dbInfo.'
          '.$memoryInfo.'
          '.$timeInfo.'
        </ul>
        <a href="#" onclick="document.getElementById(\'sfWebDebug\').style.display=\'none\'; return false;"><img src="'.$this->base_image_path.'/close.png" /></a>
      </div>

      <div id="sfWebDebugLog" class="top" style="display: none">
      <h1>Log and debug messages</h1>
      '.$logInfo.'
      </div>

      <div id="sfWebDebugConfig" class="top" style="display: none">
      <h1>Configuration and request variables</h1>
      '.$this->getCurrentConfigAsHtml().'
      </div>

      <div id="sfWebDebugDatabaseDetails" class="top" style="display: none">
      <h1>SQL queries</h1>
      '.$dbInfoDetails
      .'
      </div>
    </div>
    ';

    return $result;
  }

  private function getCurrentConfigAsHtml()
  {
    $config = array(
      'debug'        => sfConfig::get('sf_debug')             ? 'on' : 'off',
      'xdebug'       => (extension_loaded('xdebug'))          ? 'on' : 'off',
      'logging'      => sfConfig::get('sf_logging_active')    ? 'on' : 'off',
      'cache'        => sfConfig::get('sf_cache')             ? 'on' : 'off',
      'eaccelerator' => (extension_loaded('eaccelerator') && ini_get('eaccelerator.enable')) ? 'on' : 'off',
      'apc'          => (extension_loaded('apc') && ini_get('apc.enabled')) ? 'on' : 'off',
      'compression'  => sfConfig::get('sf_compressed')        ? 'on' : 'off',
      'tidy'         => (extension_loaded('tidy'))            ? 'on' : 'off',
      'syck'         => (extension_loaded('syck'))            ? 'on' : 'off',
    );

    $result = '<ul id="sfWebDebugConfigSummary">';
    foreach ($config as $key => $value)
    {
      $result .= '<li class="is'.$value.''.($key == 'syck' ? ' last' : '').'">'.$key.'</li>';
    }
    $result .= '</ul>';

    $context = sfContext::getInstance();
    $result .= $this->formatArrayAsHtml('request',  sfDebug::requestAsArray($context->getRequest()));
    $result .= $this->formatArrayAsHtml('response', sfDebug::responseAsArray($context->getResponse()));
    $result .= $this->formatArrayAsHtml('settings', sfDebug::settingsAsArray());
    $result .= $this->formatArrayAsHtml('globals',  sfDebug::globalsAsArray());
    $result .= $this->formatArrayAsHtml('php',      sfDebug::phpInfoAsArray());

    return $result;
  }

  private function formatArrayAsHtml($id, $values)
  {
    $id = ucfirst(strtolower($id));
    $content = '
    <h2>'.$id.' <a href="#" onclick="sfWebDebugToggle(\'sfWebDebug'.$id.'\'); return false;"><img src="'.$this->base_image_path.'/toggle.gif" /></a></h2>
    <div id="sfWebDebug'.$id.'" style="display: none"><pre>'.@sfYaml::Dump($values).'</pre></div>
    ';

    return $content;
  }

  public function getDatabaseRequestNumber()
  {
    if (sfConfig::get('sf_debug'))
    {
      // get Propel statistics if available (user created a model and a db)
      // we require Propel here to avoid autoloading and automatic connection
      require_once('propel/Propel.php');
      if (Propel::isInit())
      {
        try
        {
          $con = Propel::getConnection();
          if (method_exists($con, 'getNumQueriesExecuted'))
          {
            return $con->getNumQueriesExecuted();
          }
        }
        catch (Exception $e)
        {
        }
      }
    }

    return null;
  }

  public function decorateContentWithDebug($internalUri, $suffix, $retval, $new = false)
  {
    if (!sfConfig::get('sf_web_debug'))
    {
      return $retval;
    }

    $border_color = $new ? '#f00' : '#f00';
    $bg_color     = $new ? '#9ff' : '#ff9';

    $cache = $this->context->getViewCacheManager();
    $this->loadHelpers();

    $last_modified = $cache->lastModified($internalUri, $suffix);
    $id            = md5($internalUri);
    $retval = '
      <div id="main_'.$id.'" class="sfWebDebugActionCache" style="border: 1px solid '.$border_color.'">
      <div id="sub_main_'.$id.'" class="sfWebDebugCache" style="background-color: '.$bg_color.'; border-right: 1px solid '.$border_color.'; border-bottom: 1px solid '.$border_color.';">
      <div style="height: 16px; padding: 2px"><a href="#" onclick="sfWebDebugToggle(\''.$id.'\'); return false;"><strong>cache information</strong></a>&nbsp;<a href="#" onclick="Element.hide(\'sub_main_'.$id.'\'); document.getElementById(\'main_'.$id.'\').style.border = \'none\'; return false;">'.image_tag($this->base_image_path.'/close.png').'</a>&nbsp;</div>
        <div style="padding: 2px; display: none" id="'.$id.'">
        [uri]&nbsp;'.$internalUri.'<br />
        [life&nbsp;time]&nbsp;'.$cache->getLifeTime($internalUri, $suffix).'&nbsp;seconds<br />
        [last&nbsp;modified]&nbsp;'.(time() - $last_modified).'&nbsp;seconds<br />
        &nbsp;<br />&nbsp;
        </div>
      </div><div>
      '.$retval.'
      </div></div>
    ';

    return $retval;
  }

  private function getPriority($value)
  {
    if ($value >= 6)
    {
      return 'info';
    }
    else if ($value >= 4)
    {
      return 'warning';
    }
    else
    {
      return 'error';
    }
  }
}
