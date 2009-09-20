<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Deploys a project to another server.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfProjectDeployTask.class.php 22080 2009-09-16 13:27:19Z fabien $
 */
class sfProjectOptimizeTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The server name'),
      new sfCommandArgument('environment', sfCommandArgument::OPTIONAL, 'The server name', 'prod'),
    ));

    $this->namespace = 'project';
    $this->name = 'optimize';
    $this->briefDescription = 'Deploys a project to another server';

    $this->detailedDescription = <<<EOF
The [project:deploy|INFO] task deploys a project on a server:

  [./symfony project:deploy production|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $data = array();
    $modules = $this->findModules();
    $target = sfConfig::get('sf_cache_dir').'/'.$arguments['application'].'/'.$arguments['environment'].'/config/configuration.php';

    // remove existing optimization file
    if (file_exists($target))
    {
      unlink($target);
    }

    // initialize the context
    sfContext::createInstance($this->configuration);

    // force cache generation for generated modules
    foreach ($modules as $module)
    {
      $this->configuration->getConfigCache()->import('modules/'.$module.'/config/generator.yml', false, true);
    }

    $templates = $this->findTemplates($modules);

    // getTemplateDir() optimization
    $data['getTemplateDir'] = $this->optimizeGetTemplateDir($modules, $templates);
    $data['getControllerDirs'] = $this->optimizeGetControllerDirs($modules);
    $data['getPluginPaths'] = $this->configuration->getPluginPaths();

    file_put_contents($target, '<?php return '.var_export($data, true).';');
  }

  protected function optimizeGetControllerDirs($modules)
  {
    $data = array();
    foreach ($modules as $module)
    {
      $data[$module] = $this->configuration->getControllerDirs($module);
    }

    return $data;
  }

  protected function optimizeGetTemplateDir($modules, $templates)
  {
    $data = array();
    foreach ($modules as $module)
    {
      $data[$module] = array();
      foreach ($templates[$module] as $template)
      {
        if (null !== ($dir = $this->configuration->getTemplateDir($module, $template)))
        {
          $data[$module][$template] = $dir;
        }
      }
    }

    return $data;
  }

  protected function findTemplates($modules)
  {
    $files = array();

    foreach ($modules as $module)
    {
      $files[$module] = sfFinder::type('file')->follow_link()->relative()->in($this->configuration->getTemplateDirs($module));
    }

    return $files;
  }

  protected function findModules()
  {
    // application
    $dirs = array(sfConfig::get('sf_app_module_dir'));

    // plugins
    foreach ($this->configuration->getPluginPaths() as $path)
    {
      $dirs[] = $path.'/modules';
    }

    // core modules
    $dirs[] = sfConfig::get('sf_symfony_lib_dir').'/controller';

    return array_unique(sfFinder::type('dir')->maxdepth(0)->follow_link()->relative()->in($dirs));
  }
}
