<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFormatter provides methods to format text to be displayed on a console.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfFormatter
{
  protected
    $size = 65;

  function __construct($maxLineSize = 65)
  {
    $this->size = $maxLineSize;
  }

  /**
   * Formats a text according to the given parameters.
   *
   * @param  string The test to style
   * @param  mixed  An array of parameters
   * @param  stream A stream (default to STDOUT)
   *
   * @return string The formatted text
   */
  public function format($text = '', $parameters = array(), $stream = STDOUT)
  {
    return $text;
  }

  /**
   * Formats a message within a section.
   *
   * @param string  The section name
   * @param string  The text message
   * @param integer The maximum size allowed for a line (65 by default)
   */
  public function formatSection($section, $text, $size = null)
  {
    return sprintf(">> %-${size}s %s", $section, $this->excerpt($text, $size));
  }

  /**
   * Truncates a line.
   *
   * @param string  The text
   * @param integer The maximum size of the returned string (65 by default)
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

    return substr($text, 0, $subsize).'...'.substr($text, -$subsize);
  }

  /**
   * Sets the maximum line size.
   *
   * @param integer The maximum line size for a message
   */
  public function setMaxLineSize($size)
  {
    $this->size = $size;
  }
}
