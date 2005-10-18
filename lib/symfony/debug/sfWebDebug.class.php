<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebug prints debug information in the browser for easy debugging.
 *
 * @package    symfony
 * @subpackage debug
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @version    SVN: $Id: sfWebDebug.class.php 467 2005-09-19 06:30:23Z fabien $
 */
class sfWebDebug
{
  private static
    $init_count    = 0,
    $log           = '',
    $last_time_log = 0;

  public static function log($text)
  {
    if (!self::$last_time_log)
    {
      self::$last_time_log = SF_TIMER_START;
    }

    // elapsed time
    $elapsed_time = sprintf('%.0f', (microtime(true) - self::$last_time_log) * 1000);
    self::$last_time_log = microtime(true);
    $text = preg_replace('/^.+?symfony/m', ' + '.$elapsed_time.' ms symfony', $text);

    self::$log .= $text;
  }

  public static function printResults()
  {
    echo self::getResults();
  }

  public static function getResults()
  {
    if (!SF_WEB_DEBUG) return;

    $result = '';

    $time_end = microtime(true);

    $bg_color = '#dfd';
    if (SF_LOGGING_ACTIVE)
    {
      $log_image = 'info';
      if (preg_match('/\[(emerg|alert|crit|error)\]/', self::$log))
      {
        $log_image = 'error';
        $bg_color = 'red';
      }
      else if (preg_match('/\[(warning|notice)\]/', self::$log))
      {
        $log_image = 'warning';
        $bg_color = 'orange';
      }
    }

    $result .= '
    <style>
    #sfStats
    {
      font-family:arial;
      font-size:10px;
      width: 110px;
      background-color: '.$bg_color.';
      color: #666;
      position: absolute;
      right: 2px;
      top: 2px;
      filter:alpha(opacity=60);
      -moz-opacity:0.6;
      opacity: 0.6;
    }

    #sfStats a, #sfStatsRightMenu a, #sfStatsLeftMenu a, #sfStatsDetails a, #sfStatsLog a
    {
      text-decoration: none;
      border: none;
    }
    
    #sfStatsRightMenu
    {
      padding-top: 2px;
      padding-bottom: 2px;
      float:right;
    }

    #sfStatsLeftMenu
    {
      padding-top: 4px;
      padding-left: 2px;
      padding-bottom: 2px;
      font-size: 11px;
      color: #333;
    }
    
    #sfStatsDetails
    {
      background-color: #ccc;
      padding: 2px;
    }
    
    #sfStatsTime
    {
      padding: 2px;
      background-color: #aaa;
      color: #fff;
    }

    #sfStatsLog
    {
      display: none;
      z-index: 999;
      margin: 0;
      padding: 3px;
      padding-bottom: 10px;
      font-family:arial;
      font-size:11px;
      width: 98%;
      border: 1px solid #aaa;
      background-color: #eee;
      color: #333;
      position: absolute;
      left: 0;
      top: 0;
    }

    .sfStatsGreen, .sfStatsOrange, .sfStatsRed
    {
      padding-bottom: 3px;
    }

    .sfStatsLogType
    {
      color: darkgreen;
    }

    .sfStatsFileInfo
    {
      color: blue;
    }

    .ison
    {
      color: green;
    }

    .isoff
    {
      color: red;
    }
    </style>
    <script language="javascript" type="text/javascript" src="/sf/js/prototype.js"></script>
    <div id="sfStats">
    <div id="sfStatsRightMenu">
    ';

    if (SF_LOGGING_ACTIVE)
    {
      $result .= '<a href="#" onclick="document.getElementById(\'sfStatsLog\').style.display=\'block\'"><img align="absmiddle" src="/sf/images/sf_debug_stats/'.$log_image.'.png" /></a>&nbsp;';
    }

    if (SF_DEBUG && SF_CACHE)
    {
      $result .= '<a href="'.$_SERVER['PHP_SELF'].((!preg_match('/ignore_cache/', $_SERVER['PHP_SELF'])) ? '?ignore_cache=1' : '').'" title="reload and ignore cache"><img align="absmiddle" src="/sf/images/sf_debug_stats/reload.png" /></a>';
    }

    $result .= '
    <a href="#" onclick="document.getElementById(\'sfStats\').style.display=\'none\'"><img align="absmiddle" src="/sf/images/sf_debug_stats/close.png" /></a>
    </div><div id="sfStatsLeftMenu"><strong><a href="#" onclick="new Element.toggle(\'sfStatsDetails\'); new Element.toggle(\'sfStatsTime\')">SymFony</a></strong></div>
    ';

    $is_eaccelerator = (function_exists('eaccelerator') && ini_get('eaccelerator.enable')) ? 'on' : 'off';
    $is_xdebug = (function_exists('xdebug_get_function_stack')) ? 'on' : 'off';
    $is_tidy = (function_exists('tidy_parse_string')) ? 'on' : 'off';
    $is_compression = SF_COMPRESSED ? 'on' : 'off';

    $is_debug = SF_DEBUG ? 'on' : 'off';
    $is_logging = SF_LOGGING_ACTIVE ? 'on' : 'off';
    $is_routing = SF_ROUTING ? 'on' : 'off';
    $is_cache = SF_CACHE ? 'on' : 'off';

    $result .= '
    <div id="sfStatsDetails">
    <div class="is'.$is_debug.'">debug <strong>['.$is_debug.']</strong></div>
    <div class="is'.$is_logging.'">logging <strong>['.$is_logging.']</strong></div>
    <div class="is'.$is_routing.'">routing <strong>['.$is_routing.']</strong></div>
    <div class="is'.$is_cache.'">html cache <strong>['.$is_cache.']</strong></div>
    <div class="is'.$is_eaccelerator.'">eaccelerator <strong>['.$is_eaccelerator.']</strong></div>
    <div class="is'.$is_xdebug.'">xdebug <strong>['.$is_xdebug.']</strong></div>
    <div class="is'.$is_tidy.'">tidy <strong>['.$is_tidy.']</strong></div>
    <div class="is'.$is_compression.'">compression <strong>['.$is_compression.']</strong></div>
    ';

    if (SF_DEBUG)
    {
      // get Propel statistics if available (user created a model and a db)
      try
      {
        $con = Propel::getConnection();
        $result .= '
        <div>db requests <strong>['.$con->getNumQueriesExecuted().']</strong></div>
        ';
      }
      catch (Exception $e)
      {
      }
    }

    $result .= '</div>';

    $total_time = ($time_end - SF_TIMER_START) * 1000;
    if ($total_time < 1)
    {
      $total_time = sprintf('%.2f', $total_time);
    }
    else
    {
      $total_time = sprintf('%.0f', $total_time);
    }
    $result .= '<div id="sfStatsTime">processed in <strong>'.$total_time.'</strong> ms</div>';

    $result .= '</div>';

    if (SF_LOGGING_ACTIVE)
    {
      $log = self::formatLog(self::$log);

      // we find all type available in this log session
      $types = array();
      preg_match_all('/\[<span class="sfStatsLogType">([^\s]+?)<\/span>\]/s', $log, $matches);
      for ($i = 0, $max = count($matches[0]); $i < $max; $i++)
      {
        if (!in_array($matches[1][$i], $types)) $types[] = $matches[1][$i];
      }

      $result .= '
      <script>
      function toggleMessages(myclass)
      {
        var xpathResult = document.evaluate("//*[contains(@class, \'" + myclass + "\')]", document, null, 0, null);
        var elements = new Array();
        while ((elements[elements.length] = xpathResult.iterateNext())) {}
        for (i = 0; i < elements.length; i++)
        {
          if (!elements[i]) continue;
  
          if (elements[i].style.display == "none")
            elements[i].style.display = "block";
          else
            elements[i].style.display = "none";
        }

        if (elements[0].style.display == "none")
          document.getElementById(myclass).style.color = "blue";
        else
          document.getElementById(myclass).style.color = "red";
      }
      </script>
  
      <div id="sfStatsLog">
      <div style="border-bottom: 1px solid #aaa; height: 22px; margin-bottom: 5px">
      <div style="float:right">
      ';

      sort($types);
      foreach ($types as $type)
        $result .= '<a id="'.$type.'" href="#" onclick="toggleMessages(\''.$type.'\')">'.$type.'</a>&nbsp;*&nbsp;';
      
      $result .= '
        <a href="#" onclick="toggleMessages(\'sfStatsGreen\')"><img align="absmiddle" src="/sf/images/sf_debug_stats/info.png" /></a>&nbsp;
        <a href="#" onclick="document.getElementById(\'sfStatsLog\').style.display=\'none\'"><img align="absmiddle" src="/sf/images/sf_debug_stats/close.png" /></a>
      </div>
      <strong>Log messages</strong>
      </div>
      '.$log.'</div>
      ';
    }

    return $result;
  }

