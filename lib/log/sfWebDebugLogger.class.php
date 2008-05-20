<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugLogger logs messages into the web debug toolbar.
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWebDebugLogger extends sfLogger
{
  protected
    $context       = null,
    $dispatcher    = null,
    $webDebug      = null,
    $xdebugLogging = false;

  /**
   * Initializes this logger.
   *
   * @param  sfEventDispatcher $dispatcher  A sfEventDispatcher instance
   * @param  array             $options     An array of options.
   *
   * @return Boolean      true, if initialization completes successfully, otherwise false.
   */
  public function initialize(sfEventDispatcher $dispatcher, $options = array())
  {
    $this->context    = sfContext::getInstance();
    $this->dispatcher = $dispatcher;

    $class = isset($options['web_debug_class']) ? $options['web_debug_class'] : 'sfWebDebug';
    $this->webDebug = new $class($dispatcher);

    $dispatcher->connect('response.filter_content', array($this, 'filterResponseContent'));

    if (isset($options['xdebug_logging']))
    {
      $this->xdebugLogging = $options['xdebug_logging'];
    }

    // disable xdebug when an HTTP debug session exists (crashes Apache, see #2438)
    if (isset($_GET['XDEBUG_SESSION_START']) || isset($_COOKIE['XDEBUG_SESSION']))
    {
      $this->xdebugLogging = false;
    }

    return parent::initialize($dispatcher, $options);
  }

  /**
   * Listens to the response.filter_content event.
   *
   * @param  sfEvent $event   The sfEvent instance
   * @param  string  $context The response content
   *
   * @return string  The filtered response content
   */
  public function filterResponseContent(sfEvent $event, $content)
  {
    if (!sfConfig::get('sf_web_debug'))
    {
      return $content;
    }

    // log timers information
    $messages = array();
    foreach (sfTimerManager::getTimers() as $name => $timer)
    {
      $messages[] = sprintf('%s %.2f ms (%d)', $name, $timer->getElapsedTime() * 1000, $timer->getCalls());
    }
    $this->dispatcher->notify(new sfEvent($this, 'application.log', $messages));

    // don't add debug toolbar:
    // * for XHR requests
    // * if 304
    // * if not rendering to the client
    // * if HTTP headers only
    $response = $event->getSubject();
    if (!$this->context->has('request') || !$this->context->has('response') || !$this->context->has('controller') ||
      $this->context->getRequest()->isXmlHttpRequest() ||
      strpos($response->getContentType(), 'html') === false ||
      $response->getStatusCode() == 304 ||
      $this->context->getController()->getRenderMode() != sfView::RENDER_CLIENT ||
      $response->isHeaderOnly()
    )
    {
      return $content;
    }

    // add needed assets for the web debug toolbar
    $root = $this->context->getRequest()->getRelativeUrlRoot();
    $assets = sprintf('
      <script type="text/javascript" src="%s"></script>
      <link rel="stylesheet" type="text/css" media="screen" href="%s" />',
      $root.sfConfig::get('sf_web_debug_web_dir').'/js/main.js',
      $root.sfConfig::get('sf_web_debug_web_dir').'/css/main.css'
    );
    $content = str_ireplace('</head>', $assets.'</head>', $content);

    // add web debug information to response content
    $webDebugContent = $this->webDebug->getResults();
    $count = 0;
    $content = str_ireplace('</body>', $webDebugContent.'</body>', $content, $count);
    if (!$count)
    {
      $content .= $webDebugContent;
    }

    return $content;
  }

  /**
   * Logs a message.
   *
   * @param string $message   Message
   * @param string $priority  Message priority
   */
  protected function doLog($message, $priority)
  {
    // if we have xdebug and dev has not disabled the feature, add some stack information
    $debugStack = array();
    if ($this->xdebugLogging && function_exists('xdebug_get_function_stack'))
    {
      foreach (xdebug_get_function_stack() as $i => $stack)
      {
        if (
          (isset($stack['function']) && !in_array($stack['function'], array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug', 'log')))
          || !isset($stack['function'])
        )
        {
          $tmp = '';
          if (isset($stack['function']))
          {
            $tmp .= sprintf('in "%s" ', $stack['function']);
          }
          $tmp .= sprintf('from "%s" line %s', $stack['file'], $stack['line']);
          $debugStack[] = $tmp;
        }
      }
    }

    // get log type in {}
    $type = 'sfOther';
    if (preg_match('/^\s*{([^}]+)}\s*(.+?)$/', $message, $matches))
    {
      $type    = $matches[1];
      $message = $matches[2];
    }

    // send the log object containing the complete log information
    $this->webDebug->log(array(
      'priority'   => $priority,
      'time'       => time(),
      'message'    => $message,
      'type'       => $type,
      'debugStack' => $debugStack,
    ));
  }
}
