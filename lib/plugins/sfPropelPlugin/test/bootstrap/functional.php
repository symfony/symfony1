<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// we need sqlite for functional tests
if (!extension_loaded('SQLite'))
{
  return false;
}

if (!isset($root_dir))
{
  $root_dir = realpath(dirname(__FILE__).sprintf('/../%s/fixtures', isset($type) ? $type : 'functional'));
}

$class = $app.'Configuration';
require $root_dir.'/lib/'.$class.'.class.php';
$configuration = new $class('test', isset($debug) ? $debug : true);
sfContext::createInstance($configuration);

// remove all cache
sfToolkit::clearDirectory(sfConfig::get('sf_app_cache_dir'));

// build Propel om/map/sql/forms
$files = glob(sfConfig::get('sf_lib_dir').'/model/om/*.php');
if (false === $files || !count($files))
{
  chdir(sfConfig::get('sf_root_dir'));
  $task = new sfPropelBuildModelTask(new sfEventDispatcher(), new sfFormatter());
  ob_start();
  $task->run();
  $output = ob_get_clean();
}

$files = glob(sfConfig::get('sf_data_dir').'/sql/*.php');
if (false === $files || !count($files))
{
  chdir(sfConfig::get('sf_root_dir'));
  $task = new sfPropelBuildSqlTask(new sfEventDispatcher(), new sfFormatter());
  ob_start();
  $task->run();
  $output = ob_get_clean();
}

$files = glob(sfConfig::get('sf_lib_dir').'/form/base/*.php');
if (false === $files || !count($files))
{
  chdir(sfConfig::get('sf_root_dir'));
  $task = new sfPropelBuildFormsTask(new sfEventDispatcher(), new sfFormatter());
  $task->run();
}

if (isset($fixtures))
{
  // initialize database manager
  $databaseManager = new sfDatabaseManager($configuration);

  // cleanup database
  $db = sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'/database.sqlite';
  if (file_exists($db))
  {
    unlink($db);
  }

  // initialize database
  $sql = file_get_contents(sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'lib.model.schema.sql');
  $sql = preg_replace('/^\s*\-\-.+$/m', '', $sql);
  $sql = preg_replace('/^\s*DROP TABLE .+?$/m', '', $sql);
  $con = Propel::getConnection();
  $tables = preg_split('/CREATE TABLE/', $sql);
  foreach ($tables as $table)
  {
    $table = trim($table);
    if (!$table)
    {
      continue;
    }

    $con->executeQuery('CREATE TABLE '.$table);
  }

  // load fixtures
  $data = new sfPropelData();
  if (is_array($fixtures))
  {
    $data->loadDataFromArray($fixtures);
  }
  else
  {
    $data->loadData(sfConfig::get('sf_data_dir').'/'.$fixtures);
  }
}

return true;
