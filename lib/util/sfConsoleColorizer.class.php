<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfConsoleColorizer provides methods to colorize console output.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfConsoleColorizer
{
  protected
    $styles = array(
      'ERROR'   => array('bg' => 'red', 'fg' => 'white', 'bold' => true),
      'INFO'    => array('fg' => 'green', 'bold' => true),
      'COMMENT' => array('fg' => 'yellow'),
    ),
    $options    = array('bold' => 1, 'underscore' => 4, 'blink' => 5, 'reverse' => 7, 'conceal' => 8),
    $foreground = array('black' => 30, 'red' => 31, 'green' => 32, 'yellow' => 33, 'blue' => 34, 'magenta' => 35, 'cyan' => 36, 'white' => 37),
    $background = array('black' => 40, 'red' => 41, 'green' => 42, 'yellow' => 43, 'blue' => 44, 'magenta' => 45, 'cyan' => 46, 'white' => 47);

  public function setStyle($name, $options = array())
  {
    $this->styles[$name] = $options;
  }

  public function format($text = '', $parameters = array(), $stream = STDOUT)
  {
    // Disable colors if not supported (windows or non tty console)
    if (DIRECTORY_SEPARATOR == '\\' || !function_exists('posix_isatty') || !@posix_isatty($stream))
    {
      return $text;
    }

    if (!is_array($parameters) && isset($this->styles[$parameters]))
    {
      $parameters = $this->styles[$parameters];
    }

    $codes = array();
    if (isset($parameters['fg']))
    {
      $codes[] = $this->foreground[$parameters['fg']];
    }
    if (isset($parameters['bg']))
    {
      $codes[] = $this->background[$parameters['bg']];
    }
    foreach ($this->options as $option => $value)
    {
      if (isset($parameters[$option]) && $parameters[$option])
      {
        $codes[] = $value;
      }
    }

    return "\033[".implode(';', $codes).'m'.$text."\033[0m";
  }
}
