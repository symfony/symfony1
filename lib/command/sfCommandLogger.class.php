<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage log
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfCommandLogger extends sfConsoleLogger
{
  protected
    $output = null,
    $size   = 65;

  /**
   * Initializes this logger.
   *
   * @param  sfEventDispatcher A sfEventDispatcher instance
   * @param  array        An array of options.
   */
  public function initialize(sfEventDispatcher $dispatcher, $options = array())
  {
    if (!isset($options['output']))
    {
      throw new sfConfigurationException('The "output" option is mandatory for a command logger.');
    }

    $this->output = $options['output'];
    $this->size = isset($options['size']) ? $options['size'] : 65;
  }

  /**
   * Formats a message for a given type.
   *
   * @param  string The text message
   * @param  mixed  The message type (COMMENT, INFO, ERROR)
   *
   * @return string The formatted string
   */
  public function format($text, $type)
  {
    return $this->output->format($text, $type);
  }

  /**
   * Formats a message within a section.
   *
   * @param string  The section name
   * @param string  The text message
   * @param integer The maximum size allowed for a line
   */
  public function formatSection($section, $text, $size = null)
  {
    $width = 9 + strlen($this->output->format('', 'INFO'));

    return sprintf(">> %-${width}s %s\n", $this->output->format($section, 'INFO'), $this->excerpt($text, $size));
  }

  /**
   * Truncates a line.
   *
   * @param string  The text
   * @param integer The maximum size of the returned string
   *
   * @return string The truncated string
   */
  public function excerpt($text, $size = null)
  {
    if (!$size)
    {
      $size = $this->size;
    }

    if (strlen($text) < $size)
    {
      return $text;
    }

    $subsize = floor(($size - 3) / 2);

    return substr($text, 0, $subsize).$this->output->format('...', 'INFO').substr($text, -$subsize);
  }
}
