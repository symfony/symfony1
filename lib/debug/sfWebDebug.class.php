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
    $base_image_path = '/sf/images/sf_debug_stats';

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
    $this->context->getResponse()->addJavascript('/sf/js/prototype/prototype');
    $this->context->getResponse()->addStylesheet('/sf/css/sf_debug_stats/main');
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

    $log_line = sfToolkit::pregtr($log_line, array('/&quot;(.+?)&quot;/s' => '"<span class="sfStatsFileInfo">\\1</span>"',
                                                   '/^(.+?)\(\)\:/S'      => '<span class="sfStatsFileInfo">\\1()</span>:',
                                                   '/line (\d+)$/'        => 'line <span class="sfStatsFileInfo">\\1</span>'));

    // special formatting for creole/SQL lines
    if (strtolower($type) == 'creole')
    {
      $log_line = preg_replace('/\b(SELECT|FROM|AS|LIMIT|ASC|COUNT|DESC|WHERE|LEFT JOIN|INNER JOIN|RIGHT JOIN|ORDER BY|GROUP BY|IN|LIKE|DISTINCT)\b/', '<span class="sfStatsFileInfo">\\1</span>', $log_line);

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

    // total time elapsed
    $total_time = 0;
    if (sfConfig::get('sf_debug'))
    {
      $total_time = (microtime(true) - sfConfig::get('sf_timer_start')) * 1000;
      $total_time = sprintf(($total_time <= 1) ? '%.2f' : '%.0f', $total_time);
    }

    // memory used
    $total_memory = 0;
    if (sfConfig::get('sf_debug') && function_exists('memory_get_usage'))
    {
      $total_memory = sprintf('%.1f', (memory_get_usage() / 1024));
    }

    // max priority
    $log_image = '';
    if ($sf_logging_active = sfConfig::get('sf_logging_active'))
    {
      if ($this->max_priority >= 6)
      {
        $log_image = 'info';
      }
      else if ($this->max_priority >= 4)
      {
        $log_image = 'warning';
      }
      else
      {
        $log_image = 'error';
      }
    }

    // short messages
    $short_messages = '';
    if ($this->short_log)
    {
      $short_messages = '<div id="sfStatsShortMessages">&raquo;&nbsp;'.implode('<br />&raquo;&nbsp;', $this->short_log).'</div>';
    }

    $result .= '
      <div class="sfStats" id="sfStats'.ucfirst($log_image).'">
      '.$this->displayMenu($log_image).'
      <div id="sfStatsDetails">'.$this->displayCurrentConfig().'</div>
      <div id="sfStatsTime">processed in <strong>'.$total_time.'</strong> ms</div>
      '.($total_memory ? '<div id="sfStatsMemory">memory: <strong>'.$total_memory.'</strong> KB</div>' : '').'
      '.$short_messages.'
      </div>
    ';

    if ($sf_logging_active)
    {
      $logs  = '<table id="sfStatsLogs">';
      $logs .= "<tr>
        <th>#</th>
        <th>&nbsp;</th>
        <th>ms</th>
        <th>type</th>
        <th>message</th>
      </tr>\n";
      $line_nb = 0;
      foreach($this->log as $logEntry)
      {
        $log = $logEntry->getMessage();

        if ($logEntry->getPriority() >= 6)
        {
          $class = 'Green';
          $priority = 'info';
        }
        else if ($logEntry->getPriority() >= 4)
        {
          $class = 'Orange';
          $priority = 'warning';
        }
        else
        {
          $class = 'Red';
          $priority = 'error';
        }

        if (strpos($type = $logEntry->getType(), 'sf') === 0) {
            $type = substr($type, 2);
        }

        // xdebug information
        $debug_info = '';
        if ($logEntry->getDebugStack())
        {
          $debug_info .= '&nbsp;<a href="#" onclick="Element.toggle(\'debug_'.$line_nb.'\'); return false;">'.image_tag($this->base_image_path.'/toggle.gif').'</a><div class="sfStatsDebugInfo" id="debug_'.$line_nb.'" style="display:none">';
          foreach ($logEntry->getDebugStack() as $i => $log_line)
          {
            $debug_info .= '#'.$i.' &raquo; '.$this->formatLogLine($type, $log_line).'<br/>';
          }
          $debug_info .= "</div>\n";
        }

        // format log
        $log = $this->formatLogLine($type, $log);

        ++$line_nb;
        $logs .= sprintf("<tr class='sfStats%s %s'><td>%s</td><td>%s</td><td>+%s&nbsp;</td><td><span class=\"sfStatsLogType\">%s</span></td><td>%s%s</td></tr>\n", $class, $logEntry->getType(), $line_nb, image_tag($this->base_image_path.'/'.$priority.'.png', 'align=middle'), $logEntry->getElapsedTime(), $type, $log, $debug_info);
      }
      $logs .= '</table>';

      $result .= javascript_tag('
      function toggleMessages(myclass)
      {
        elements = document.getElementsByClassName(myclass);
        for (i = 0, x = elements.length; i < x; ++i)
        {
          Element.toggle(elements[i]);
        }
      }
      ');

      ksort($this->types);
      $types = array();
      foreach ($this->types as $type => $nb)
      {
        $types[] = '<a id="'.$type.'" href="#" onclick="toggleMessages(\''.$type.'\'); return false;">'.$type."</a>\n";
      }

      $result .= '
      <div id="sfStatsLogMain" style="display: none">
        <div id="sfStatsLogMenu">
          <div class="float">'.
          implode('&nbsp;-&nbsp;', $types).'&nbsp;&nbsp;
          <a href="#" onclick="toggleMessages(\'sfStatsGreen\')">'.image_tag($this->base_image_path.'/info.png', 'align=middle').'</a>&nbsp;
          <a href="#" onclick="Element.hide(\'sfStatsLogMain\')">'.image_tag($this->base_image_path.'/close.png', 'align=middle').'</a>
          </div>
          <strong>Log messages</strong>
        </div>
        <div id="sfStatsLog">'.$logs.'</div>
      </div>
      ';
    }

    return '<div id="sfStatsBase">'.$result.'</div>';
  }

  private function displayMenu($log_image)
  {
    $result = '<div id="sfStatsRightMenu">';

    if (sfConfig::get('sf_logging_active'))
    {
      $result .= '<a href="#" onclick="Element.show(\'sfStatsLogMain\'); return false;">'.image_tag($this->base_image_path.'/'.$log_image.'.png', 'align=middle').'</a>&nbsp;';
    }

    if (sfConfig::get('sf_debug') && sfConfig::get('sf_cache'))
    {
      $self_url = $_SERVER['PHP_SELF'].((strpos($_SERVER['PHP_SELF'], 'ignore_cache') !== false) ? '?ignore_cache=1' : '');
      $result .= '<a href="'.$self_url.'" title="reload and ignore cache">'.image_tag($this->base_image_path.'/reload.png', 'align=middle').'</a>';
    }

    $result .= '
    <a href="#" onclick="Element.hide(\'sfStats'.ucfirst($log_image).'\'); return false;">'.image_tag($this->base_image_path.'/close.png', 'align=middle').'</a>
    </div>
    <div id="sfStatsLeftMenu"><a href="#" class="bold" onclick="Element.toggle(\'sfStatsDetails\', \'sfStatsTime\'); return false;">symfony</a></div>
    ';

    return $result;
  }

  private function displayCurrentConfig()
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
      'memusage'     => (function_exists('memory_get_usage')) ? 'on' : 'off'
    );

    $result = '';
    foreach ($config as $key => $value)
    {
      $result .= '<div class="is'.$value.'"><span class="float bold">['.$value.']</span>'.$key.'</div>';
    }

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
            $result .= '<div><span class="float bold">['.$con->getNumQueriesExecuted().']</span>db requests</div>';
          }
        }
        catch (Exception $e)
        {
        }
      }
    }

    return $result;
  }

  public function decorateContentWithDebug($internalUri, $suffix, $retval, $border_color, $bg_color)
  {
    if (!sfConfig::get('sf_web_debug'))
    {
      return $retval;
    }

    $cache = $this->context->getViewCacheManager();
    $this->loadHelpers();

    $last_modified = $cache->lastModified($internalUri, $suffix);
    $id            = md5($internalUri);
    $retval = '
      <div id="main_'.$id.'" class="sfWebDebugActionCache" style="border: 1px solid '.$border_color.'">
      <div id="sub_main_'.$id.'" class="sfStatsCache" style="background-color: '.$bg_color.'; border-right: 1px solid '.$border_color.'; border-bottom: 1px solid '.$border_color.';">
      <div style="height: 16px; padding: 2px"><a href="#" onclick="Element.toggle(\''.$id.'\'); return false;"><strong>cache information</strong></a>&nbsp;<a href="#" onclick="Element.hide(\'sub_main_'.$id.'\'); document.getElementById(\'main_'.$id.'\').style.border = \'none\'; return false;">'.image_tag($this->base_image_path.'/close.png', 'align=middle').'</a>&nbsp;</div>
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
}

?>