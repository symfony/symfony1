<?php

pake_desc('launch symfony web server');
pake_task('server');

function run_server($task, $args)
{
  if (!count($args))
  {
    throw new Exception('You must provide the app to serve.');
  }

  $app = $args[0];
  $port = isset($args[1]) ? $args[1] : 8000;

  define('SF_ROOT_DIR',    sfConfig::get('sf_root_dir'));
  define('SF_APP',         $app);
  define('SF_ENVIRONMENT', 'dev');
  define('SF_DEBUG',       true);

  require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');

  $browser = new sfTestBrowser();
  $browser->initialize();

  $config = array(
    'doc_root' => sfConfig::get('sf_web_dir'),
    'browser'  => $browser,
  );

  sfConfig::set('sf_factory_storage',   'sfSessionTestStorage');
  sfConfig::set('sf_no_script_name',    true);
  sfConfig::set('sf_relative_url_root', '');

  require_once('symfony/vendor/nanoserv/nanoserv.php');

  $l = Nanoserv::New_Listener('tcp://0.0.0.0:'.$port, 'sfWebServer', $config);
  $l->Set_Forking();
  $l->Activate();

  Nanoserv::Run();
}
