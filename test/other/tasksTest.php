<?php

$_test_dir = realpath(dirname(__FILE__).'/..');
require_once($_test_dir.'/../lib/vendor/lime/lime.php');

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

    require_once('System.php');
    $this->tmp_dir = System::tmpdir().DIRECTORY_SEPARATOR.'symfony_cmd';

    if (is_dir($this->tmp_dir))
    {
      $this->clearTmpDir();
      rmdir($this->tmp_dir);
    }

    mkdir($this->tmp_dir, 0777);

    $this->current_dir = getcwd();
    chdir($this->tmp_dir);

    // php cli
    $this->php_cli = PHP_BINDIR.DIRECTORY_SEPARATOR.'php';
    if (!is_executable($this->php_cli))
    {
      require_once('System.php');
      $this->php_cli = System::which('php');

      if (!is_executable($this->php_cli))
      {
        throw new Exception("Unable to find PHP executable.");
      }
    }
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
    passthru(sprintf('%s %s %s 2>&1', $this->php_cli, dirname(__FILE__).'/../../data/bin/symfony.php', $cmd), $return);
    $content = ob_get_clean();
    $this->t->cmp_ok($return, '<=', 0, sprintf('"symfony %s" returns ok', $cmd));

    return $content;
  }
}

$t = new lime_test(19, new lime_output_color());
$c = new symfony_cmd();
$c->initialize($t);

$t->is($c->execute_command('-T'), $c->execute_command(''), '"symfony" is an alias for "symfony -T"');

$content = $c->execute_command('init-project myproject');
$t->ok(file_exists($c->tmp_dir.DS.'SYMFONY'), '"init-project" creates a SYMFONY file in root project directory');

$content = $c->execute_command('init-app frontend');
$t->ok(is_dir($c->tmp_dir.DS.'apps'.DS.'frontend'), '"init-app" creates a "frontend" directory under "apps" directory');

$content = $c->execute_command('init-module frontend foo');
$t->ok(is_dir($c->tmp_dir.DS.'apps'.DS.'frontend'.DS.'modules'.DS.'foo'), '"init-module" creates a "foo" directory under "modules" directory');

copy(dirname(__FILE__).'/fixtures/schema.yml', $c->tmp_dir.DS.'config'.DS.'schema.yml');

$content = $c->execute_command('propel-build-sql');
$t->ok(file_exists($c->tmp_dir.DS.'data'.DS.'sql'.DS.'lib.model.schema.sql'), '"propel-build-sql" creates a "schema.sql" file under "data/sql" directory');

$content = $c->execute_command('propel-build-model');
$t->ok(file_exists($c->tmp_dir.DS.'lib'.DS.'model'.DS.'Article.php'), '"propel-build-model" creates model classes under "lib/model" directory');

$content = $c->execute_command('propel-init-crud frontend articleInitCrud Article');
$t->ok(file_exists($c->tmp_dir.DS.'apps'.DS.'frontend'.DS.'modules'.DS.'articleInitCrud'.DS.'config'.DS.'generator.yml'), '"propel-init-crud" initializes a CRUD module');

$content = $c->execute_command('propel-generate-crud frontend articleGenCrud Article');
$t->ok(is_dir($c->tmp_dir.DS.'apps'.DS.'frontend'.DS.'modules'.DS.'articleGenCrud'), '"propel-generate-crud" generates a CRUD module');

$content = $c->execute_command('propel-init-admin frontend articleInitAdmin Article');
$t->ok(file_exists($c->tmp_dir.DS.'apps'.DS.'frontend'.DS.'modules'.DS.'articleInitAdmin'.DS.'config'.DS.'generator.yml'), '"propel-init-admin" initializes an admin generator module');

$c->shutdown();
