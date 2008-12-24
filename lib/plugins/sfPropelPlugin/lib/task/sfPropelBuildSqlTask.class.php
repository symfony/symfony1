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
 * Create SQL for the current model.
 *
 * @package    symfony
 * @subpackage propel
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelBuildSqlTask extends sfPropelBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name'),
      new sfCommandOption('phing-arg', null, sfCommandOption::PARAMETER_REQUIRED | sfCommandOption::IS_ARRAY, 'Arbitrary phing argument'),
    ));

    $this->aliases = array('propel-build-sql');
    $this->namespace = 'propel';
    $this->name = 'build-sql';
    $this->briefDescription = 'Creates SQL for the current model';

    $this->detailedDescription = <<<EOF
The [propel:build-sql|INFO] task creates SQL statements for table creation:

  [./symfony propel:build-sql|INFO]

The task read the database configuration from `databases.yml`.
You can use a specific application/environment by passing
an [--application|INFO] or [--env|INFO] option.

You can also use the [--connection|INFO] option if you want to
only load SQL statements for a given connection.

The generated SQL is optimized for the database configured in [config/propel.ini|COMMENT]:

  [propel.database = mysql|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
		$ret = false;
    $this->schemaToXML(self::DO_NOT_CHECK_SCHEMA, 'generated-');
    $this->copyXmlSchemaFromPlugins('generated-');

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

      $ret = $this->callPhing('sql', self::CHECK_SCHEMA, $properties);
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
