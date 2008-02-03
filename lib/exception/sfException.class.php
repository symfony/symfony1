<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfException is the base class for all symfony related exceptions and
 * provides an additional method for printing up a detailed view of an
 * exception.
 *
 * @package    symfony
 * @subpackage exception
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfException extends Exception
{
  protected
    $wrappedException = null;

  /**
   * Wraps an Exception.
   *
   * @param Exception An Exception instance
   *
   * @return sfException An sfException instance that wraps the given Exception object
   */
  static public function createFromException(Exception $e)
  {
    $exception = new sfException(sprintf('Wrapped %s: %s', get_class($e), $e->getMessage()));
    $exception->setWrappedException($e);

    return $exception;
  }

  /**
   * Changes the wrapped exception.
   *
   * @param Exception An Exception instance
   */
  public function setWrappedException(Exception $e)
  {
    $this->wrappedException = $e;
  }

  /**
   * Prints the stack trace for this exception.
   */
  public function printStackTrace()
  {
    $exception = is_null($this->wrappedException) ? $this : $this->wrappedException;

    if (!sfConfig::get('sf_test'))
    {
      // log all exceptions in php log
      error_log($exception->getMessage());

      // clean current output buffer
      while (@ob_end_clean());

      ob_start(sfConfig::get('sf_compressed') ? 'ob_gzhandler' : '');

      header('HTTP/1.0 500 Internal Server Error');
    }

    try
    {
      $this->outputStackTrace($exception);
    }
    catch (Exception $e)
    {
    }

    if (!sfConfig::get('sf_test'))
    {
      exit(1);
    }
  }

  /**
   * Gets the stack trace for this exception.
   */
  static protected function outputStackTrace($exception)
  {
    if (class_exists('sfContext', false) && sfContext::hasInstance())
    {
      $dispatcher = sfContext::getInstance()->getEventDispatcher();

      if (sfConfig::get('sf_logging_enabled'))
      {
        $dispatcher->notify(new sfEvent($exception, 'application.log', array($exception->getMessage(), 'priority' => sfLogger::ERR)));
      }

      $event = $dispatcher->notifyUntil(new sfEvent($exception, 'application.throw_exception'));
      if ($event->isProcessed())
      {
        return;
      }
    }

    // send an error 500 if not in debug mode
    if (!sfConfig::get('sf_debug'))
    {
      $file = sfConfig::get('sf_web_dir').'/errors/error500.php';

      include is_readable($file) ? $file : dirname(__FILE__).'/data/error500.php';

      return;
    }

    $message = null !== $exception->getMessage() ? $exception->getMessage() : 'n/a';
    $name    = get_class($exception);
    $format  = 0 == strncasecmp(PHP_SAPI, 'cli', 3) ? 'plain' : 'html';
    $traces  = self::getTraces($exception, $format);

    // dump main objects values
    $sf_settings = '';
    $settingsTable = $requestTable = $responseTable = $globalsTable = '';
    if (class_exists('sfContext', false) && sfContext::hasInstance())
    {
      $context = sfContext::getInstance();
      $settingsTable = self::formatArrayAsHtml(sfDebug::settingsAsArray());
      $requestTable  = self::formatArrayAsHtml(sfDebug::requestAsArray($context->getRequest()));
      $responseTable = self::formatArrayAsHtml(sfDebug::responseAsArray($context->getResponse()));
      $globalsTable  = self::formatArrayAsHtml(sfDebug::globalsAsArray());
    }

    include dirname(__FILE__).'/data/exception.'.($format == 'html' ? 'php' : 'txt');
  }

  /**
   * Returns an array of exception traces.
   *
   * @param Exception An Exception implementation instance
   * @param string The trace format (plain or html)
   *
   * @return array An array of traces
   */
  static public function getTraces($exception, $format = 'plain')
  {
    $traceData = $exception->getTrace();
    array_unshift($traceData, array(
      'function' => '',
      'file'     => $exception->getFile() != null ? $exception->getFile() : 'n/a',
      'line'     => $exception->getLine() != null ? $exception->getLine() : 'n/a',
      'args'     => array(),
    ));

    $traces = array();
    if ($format == 'html')
    {
      $lineFormat = 'at <strong>%s%s%s</strong>(%s)<br />in <em>%s</em> line %s <a href="#" onclick="toggle(\'%s\'); return false;">...</a><br /><ul id="%s" style="display: %s">%s</ul>';
    }
    else
    {
      $lineFormat = 'at %s%s%s(%s) in %s line %s';
    }
    for ($i = 0, $count = count($traceData); $i < $count; $i++)
    {
      $line = isset($traceData[$i]['line']) ? $traceData[$i]['line'] : 'n/a';
      $file = isset($traceData[$i]['file']) ? $traceData[$i]['file'] : 'n/a';
      $shortFile = preg_replace(array('#^'.preg_quote(sfConfig::get('sf_root_dir')).'#', '#^'.preg_quote(realpath(sfConfig::get('sf_symfony_lib_dir'))).'#'), array('SF_ROOT_DIR', 'SF_SYMFONY_LIB_DIR'), $file);
      $args = isset($traceData[$i]['args']) ? $traceData[$i]['args'] : array();
      $traces[] = sprintf($lineFormat,
        (isset($traceData[$i]['class']) ? $traceData[$i]['class'] : ''),
        (isset($traceData[$i]['type']) ? $traceData[$i]['type'] : ''),
        $traceData[$i]['function'],
        self::formatArgs($args, false, $format),
        $shortFile,
        $line,
        'trace_'.$i,
        'trace_'.$i,
        $i == 0 ? 'block' : 'none',
        self::fileExcerpt($file, $line)
      );
    }

    return $traces;
  }

  /**
   * Returns an HTML version of an array as YAML.
   *
   * @param array The values array
   *
   * @return string An HTML string
   */
  static protected function formatArrayAsHtml($values)
  {
    return '<pre>'.@sfYaml::Dump($values).'</pre>';
  }

  /**
   * Returns an excerpt of a code file around the given line number.
   *
   * @param string A file path
   * @param int The selected line number
   *
   * @return string An HTML string
   */
  static protected function fileExcerpt($file, $line)
  {
    if (is_readable($file))
    {
      $content = preg_split('#<br />#', highlight_file($file, true));

      $lines = array();
      for ($i = max($line - 3, 1), $max = min($line + 3, count($content)); $i <= $max; $i++)
      {
        $lines[] = '<li'.($i == $line ? ' class="selected"' : '').'>'.$content[$i - 1].'</li>';
      }

      return '<ol start="'.max($line - 3, 1).'">'.implode("\n", $lines).'</ol>';
    }
  }

  /**
   * Formats an array as a string.
   *
   * @param array The argument array
   * @param boolean 
   * @param string The format string (html or plain)
   *
   * @return string
   */
  static protected function formatArgs($args, $single = false, $format = 'html')
  {
    $result = array();

    $single and $args = array($args);

    foreach ($args as $key => $value)
    {
      if (is_object($value))
      {
        $result[] = ($format == 'html' ? '<em>object</em>' : 'object').'(\''.get_class($value).'\')';
      }
      else if (is_array($value))
      {
        $result[] = ($format == 'html' ? '<em>array</em>' : 'array').'('.self::formatArgs($value).')';
      }
      else if ($value === null)
      {
        $result[] = '<em>null</em>';
      }
      else if (!is_int($key))
      {
        $result[] = "'$key' =&gt; '$value'";
      }
      else
      {
        $result[] = "'".$value."'";
      }
    }

    return implode(', ', $result);
  }
}
