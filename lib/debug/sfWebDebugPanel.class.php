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

  public function __construct(sfWebDebug $webDebug)
  {
    $this->webDebug = $webDebug;
  }

  public function getLinkUrl()
  {
  }

  abstract public function getLinkText();

  abstract public function getPanelContent();

  abstract public function getTitle();
}
