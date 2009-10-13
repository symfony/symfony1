<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base class for all symfony Doctrine tasks.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id$
 */
abstract class sfDoctrineBaseTask extends sfBaseTask
{
  /**
   * Returns an array of configuration variables for the Doctrine CLI.
   *
   * @return array $config
   *
   * @see sfDoctrinePluginConfiguration::getCliConfig()
   */
  public function getCliConfig()
  {
    return $this->configuration->getPluginConfiguration('sfDoctrinePlugin')->getCliConfig();
  }

  /**
   * Calls a Doctrine CLI command.
   *
   * @param string $task Name of the Doctrine task to call
   * @param array  $args Arguments for the task
   *
   * @see sfDoctrineCli
   */
  public function callDoctrineCli($task, $args = array())
  {
    $config = $this->getCliConfig();

    $arguments = array('./symfony', $task);

    foreach ($args as $key => $arg)
    {
      if (isset($config[$key]))
      {
        $config[$key] = $arg;
      }
      else
      {
        $arguments[] = $arg;
      }
    }

    $cli = new sfDoctrineCli($config);
    $cli->setDispatcher($this->dispatcher);
    $cli->setFormatter($this->formatter);
    $cli->run($arguments);
  }

  /**
   * Copies schema files in a temporary directory and preps them for Doctrine.
   * 
   * @return string The directory where the schema files are saved to
   */
  protected function prepareSchemaFiles($yamlSchemaPath)
  {
    if (!file_exists($directory = sys_get_temp_dir().'/sfDoctrinePlugin/'.md5(sfConfig::get('sf_root_dir')).'/yaml_schema_files'))
    {
      $this->getFilesystem()->mkdirs($directory);
    }

    // clear the tmp directory
    $finder = sfFinder::type('file')->name('*.yml');
    $this->getFilesystem()->remove($finder->in($directory));

    // copy and markup plugin schema files
    $i = 1;
    foreach ($this->configuration->getPlugins() as $name)
    {
      $plugin = $this->configuration->getPluginConfiguration($name);
      $schemas = $finder->in($plugin->getRootDir().'/config/doctrine');

      if (count($schemas))
      {
        foreach ($schemas as $schema)
        {
          $models = Doctrine_Parser::load($schema, 'yml');

          if (!isset($models['package']))
          {
            $models['package'] = $plugin->getName().'.lib.model.doctrine';
            $models['package_custom_path'] = $plugin->getRootDir().'/lib/model/doctrine';
          }

          $file = sprintf('%s/%03d_%s-%s', $directory, $i, $plugin->getName(), basename($schema));
          $this->logSection('file+', $file);

          Doctrine_Parser::dump($models, 'yml', $file);
        }

        $i++;
      }
    }

    // copy project schema files
    foreach ($finder->in($yamlSchemaPath) as $schema)
    {
      $this->getFilesystem()->copy($schema, sprintf('%s/%03d_project-%s', $directory, $i, basename($schema)));
    }

    return $directory;
  }
}
