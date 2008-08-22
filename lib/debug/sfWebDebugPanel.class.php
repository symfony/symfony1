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
  public function getLinkUrl()
  {
  }

  /**
   * Gets the text for the link.
   *
   * @return string The link text
   */
  abstract public function getLinkText();

  /**
   * Gets the panel HTML content.
   *
   * @return string The panel HTML content
   */
  abstract public function getPanelContent();

  /**
   * Gets the title of the panel.
   *
   * @return string The panel title
   */
  abstract public function getTitle();
}
