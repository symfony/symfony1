<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).'/../vendor');

/**
 * Base class for all symfony Propel tasks.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfPropelBaseTask extends sfBaseTask
{
  const CHECK_SCHEMA = true;
  const DO_NOT_CHECK_SCHEMA = false;

  static protected $done = false;

  public function initialize(sfEventDispatcher $dispatcher, sfFormatter $formatter)
  {
    parent::initialize($dispatcher, $formatter);

    if (!self::$done)
    {
      set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__).'/../vendor');

      $libDir = dirname(__FILE__).'/..';

      $autoloader = sfSimpleAutoload::getInstance();
      $autoloader->addDirectory($libDir.'/vendor/creole');
      $autoloader->addDirectory($libDir.'/vendor/propel');
      $autoloader->addDirectory($libDir.'/creole');
      $autoloader->addDirectory($libDir.'/propel');
      $autoloader->setClassPath('Propel', $libDir.'/propel/sfPropelAutoload.php');
      $autoloader->addDirectory(sfConfig::get('sf_lib_dir').'/model');
      $autoloader->addDirectory(sfConfig::get('sf_lib_dir').'/form');
      $autoloader->register();

      self::$done = true;
    }
  }

  protected function schemaToYML($checkSchema = self::CHECK_SCHEMA, $prefix = '')
  {
    $finder = sfFinder::type('file')->name('*schema.xml');

    $schemas = array_unique(array_merge($finder->in('config'), $finder->in(glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'config'))));
    if (self::CHECK_SCHEMA === $checkSchema && !count($schemas))
    {
      throw new sfCommandException('You must create a schema.xml file.');
    }

    $dbSchema = new sfPropelDatabaseSchema();
    foreach ($schemas as $schema)
    {
      $dbSchema->loadXML($schema);

      $this->logSection('schema', sprintf('converting "%s" to YML', $schema));

      $localprefix = $prefix;

      // change prefix for plugins
      if (preg_match('#plugins[/\\\\]([^/\\\\]+)[/\\\\]#', $schema, $match))
      {
        $localprefix = $prefix.$match[1].'-';
      }

      // save converted xml files in original directories
      $yml_file_name = str_replace('.xml', '.yml', basename($schema));

      $file = str_replace(basename($schema), $prefix.$yml_file_name,  $schema);
      $this->logSection('schema', sprintf('putting %s', $file));
      file_put_contents($file, $dbSchema->asYAML());
    }
  }

  protected function schemaToXML($checkSchema = self::CHECK_SCHEMA, $prefix = '')
  {
    $finder = sfFinder::type('file')->name('*schema.yml');
    $dirs = array('config');
    if ($pluginDirs = glob(sfConfig::get('sf_plugins_dir').'/*/config'))
    {
      $dirs = array_merge($dirs, $pluginDirs);
    }
    $schemas = $finder->in($dirs);
    if (self::CHECK_SCHEMA === $checkSchema && !count($schemas))
    {
      throw new sfCommandException('You must create a schema.yml file.');
    }

    $dbSchema = new sfPropelDatabaseSchema();

    foreach ($schemas as $schema)
    {
      $schemaArray = sfYaml::load($schema);
      
      if (!is_array($schemaArray))
      {
        continue; // No defined schema here, skipping
      }

      if (!isset($schemaArray['classes']))
      {
        // Old schema syntax: we convert it
        $schemaArray = $dbSchema->convertOldToNewYaml($schemaArray);
      }

      $customSchemaFilename = str_replace(array(sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR, 'plugins'.DIRECTORY_SEPARATOR, 'config'.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, 'schema.yml'), array('', '', '', '_', 'schema.custom.yml'), $schema);
      $customSchemas = sfFinder::type('file')->name($customSchemaFilename)->in($dirs);

      foreach ($customSchemas as $customSchema)
      {
        $this->logSection('schema', sprintf('found custom schema %s', $customSchema));

        $customSchemaArray = sfYaml::load($customSchema);
        if (!isset($customSchemaArray['classes']))
        {
          // Old schema syntax: we convert it
          $customSchemaArray = $dbSchema->convertOldToNewYaml($customSchemaArray);
        }
        $schemaArray = sfToolkit::arrayDeepMerge($schemaArray, $customSchemaArray);
      }

      $dbSchema->loadArray($schemaArray);

      $this->logSection('schema', sprintf('converting "%s" to XML', $schema));

      $localprefix = $prefix;

      // change prefix for plugins
      if (preg_match('#plugins[/\\\\]([^/\\\\]+)[/\\\\]#', $schema, $match))
      {
        $localprefix = $prefix.$match[1].'-';
      }

      // save converted xml files in original directories
      $xml_file_name = str_replace('.yml', '.xml', basename($schema));

      $file = str_replace(basename($schema), $localprefix.$xml_file_name,  $schema);
      $this->logSection('schema', sprintf('putting %s', $file));
      file_put_contents($file, $dbSchema->asXML());
    }
  }

  protected function copyXmlSchemaFromPlugins($prefix = '')
  {
    if($dirs = glob(sfConfig::get('sf_plugins_dir').DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'config'))
    {
      $schemas = sfFinder::type('file')->name('*schema.xml')->in($dirs);
      foreach ($schemas as $schema)
      {
        // reset local prefix
        $localprefix = '';

        // change prefix for plugins
        if (preg_match('#plugins[/\\\\]([^/\\\\]+)[/\\\\]#', $schema, $match))
        {
          // if the plugin name is not in the schema filename, add it
          if (!strstr(basename($schema), $match[1]))
          {
            $localprefix = $match[1].'-';
          }
        }

        // if the prefix is not in the schema filename, add it
        if (!strstr(basename($schema), $prefix))
        {
          $localprefix = $prefix.$localprefix;
        }

        $this->getFilesystem()->copy($schema, 'config'.DIRECTORY_SEPARATOR.$localprefix.basename($schema));
        if ('' === $localprefix)
        {
          $this->getFilesystem()->remove($schema);
        }
      }
    }
  }

  protected function cleanup()
  {
    $finder = sfFinder::type('file')->name('generated-*schema.xml');
    $this->getFilesystem()->remove($finder->in(array('config', 'plugins')));
  }

  protected function callPhing($taskName, $checkSchema)
  {
    $schemas = sfFinder::type('file')->name('*schema.xml')->relative()->follow_link()->in('config');
    if (self::CHECK_SCHEMA === $checkSchema && !$schemas)
    {
      throw new sfCommandException('You must create a schema.yml or schema.xml file.');
    }

    // Call phing targets
    if (false === strpos('propel-generator', get_include_path()))
    {
      set_include_path(sfConfig::get('sf_symfony_lib_dir').'/plugins/sfPropelPlugin/lib/vendor/propel-generator/classes'.PATH_SEPARATOR.get_include_path());
    }
    set_include_path(sfConfig::get('sf_root_dir').PATH_SEPARATOR.get_include_path());

    $args = array();

    // Needed to include the right Propel builders
    set_include_path(sfConfig::get('sf_symfony_lib_dir').PATH_SEPARATOR.get_include_path());

    $options = array(
      'project.dir'       => sfConfig::get('sf_config_dir'),
      'build.properties'  => 'propel.ini',
      'propel.output.dir' => sfConfig::get('sf_root_dir'),
    );
    foreach ($options as $key => $value)
    {
      $args[] = "-D$key=$value";
    }

    // Build file
    $args[] = '-f';
    $args[] = realpath(sfConfig::get('sf_symfony_lib_dir').'/plugins/sfPropelPlugin/lib/vendor/propel-generator/build.xml');

    if (is_null($this->commandApplication) || !$this->commandApplication->isVerbose())
    {
      $args[] = '-q';
    }

    // Logger
    if (DIRECTORY_SEPARATOR != '\\' && (function_exists('posix_isatty') && @posix_isatty(STDOUT)))
    {
      $args[] = '-logger';
      $args[] = 'phing.listener.AnsiColorLogger';
    }

    $args[] = $taskName;

    require_once dirname(__FILE__).'/sfPhing.class.php';

    Phing::startup();
    Phing::setProperty('phing.home', getenv('PHING_HOME'));

    $m = new sfPhing();
    $m->execute($args);
    $m->runBuild();

    chdir(sfConfig::get('sf_root_dir'));
  }
}
