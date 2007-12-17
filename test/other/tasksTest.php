<?php

$_test_dir = realpath(dirname(__FILE__).'/..');
require_once($_test_dir.'/../lib/vendor/lime/lime.php');
require_once($_test_dir.'/../lib/util/sfToolkit.class.php');

define('DS', DIRECTORY_SEPARATOR);

class symfony_cmd
{
  public $php_cli = null;
  public $tmp_dir = null;
  public $t = null;
  public $current_dir = null;

  public function initialize($t)
  {
    $this->t = $t;

    $this->tmp_dir = sfToolkit::getTmpDir().DIRECTORY_SEPARATOR.'symfony_cmd';

    if (is_dir($this->tmp_dir))
    {
      $this->clearTmpDir();
      rmdir($this->tmp_dir);
    }

    mkdir($this->tmp_dir, 0777);

    $this->current_dir = getcwd();
    chdir($this->tmp_dir);

    $this->php_cli = sfToolkit::getPhpCli();
  }

  public function shutdown()
  {
    $this->clearTmpDir();
    rmdir($this->tmp_dir);
    chdir($this->current_dir);
  }

  protected function clearTmpDir()
  {
    require_once(dirname(__FILE__).'/../../lib/util/sfToolkit.class.php');
    sfToolkit::clearDirectory($this->tmp_dir);
  }

  public function execute_command($cmd)
  {
    ob_start();
    passthru(sprintf('%s -d html_errors=off -d open_basedir= -q "%s" %s 2>&1', $this->php_cli, dirname(__FILE__).'/../../data/bin/symfony', $cmd), $return);
    $content = ob_get_clean();
    $this->t->cmp_ok($return, '<=', 0, sprintf('"symfony %s" returns ok', $cmd));

    return $content;
  }
}

$t = new lime_test(35, new lime_output_color());
$c = new symfony_cmd();
$c->initialize($t);

$t->is($c->execute_command('-T'), $c->execute_command(''), '"symfony" is an alias for "symfony -T"');

// generate:*
$content = $c->execute_command('generate:project myproject');
$t->ok(file_exists($c->tmp_dir.DS.'symfony'), '"generate:project" installs the symfony CLI in root project directory');

$content = $c->execute_command('generate:app frontend');
$t->ok(is_dir($c->tmp_dir.DS.'apps'.DS.'frontend'), '"generate:app" creates a "frontend" directory under "apps" directory');

$content = $c->execute_command('generate:module frontend foo');
$t->ok(is_dir($c->tmp_dir.DS.'apps'.DS.'frontend'.DS.'modules'.DS.'foo'), '"generate:module" creates a "foo" directory under "modules" directory');

copy(dirname(__FILE__).'/fixtures/propel/schema.yml', $c->tmp_dir.DS.'config'.DS.'schema.yml');
copy(dirname(__FILE__).'/fixtures/propel/databases.yml', $c->tmp_dir.DS.'config'.DS.'databases.yml');
copy(dirname(__FILE__).'/fixtures/propel/propel.ini', $c->tmp_dir.DS.'config'.DS.'propel.ini');

// propel:*
$content = $c->execute_command('propel:build-sql');
$t->ok(file_exists($c->tmp_dir.DS.'data'.DS.'sql'.DS.'lib.model.schema.sql'), '"propel:build-sql" creates a "schema.sql" file under "data/sql" directory');

$content = $c->execute_command('propel:build-model');
$t->ok(file_exists($c->tmp_dir.DS.'lib'.DS.'model'.DS.'Article.php'), '"propel:build-model" creates model classes under "lib/model" directory');

$c->execute_command('propel:insert-sql');
$file = dirname(__FILE__).DS.'..'.DS.'..'.DS.'lib'.DS.'plugins'.DS.'sfPropelPlugin'.DS.'lib'.DS.'vendor'.DS.'propel-generator'.DS.'database.sqlite';
$t->ok(file_exists($file), '"propel:insert-sql" creates tables in the database');
rename($file, $c->tmp_dir.'/data/database.sqlite');

$content = $c->execute_command('propel:init-crud frontend articleInitCrud Article');
$t->ok(file_exists($c->tmp_dir.DS.'apps'.DS.'frontend'.DS.'modules'.DS.'articleInitCrud'.DS.'config'.DS.'generator.yml'), '"propel:init-crud" initializes a CRUD module');

$content = $c->execute_command('propel:generate-crud frontend articleGenCrud Article');
$t->ok(is_dir($c->tmp_dir.DS.'apps'.DS.'frontend'.DS.'modules'.DS.'articleGenCrud'), '"propel:generate-crud" generates a CRUD module');

$content = $c->execute_command('propel:init-admin frontend articleInitAdmin Article');
$t->ok(file_exists($c->tmp_dir.DS.'apps'.DS.'frontend'.DS.'modules'.DS.'articleInitAdmin'.DS.'config'.DS.'generator.yml'), '"propel:init-admin" initializes an admin generator module');

// test:*
$content = $c->execute_command('test:functional frontend articleInitCrudActions');
$t->is($content, str_replace("\r\n", "\n", file_get_contents(dirname(__FILE__).'/fixtures/test/functional/result.txt')), '"test:functional" can launch a particular functional test');

$content = $c->execute_command('test:functional frontend');
$t->is($content, str_replace("\r\n", "\n", file_get_contents(dirname(__FILE__).'/fixtures/test/functional/result-harness.txt')), '"test:functional" can launch all functional tests');

copy(dirname(__FILE__).'/fixtures/test/unit/testTest.php', $c->tmp_dir.DS.'test'.DS.'unit'.DS.'testTest.php');

$content = $c->execute_command('test:unit test');
$t->is($content, str_replace("\r\n", "\n", file_get_contents(dirname(__FILE__).'/fixtures/test/unit/result.txt')), '"test:unit" can launch a particular unit test');

$content = $c->execute_command('test:unit');
$t->is($content, str_replace("\r\n", "\n", file_get_contents(dirname(__FILE__).'/fixtures/test/unit/result-harness.txt')), '"test:unit" can launch all unit tests');

$content = $c->execute_command('test:all');
$t->is($content, str_replace("\r\n", "\n", file_get_contents(dirname(__FILE__).'/fixtures/test/result-harness.txt')), '"test:all" launches all unit and functional tests');

$content = $c->execute_command('project:freeze');
$t->like(file_get_contents($c->tmp_dir.DS.'config'.DS.'config.php'), '/dirname\(__FILE__\)/', '"project:freeze" freezes symfony lib and data dir into the project directory');

$content = $c->execute_command('project:unfreeze');
$t->unlike(file_get_contents($c->tmp_dir.DS.'config'.DS.'config.php'), '/dirname\(__FILE__\)/', '"project:unfreeze" unfreezes symfony lib and data dir');

$c->shutdown();
