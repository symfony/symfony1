<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'PEAR/Frontend.php';
require_once 'PEAR/Frontend/CLI.php';

/**
 * The PEAR Frontend object.
 *
 * @package    symfony
 * @subpackage plugin
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPearFrontendPlugin extends PEAR_Frontend_CLI
{
  protected
    $dispatcher = null;

  /**
   * Sets the sfEventDispatcher object for this frontend.
   *
   * @param sfEventDispatcher The sfEventDispatcher instance
   */
  public function setEventDispatcher(sfEventDispatcher $dispatcher)
  {
    $this->dispatcher = $dispatcher;
  }

  public function _displayLine($text)
  {
    $this->_display($text);
  }

  public function _display($text)
  {
    $this->dispatcher->notify(new sfEvent($this, 'application.log', array($this->splitLongLine($text))));
  }

  protected function splitLongLine($text)
  {
    $t = '';
    foreach (explode("\n", $text) as $longline)
    {
      foreach (explode("\n", wordwrap($longline, 62)) as $line)
      {
        if ($line = trim($line))
        {
          $t .= $line;
        }
      }
    }

    return $t;
  }
}
