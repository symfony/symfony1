<?php

require_once 'symfony/test/sfLiveProjectUnitTestCase.class.php';

class propelCrudGeneratorTest extends sfLiveProjectUnitTestCase
{
  public function test_simple()
  {
    // copy schema
    copy($this->getFixturesDir().'/config/schema.xml', sfConfig::get('sf_config_dir').'/schema.xml');

    // copy fixtures data
    copy($this->getFixturesDir().'/data/fixtures.yml', sfConfig::get('sf_data_dir').'/fixtures.yml');

    // copy databases.yml configuration file
    // fix db path and switch to sqlite
    $orm_config = file_get_contents($this->getFixturesDir().'/config/databases.yml');
    $orm_config = preg_replace('/##DB_PATH##/', sfConfig::get('sf_data_dir').'/test.db', $orm_config);
    file_put_contents(sfConfig::get('sf_app_config_dir').'/databases.yml', $orm_config);

    // switch to sqlite
    $propel_config = file_get_contents(sfConfig::get('sf_config_dir').'/propel.ini');
    $propel_config = preg_replace('/mysql/', 'sqlite', $propel_config);
    $propel_config = preg_replace('/propel.database.url\s*=\s*.+$/m', 'propel.database.url = sqlite://localhost/'.sfConfig::get('sf_data_dir').'/test.db', $propel_config);
    file_put_contents(sfConfig::get('sf_config_dir').'/propel.ini', $propel_config);

    // build Propel object classes
    $this->runSymfony('build-model');
    $this->runSymfony('build-sql');

    // force autoload classes regeneration
    $autoload_config_file = sfConfig::get('sf_app_config_dir_name').'/autoload.yml';
    if (is_readable($autoload_config_file))
    {
      unlink(sfConfigCache::getCacheName($autoload_config_file));
    }
    require(sfConfigCache::checkConfig($autoload_config_file));

    // create database
    $this->runSymfony('insert-sql');

    // populate with some fixture data
    $data = new sfPropelData();
    $data->loadData(sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'fixtures.yml');

    // initialize scaffolding
    $this->runSymfony('init-propelcrud app posti Post');

    // generate scaffolding
    $this->runSymfony('generate-propelcrud app postg Post');

    // tests

    // list
    $this->checkModuleResponse('/postg/list', array('/This is my first post/', '/create/'));

    // edit id=1
    $this->checkModuleResponse('/postg/edit/id/1', array('/This is my first post/', '/delete/', '/cancel/'));

    // delete id=1
    $this->checkModuleResponse('/postg/delete/id/1');

    // id=1 is gone
    $this->checkModuleResponse('/postg/edit/id/1', array('/404/'));

    // list
    $this->checkModuleResponse('/posti/list', array('/This is my second post/', '/create/'));

    // edit id=2
    $this->checkModuleResponse('/posti/edit/id/2', array('/This is my second post/', '/delete/', '/cancel/'));

    // delete id=2
    $this->checkModuleResponse('/posti/delete/id/2');

    // id=2 is gone
    $this->checkModuleResponse('/posti/edit/id/2', array('/404/'));
  }

  public function getFixturesDir()
  {
    return dirname(__FILE__).DIRECTORY_SEPARATOR.'fixtures';
  }
}

?>