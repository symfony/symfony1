<?php

/*
    $context = $this->browser->initRequest('/');
    $html = $this->browser->getContent();
    $this->browser->closeRequest();
*/

require_once 'symfony/util/sfToolkit.class.php';
require_once 'symfony/test/sfTestBrowser.class.php';

if (!function_exists('pake_task'))
{
  //require_once 'pake.php';
  // FIXME
  require_once dirname(__FILE__).'/../../../pake/bin/pake.php';
}

class sfLiveAppTest extends UnitTestCase
{
  private
    $current_dir = '',
    $tmp_dir     = null,
    $browser     = null;

  public function __construct()
  {
    $this->current_dir = getcwd();

    // sandbox initialization
    $root_dir = '/tmp/symfonylivetest';
    $this->tmp_dir = $root_dir.'/'.md5(uniqid(rand(), true));
    if (!is_dir($root_dir))
    {
      mkdir($root_dir, 0777);
    }
    mkdir($this->tmp_dir, 0777);
    chdir($this->tmp_dir);

    // symfony pakefile
    $pakefile = dirname(__FILE__).'/../../data/symfony/bin/pakefile.php';

    // create a new symfony project
    ob_start();
    pakeApp::get_instance()->run($pakefile, 'init-project liveapp');
    $ret = ob_get_clean();

    // create a new symfony application
    ob_start();
    pakeApp::get_instance()->run($pakefile, 'init-app app');
    $ret = ob_get_clean();

    // create a new symfony module
    ob_start();
    pakeApp::get_instance()->run($pakefile, 'init-module app module');
    $ret = ob_get_clean();

    // initialize our testing environment
    define('SF_ROOT_DIR',    $this->tmp_dir);
    define('SF_APP',         'app');
    define('SF_ENVIRONMENT', 'test');
    define('SF_DEBUG',       true);
    define('SF_TEST',        true);

    // save current error_reporting level
    $error_reporting = error_reporting();

    // get configuration
    require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');

    // change error_reporting because simpletest is not PHP5 E_STRICT compliant
    // FIXME
    error_reporting($error_reporting);

    chdir($this->current_dir);

    register_shutdown_function(array($this, 'shutdown'));
  }

  public function shutdown()
  {
    // remove all temporary files and directories
    sfToolkit::clearDirectory($this->tmp_dir);
    rmdir($this->tmp_dir);
  }

  public function SetUp()
  {
    // initialize our test browser
    $this->browser = new sfTestBrowser();
    $this->browser->initialize('liveapp');
  }

  public function tearDown()
  {
    $this->browser->shutdown();
  }

  public function test_simple()
  {
    // check symfony default module response on a new project
    $html = $this->browser->get('/');
    $this->assertWantedPattern('/congratulations/i', $html);

    // check our module response
    $html = $this->browser->get('/module/index.html');
    $this->assertWantedPattern('/congratulation/i', $html);
  }

  public function test_action_doesnotexist()
  {
    // action does not exists
    $html = $this->browser->get('/module/nonexistantaction.html');

    $this->assertWantedPattern('/couldn\'t find this page/', $html);
  }

  public function test_sfAction()
  {
    // test sfAction action
    $text = 'this is testAction action response';
    $data = <<<EOF
<?php
  class testAction extends sfAction
  {
    public function execute()
    {
      \$this->content = "$text";
    }
  }
?>
EOF;
    file_put_contents($this->tmp_dir.'/app/modules/module/actions/testAction.class.php', $data);

    // test sfAction template
    $data = <<<EOF
    <?php print \$content ?>
EOF;
    file_put_contents($this->tmp_dir.'/app/modules/module/templates/testSuccess.php', $data);

    $html = $this->browser->get('/module/test.html');
    $this->assertWantedPattern('/'.$text.'/', $html);
  }

  // TODO
  public function test_scaffold()
  {
/*
    $data = <<<EOF
<?php
  class anothertemplateAction extends sfScaffoldActions
  {
    public function initAction()
    {
      \$this->setScaffoldingClassName('Test');
    }
  }
?>
EOF;
    file_put_contents($this->tmp_dir.'/app/modules/module/actions/anothertemplateAction.class.php', $data);

    // templates
    $data = 'OK yetanothertemplate';
    file_put_contents($this->tmp_dir.'/app/modules/module/templates/yetanothertemplateSuccess.php', $data);

    $html = $this->browser->get('/module/anothertemplate.html');
    $this->assertWantedPattern('/OK yetanothertemplate/', $html);
*/
  }

  public function test_global_template()
  {
    // create 2 actions:
    // - one with layout
    // - one without
    $data = <<<EOF
<?php
  class layoutAction extends sfAction
  {
    public function execute()
    {
    }
  }
?>
EOF;
file_put_contents($this->tmp_dir.'/app/modules/module/actions/layoutAction.class.php', $data);

    $data = <<<EOF
<?php
  class nolayoutAction extends sfAction
  {
    public function execute()
    {
    }
  }
?>
EOF;
    file_put_contents($this->tmp_dir.'/app/modules/module/actions/nolayoutAction.class.php', $data);

    $data = <<<EOF
nolayoutSuccess:
  has_layout: off

all:
  has_layout: on
EOF;
    file_put_contents($this->tmp_dir.'/app/modules/module/config/view.yml', $data);

    // templates
    $data = 'OK';
    file_put_contents($this->tmp_dir.'/app/modules/module/templates/layoutSuccess.php', $data);
    file_put_contents($this->tmp_dir.'/app/modules/module/templates/nolayoutSuccess.php', $data);

    // with layout
    $html = $this->browser->get('/module/layout.html');
//    $this->assertWantedPattern('/<title>/', $html);

    // without layout
    $html = $this->browser->get('/module/nolayout.html');
//    $this->assertNoUnwantedPattern('/<title>/', $html);
  }

  public function test_change_template()
  {
    $data = <<<EOF
<?php
  class anothertemplateAction extends sfAction
  {
    public function execute()
    {
      \$this->setTemplate('yetanothertemplate');
    }
  }
?>
EOF;
    file_put_contents($this->tmp_dir.'/app/modules/module/actions/anothertemplateAction.class.php', $data);

    // templates
    $data = 'OK yetanothertemplate';
    file_put_contents($this->tmp_dir.'/app/modules/module/templates/yetanothertemplateSuccess.php', $data);

    $html = $this->browser->get('/module/anothertemplate.html');
    $this->assertWantedPattern('/OK yetanothertemplate/', $html);
  }

  public function test_error404()
  {
    // test 404 default message
    $html = $this->browser->get('/nonexistantpage.html');
    $this->assertWantedPattern('/couldn\'t find this page/', $html);

    // test 404 personnalization / overriding templates without module duplication
    $data = "Page does not exist. Sorry! You must see this symfony error message";
    mkdir($this->tmp_dir.'/app/modules/default');
    mkdir($this->tmp_dir.'/app/modules/default/templates');
    file_put_contents($this->tmp_dir.'/app/modules/default/templates/error404Success.php', $data);

    $html = $this->browser->get('/nonexistantpage.html');
    $this->assertWantedPattern('/You must see this symfony error message/', $html);
  }
}

?>