  static private function formatLog($log)
  {
    foreach (array('SF_APP_DIR', 'SF_ROOT_DIR') as $constant)
    {
      $log = str_replace(realpath(constant($constant)), $constant, $log);
    }

    $log = preg_replace('/^(.+?)symfony /m', '\\1', $log);
    $log = preg_replace('/"(.+?)"/s', '"<span class="sfStatsFileInfo">\\1</span>"', $log);
    $log = preg_replace('/in (.+?) at line (\d+)/s', 'in <span class="sfStatsFileInfo">\\1</span> at line <span class="sfStatsFileInfo">\\2</span>', $log);

    $log = preg_replace('/^(.+?)\[(emerg|alert|crit|error)\] ({([^\s]+?)})?(.*?)$/m', <<<EOF
'<div class="sfStatsRed '.('\\4' ? '\\4' : 'sfOther').'"><img align="absmiddle" src="/sf/images/sf_debug_stats/error.png" />&nbsp;[<span class="sfStatsLogType">'.('\\4' ? '\\4' : 'sfOther').'</span>]\\1 \\5</div>'
EOF
, $log);
    $log = preg_replace('/^(.+?)\[(warning|notice)\] ({([^\s]+?)})?(.*?)$/me', <<<EOF
'<div class="sfStatsOrange '.('\\4' ? '\\4' : 'sfOther').'"><img align="absmiddle" src="/sf/images/sf_debug_stats/warning.png" />&nbsp;[<span class="sfStatsLogType">'.('\\4' ? '\\4' : 'sfOther').'</span>]\\1 \\5</div>'
EOF
, $log);
    $log = preg_replace('/^(.+?)\[(info|debug)\] ({([^\s]+?)})?(.*?)$/me', <<<EOF
'<div class="sfStatsGreen '.('\\4' ? '\\4' : 'sfOther').'" style="display:none"><img align="absmiddle" src="/sf/images/sf_debug_stats/info.png" />&nbsp;[<span class="sfStatsLogType">'.('\\4' ? '\\4' : 'sfOther').'</span>]\\1 \\5</div>'
EOF
, $log);

    $log = preg_replace('/\[BEGIN_STACK\]/', '&nbsp;<a href="#" onclick="new Element.toggle(this.nextSibling);"><img src="/sf/images/sf_debug_stats/toggle.gif" /></a><span style="display:none">', $log);
    $log = preg_replace('/\[END_STACK\]/', '</span>', $log);

    $log = preg_replace('/\[BEGIN_COMMENT\]/', '&nbsp;<a href="#" onclick="new Element.toggle(this.nextSibling);"><img src="/sf/images/sf_debug_stats/toggle.gif" /></a><span style="display:none">', $log);
    $log = preg_replace('/\[END_COMMENT\]/', '</span>', $log);

    $log = preg_replace('/\[n\]/', '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $log);

    // we add line numbers
    $logs = explode("\n", $log);
    for ($i = 0; $i < count($logs); $i++)
      $logs[$i] = preg_replace('/^(<.+?>)/im', '\\1&nbsp;&lt;<em>'.($i + 1).'</em>&gt;&nbsp;', $logs[$i]);

    return implode("\n", $logs);
  }

  static public function decorateContentWithDebug($moduleName, $actionName, $suffix, $retval, $border_color, $bg_color)
  {
    $context = sfContext::getInstance();
    $cache = $context->getViewCacheManager();

    $last_modified = $cache->lastModified($moduleName, $actionName, $suffix);
    $uri = $cache->getUri($moduleName, $actionName, $suffix);
    $id = md5($uri);
    $retval = '
      <div id="main_'.$id.'" class="sfWebDebugActionCache" style="border: 1px solid '.$border_color.'">
      <div id="sub_main_'.$id.'" style="padding: 0; margin: 0; font-family: Arial; position: absolute; overflow: hidden; z-index: 998; font-size: 9px; padding: 2px; background-color: '.$bg_color.'; border-right: 1px solid '.$border_color.'; border-bottom: 1px solid '.$border_color.'; filter:alpha(opacity=85); -moz-opacity:0.85; opacity: 0.85">
      <div style="height: 16px; padding: 2px"><a href="#" onclick="el = document.getElementById(\''.$id.'\'); el.style.display = (el.style.display == \'none\') ? \'block\' : \'none\'"><strong>cache information</strong></a>&nbsp;<a href="#" onclick="document.getElementById(\'sub_main_'.$id.'\').style.display = \'none\'; document.getElementById(\'main_'.$id.'\').style.border = \'none\'"><img align="absmiddle" src="/sf/images/sf_debug_stats/close.png" /></a>&nbsp;</div>
        <div style="padding: 2px; display: none" id="'.$id.'">
        [uri]&nbsp;'.$uri.'<br />
        [life&nbsp;time]&nbsp;'.$cache->getLifeTime($moduleName, $actionName, $suffix).'&nbsp;seconds<br />
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