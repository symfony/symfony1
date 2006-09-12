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
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPartialView extends sfPHPView
{
  public function execute()
  {
  }

  public function configure()
  {
    $this->setDecorator(false);

    $this->setTemplate($this->actionName.$this->getExtension());
    if ('global' == $this->moduleName)
    {
      $this->setDirectory(sfConfig::get('sf_app_template_dir'));
    }
  }

  public function render($templateVars = array())
  {
    $sf_logging_active = sfConfig::get('sf_logging_active');
    if (sfConfig::get('sf_web_debug') && $sf_logging_active)
    {
      $timer = sfTimerManager::getTimer(sprintf('Partial "%s/%s"', $this->moduleName, $this->actionName));
    }

    // execute pre-render check
    $this->preRenderCheck();

    // assigns some variables to the template
    $this->attribute_holder->add($this->getGlobalVars());
    $this->attribute_holder->add($templateVars);

    // render template
    $retval = $this->renderFile($this->getDirectory().'/'.$this->getTemplate());

    if (sfConfig::get('sf_web_debug') && $sf_logging_active)
    {
      $timer->addTime();
    }

    return $retval;
  }
}
