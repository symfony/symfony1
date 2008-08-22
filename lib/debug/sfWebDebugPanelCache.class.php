<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugPanelCache adds a panel to the web debug toolbar with a link to ignore the cache
 * on the next request.
 *
 * @package    symfony
 * @subpackage debug
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWebDebugPanelCache extends sfWebDebugPanel
{
  public function getLinkText()
  {
    if (sfConfig::get('sf_debug') && sfConfig::get('sf_cache'))
    {
      return image_tag(sfConfig::get('sf_web_debug_web_dir').'/images/reload.png');
    }
  }

  public function getLinkUrl()
  {
    return $_SERVER['PHP_SELF'].((strpos($_SERVER['PHP_SELF'], '_sf_ignore_cache') === false) ? '?_sf_ignore_cache=1' : '');
  }

  public function getPanelContent()
  {
  }

  public function getTitle()
  {
    return 'reload and ignore cache';
  }
}
