<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfPHPView extends sfView
{
  /**
   * Executes any presentation logic for this view.
   */
  public function execute()
  {
  }

  /**
   * Load core and standard helpers to be use in the template.
   */
  protected function loadCoreAndStandardHelpers()
  {
    static $coreHelpersLoaded = 0;

    if ($coreHelpersLoaded)
    {
      return;
    }

    $coreHelpersLoaded = 1;
    $core_helpers = array('Helper', 'Url', 'Asset', 'Tag', 'Escaping');
    $standard_helpers = sfConfig::get('sf_standard_helpers');

    $helpers = array_unique(array_merge($core_helpers, $standard_helpers));
    sfLoader::loadHelpers($helpers);
  }

  /**
   * Renders the presentation.
   *
   * @param string Filename
   *
   * @return string File content
   */
  protected function renderFile($_sfFile)
  {
    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->context->getLogger()->info(sprintf('{sfView} render "%s"', $_sfFile));
    }

    $this->loadCoreAndStandardHelpers();

    extract($this->attributeHolder->toArray());

    // render
    ob_start();
    ob_implicit_flush(0);
    require($_sfFile);

    return ob_get_clean();
  }

  /**
   * Retrieves the template engine associated with this view.
   *
   * Note: This will return null because PHP itself has no engine reference.
   *
   * @return null
   */
  public function getEngine()
  {
    return null;
  }

  /**
   * Configures template.
   *
   * @return void
   */
  public function configure()
  {
    // store our current view
    $this->context->set('view_instance', $this);

    // require our configuration
    $viewConfigFile = $this->moduleName.'/'.sfConfig::get('sf_app_module_config_dir_name').'/view.yml';
    require(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_module_dir_name').'/'.$viewConfigFile));

    // decorator configuration
    $this->updateDecoratorConfiguration();

    // set template directory
    if (!$this->directory)
    {
      $this->setDirectory(sfLoader::getTemplateDir($this->moduleName, $this->getTemplate()));
    }
  }

  /**
   * Loop through all template slots and fill them in with the results of
   * presentation data.
   *
   * @param string A chunk of decorator content
   *
   * @return string A decorated template
   */
  protected function decorate($content)
  {
    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->context->getLogger()->info(sprintf('{sfView} decorate content with "%s"', $this->getDecoratorDirectory().'/'.$this->getDecoratorTemplate()));
    }

    // set the decorator content as an attribute
    $this->attributeHolder->set('sf_content', $content);

    // render the decorator template and return the result
    return $this->renderFile($this->getDecoratorDirectory().'/'.$this->getDecoratorTemplate());
  }

  /**
   * Renders the presentation.
   *
   * @return string A string representing the rendered presentation
   */
  public function render()
  {
    $content = null;
    if (sfConfig::get('sf_cache'))
    {
      $viewCache = $this->context->getViewCacheManager();
      $uri = $this->context->getRouting()->getCurrentInternalUri();

      list($content, $attributeHolder) = $viewCache->getActionCache($uri);
      if (!is_null($content))
      {
        $this->attributeHolder = $attributeHolder;
      }

      // FIXME: needed because the response in cache can change the layout
      $this->updateDecoratorConfiguration();
    }

    // render template if no cache
    if (is_null($content))
    {
      // execute pre-render check
      $this->preRenderCheck();

      // render template file
      $content = $this->renderFile($this->getDirectory().'/'.$this->getTemplate());

      if (sfConfig::get('sf_cache'))
      {
        $content = $viewCache->setActionCache($uri, $content, $this->attributeHolder);
      }
    }

    // now render decorator template, if one exists
    if ($this->isDecorator())
    {
      $content = $this->decorate($content);
    }

    return $content;
  }

  protected function updateDecoratorConfiguration()
  {
    // decorator configuration
    $layout = $this->context->getResponse()->getParameter($this->moduleName.'_'.$this->actionName.'_layout', null, 'symfony/action/view');
    if (false === $layout)
    {
      $this->setDecorator(false);
    }
    else if (!is_null($layout))
    {
      $this->setDecoratorTemplate($layout.$this->getExtension());
    }
  }
}
