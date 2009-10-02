<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Optimizes a project for better performance.
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
      new sfCommandArgument('env', sfCommandArgument::OPTIONAL, 'The environment name', 'prod'),
      new sfCommandArgument('app', sfCommandArgument::OPTIONAL | sfCommandArgument::IS_ARRAY, 'The application name'),
    ));

    $this->namespace = 'project';
    $this->name = 'optimize';
    $this->briefDescription = 'Optimizes a project for better performance';

    $this->detailedDescription = <<<EOF
The [project:optimize|INFO] optimizes a project for better performance:

  [./symfony project:optimize|INFO]

This task should only be used on a production server. Don't forget to re-run
the task each time the project changes.

You can specify an environment other than [prod|COMMENT] by passing it as an
argument:

  [./symfony project:optimize staging|INFO]

You can further specify one or more applications to optimize by passing
additional arguments:

  [./symfony project:optimize prod frontend|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $applications = count($arguments['app']) ? $arguments['app'] : sfFinder::type('dir')->relative()->maxdepth(0)->in(sfConfig::get('sf_apps_dir'));

    if (count($applications) > 1)
    {
      // optimize each application in a separate process
      foreach ($applications as $application)
      {
        try
        {
          $this->logSection('optimize', sprintf('Optimizing %s %s', $arguments['env'], $application));
          $this->getFilesystem()->execute(sprintf('php symfony project:optimize %s %s', $arguments['env'], $application));
        }
        catch (Exception $e)
        {
          $this->logBlock(array_merge(array(
            'Unable to optimize multiple applications. These must be optimized separately:',
            '',
          ), array_map(
            create_function('$a', 'return \'  php symfony project:optimize '.$arguments['env'].' \'.$a;'),
            $applications
          )), 'ERROR_LARGE');

          return 1;
        }
      }
    }
    else
    {
      $application = current($applications);

      $this->logSection('optimize', sprintf('Optimizing %s %s', $arguments['env'], $application));

      $data = array();
      $modules = $this->findModules();
      $target = sfConfig::get('sf_cache_dir').'/'.$application.'/'.$arguments['env'].'/config/configuration.php';

      // remove existing optimization file
      if (file_exists($target))
      {
        $this->getFilesystem()->remove($target);
      }

      // initialize the application and context
      $this->configuration = $this->createConfiguration($application, $arguments['env']);
      sfContext::createInstance($this->configuration);

      // force cache generation for generated modules
      foreach ($modules as $module)
      {
        $this->configuration->getConfigCache()->import('modules/'.$module.'/config/generator.yml', false, true);
      }

      $templates = $this->findTemplates($modules);

      $data['getTemplateDir'] = $this->optimizeGetTemplateDir($modules, $templates);
      $data['getControllerDirs'] = $this->optimizeGetControllerDirs($modules);
      $data['getPluginPaths'] = $this->configuration->getPluginPaths();

      $this->logSection('file+', $target);
      file_put_contents($target, '<?php return '.var_export($data, true).';');
    }
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
