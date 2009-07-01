<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanel represents a web debug panel.
 *
 * @package    symfony
 * @subpackage debug
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfWebDebugPanel
{
  protected
    $webDebug = null;

  /**
   * Constructor.
   *
   * @param sfWebDebug $webDebug The web debut toolbar instance
   */
  public function __construct(sfWebDebug $webDebug)
  {
    $this->webDebug = $webDebug;
  }

  /**
   * Gets the link URL for the link.
   *
   * @return string The URL link
   */
  public function getTitleUrl()
  {
  }

  /**
   * Gets the text for the link.
   *
   * @return string The link text
   */
  abstract public function getTitle();

  /**
   * Gets the title of the panel.
   *
   * @return string The panel title
   */
  abstract public function getPanelTitle();

  /**
   * Gets the panel HTML content.
   *
   * @return string The panel HTML content
   */
  abstract public function getPanelContent();

  /**
   * Returns a toggler element.
   * 
   * @param  string $element The value of an element's DOM id attribute
   * @param  string $title   A title attribute
   * 
   * @return string
   */
  public function getToggler($element, $title = 'Toggle details')
  {
    return '<a href="#" onclick="sfWebDebugToggle(\''.$element.'\'); return false;" title="'.$title.'"><img src="'.$this->webDebug->getOption('image_root_path').'/toggle.gif" alt="'.$title.'"/></a>';
  }

  /**
   * Formats a file link.
   * 
   * @param  string  $fileOrClass A file path or class name
   * @param  integer $line
   * @param  string  $text        Text to use for the link
   * 
   * @return string
   */
  public function formatFileLink($fileOrClass, $line = null, $text = null)
  {
    if (class_exists($fileOrClass))
    {
      if (is_null($text))
      {
        $text = $fileOrClass;
      }

      $r = new ReflectionClass($fileOrClass);
      $fileOrClass = $r->getFileName();
    }

    if (is_null($text))
    {
      $text = sfDebug::shortenFilePath($fileOrClass);
    }

    if ($linkFormat = sfConfig::get('sf_file_link_format', ini_get('xdebug.file_link_format')))
    {
      $link = strtr($linkFormat, array('%f' => $fileOrClass, '%l' => $line));
      $text = sprintf('<a href="%s" title="Open this file">%s</a>', htmlspecialchars($link, ENT_QUOTES, sfConfig::get('sf_charset')), $text);
    }

    return $text;
  }
}
