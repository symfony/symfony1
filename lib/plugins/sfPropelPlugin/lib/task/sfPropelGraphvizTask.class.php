<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfPropelBaseTask.class.php');

/**
 * Generates a graphviz chart of current object model.
 *
 * @package    symfony
 * @subpackage propel
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfPropelGraphvizTask.class.php 5506 2007-10-14 10:28:15Z dwhittle $
 */
class sfPropelGraphvizTask extends sfPropelBaseTask
{
  /**
   * @see BaseTask::configure()
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name'),
      new sfCommandOption('output-dir', 'o', sfCommandOption::PARAMETER_OPTIONAL, 'Output directory for graph', sfConfig::get('sf_data_dir').'/graph'),
      new sfCommandOption('dot-path', null, sfCommandOption::PARAMETER_OPTIONAL, 'The path to the dot command.', 'dot'),
      new sfCommandOption('format', null, sfCommandOption::PARAMETER_OPTIONAL, 'Export graphviz chart to dot, png, jpg, gif, svg, or pdf format.', 'dot'),
      new sfCommandOption('phing-arg', null, sfCommandOption::PARAMETER_REQUIRED | sfCommandOption::IS_ARRAY, 'Arbitrary phing argument'),
    ));

    $this->namespace = 'propel';
    $this->name = 'graphviz';
    $this->briefDescription = 'Generates a graphviz chart of current object model';
    $this->detailedDescription = <<<EOF
The [propel:graphviz|INFO] task creates a graphviz DOT
visualization for automatic graph drawing of object model:

  [./symfony propel:graphviz|INFO]

By default, the task use the [propel|COMMENT] connection as defined in [config/databases.yml|COMMENT].
You can use another connection by using the [connection|COMMENT] option:

  [./symfony propel:graphviz --connection="name"|INFO]

If you want to use a specific database configuration from an application, you can use
the [application|COMMENT] option:

  [./symfony propel:graphviz --application=frontend --format=png|INFO]
EOF;
  }

  /**
   * @see BaseTask::execute()
   */
  protected function execute($arguments = array(), $options = array())
  {
    $ret = false;
    $this->schemaToXML(self::DO_NOT_CHECK_SCHEMA, 'generated-');
    $this->copyXmlSchemaFromPlugins('generated-');

    // load Propel configuration before Phing
    $databaseManager = new sfDatabaseManager($this->configuration);

    register_shutdown_function(array($this, 'removeTmpDir'));

    $properties = $this->getProperties(sfConfig::get('sf_data_dir').'/sql/sqldb.map');
    $sqls = array();
    foreach ($properties as $file => $connection)
    {
      if (!is_null($options['connection']) && $options['connection'] != $connection)
      {
        continue;
      }

      if (!isset($sqls[$connection]))
      {
        $sqls[$connection] = array();
      }

      $sqls[$connection][] = $file;
    }

    $this->tmpDir = sfToolkit::getTmpDir().'/propel_insert_sql_'.rand(11111, 99999);
    mkdir($this->tmpDir, 0777, true);
    foreach ($sqls as $connection => $files)
    {
      $dir = $this->tmpDir.'/'.$connection;
      mkdir($dir, 0777, true);

      $content = '';
      foreach ($files as $file)
      {
        $content .= "$file=$connection\n";
        copy(sfConfig::get('sf_data_dir').'/sql/'.$file, $dir.'/'.$file);
      }

      file_put_contents($dir.'/sqldb.map', $content);
      $properties = $this->getPhingPropertiesForConnection($databaseManager, $connection);
      $properties['propel.sql.dir'] = $dir;

    	$ret = $this->callPhing('graphviz', self::CHECK_SCHEMA, array_merge($properties, array('propel.graph.dir' => $options['output-dir'])));
      if($ret && in_array($options['format'], array('jpg', 'png', 'gif', 'svg', 'pdf')))
      {
        $this->getFilesystem()->sh(sprintf('%s -T%s "%s/%s.schema.dot" -o "%s/%s.schema.%s"', $options['dot-path'], $options['format'], $options['output-dir'], $connection, $options['output-dir'], $connection, $options['format']));
      }
    }

    $this->removeTmpDir();

    $this->cleanup();

    return !$ret;
  }

  public function removeTmpDir()
  {
    if (!is_dir($this->tmpDir))
    {
      return;
    }

    sfToolkit::clearDirectory($this->tmpDir);
    rmdir($this->tmpDir);
  }
}
