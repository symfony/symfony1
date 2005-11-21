<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfRenderView.class.php 502 2005-10-06 16:02:14Z fabien $
 */
class sfRenderView extends sfPHPView
{
  public function execute()
  {
    $context          = $this->getContext();
    $actionStackEntry = $context->getController()->getActionStack()->getLastEntry();
    $action           = $actionStackEntry->getActionInstance();

    // require our configuration
    $viewConfigFile = $this->moduleName.'/'.SF_APP_MODULE_CONFIG_DIR_NAME.'/view.yml';
    require(sfConfigCache::checkConfig(SF_APP_MODULE_DIR_NAME.'/'.$viewConfigFile));

    $viewType = sfView::SUCCESS;
    if (preg_match('/^'.$action->getActionName().'(.+)$/i', $this->viewName, $match))
    {
      $viewType = $match[1];
    }

    // set template name
    $templateFile = $templateName.$viewType.'.php';
    $this->setTemplate($templateFile);

    // set template directory
    $module = $context->getModuleName();
    if (!is_readable($this->getDirectory().'/'.$templateFile))
    {
      // search template in a symfony module directory
      if (is_readable(SF_SYMFONY_DATA_DIR.'/symfony/modules/'.$module.'/templates/'.$templateFile))
      {
        $this->setDirectory(SF_SYMFONY_DATA_DIR.'/symfony/modules/'.$module.'/templates');
      }

      // search template for generated templates in cache
      if (is_readable(SF_MODULE_CACHE_DIR.'/auto'.ucfirst($module).'/templates/'.$templateFile))
      {
        $this->setDirectory(SF_MODULE_CACHE_DIR.'/auto'.ucfirst($module).'/templates');
      }
    }

    if (SF_LOGGING_ACTIVE) $context->getLogger()->info('{sfRenderView} execute view for template "'.$templateName.$viewType.'.php"');
  }
}

?>