<?php
class AdminGenBrowser extends sfTestBrowser
{
  protected $_modules = array('Article'       => 'articles',
                              'Author'        => 'authors',
                              'Subscription'  => 'subscriptions',
                              'User'          => 'users');

  public function __construct()
  {
    parent::__construct();
    $this->setTester('doctrine', 'sfTesterDoctrine');

    $this->_generateAdminGenModules();
  }

  public function runTests()
  {
    $this->info('Run sfDoctrinePlugin Admin Generator Tests');

    $methods = get_class_methods($this);
    foreach ($methods as $method)
    {
      if (substr($method, 0, 5) == '_test')
      {
        $this->$method();
      }
    }
  }

  protected function _testSanityCheck()
  {
    $this->info('Admin Generator Sanity Checks');

    foreach ($this->_modules as $model => $module)
    {
      $this->_runAdminGenModuleSanityCheck($model, $module);
    }
  }

  protected function _testArticleI18nEmbedded()
  {
    $this->info('Testing "articles" module embeds I18n');

    $i = array('author_id' => 1, 'is_on_homepage' => false, 'en' => array('title' => 'Test English title', 'body' => 'Test English body'), 'fr' => array('title' => 'Test French title', 'body' => 'Test French body'));
    $info = $info ? array_merge($i, $info) : $i;

    $this->
      get('/articles/new')->
        with('response')->begin()->
          contains('En')->
          contains('Fr')->
          contains('Title')->
          contains('Body')->
          contains('Slug')->
          contains('Jonathan H. Wage')->
          contains('Fabien POTENCIER')->
        end()->
        with('request')->begin()->
          isParameter('module', 'articles')->
          isParameter('action', 'new')->
        end()->
      click('Save', array('article' => $info))->
        with('response')->begin()->
          isRedirected()->
          followRedirect()->
        end()->
        with('doctrine')->begin()->
          check('Article', array('is_on_homepage' => $info['is_on_homepage']))->
          check('ArticleTranslation', array('lang' => 'fr', 'title' => 'Test French title'))->
          check('ArticleTranslation', array('lang' => 'en', 'title' => 'Test English title'))->
        end()
    ;
  }

  protected function _testEnumDropdown()
  {
    $this->info('Test enum column type uses a dropdown as the widget');

    $this->
      get('/subscriptions/new')->
        with('response')->begin()->
          checkElement('select', 'NewActivePendingExpired')->
        end()
    ;
  }

  protected function _testUserEmbedsProfileForm()
  {
    $this->info('Test user form embeds the profile form');

    $this->
      get('/users/new')->
        with('response')->begin()->
          contains('Profile')->
          contains('First name')->
          contains('Last name')->
        end()
    ;

    $this->info('Test the Profile form saves and attached to user properly');

    $userInfo = array(
      'user' => array(
        'username'         => 'test',
        'password'         => 'test',
        'groups_list'      => array(1, 2),
        'permissions_list' => array(3, 4),
        'Profile'  => array(
          'first_name' => 'Test',
          'last_name'  => 'Test'
        )
      )
    );

    $this->
      click('Save', $userInfo);

    $user = Doctrine::getTable('User')->findOneByUsername($userInfo['user']['username']);
    $userInfo['user']['Profile']['user_id'] = $user->getId();

    $this->
        with('response')->begin()->
          isRedirected()->
          followRedirect()->
        end()->
        with('doctrine')->begin()->
          check('User', array('username' => 'test'))->
          check('Profile', $userInfo['user']['Profile'])->
          check('UserGroup', array('user_id' => $user->getId(), 'group_id' => 1))->
          check('UserGroup', array('user_id' => $user->getId(), 'group_id' => 2))->
          check('UserPermission', array('user_id' => $user->getId(), 'permission_id' => 3))->
          check('UserPermission', array('user_id' => $user->getId(), 'permission_id' => 4))->
        end()
    ;

    unset($userInfo['user']['Profile']['user_id']);
    $tester = $this->get('/users/new')->
      click('Save', $userInfo)->
      with('form')->begin();
    $tester->hasErrors();
    $form = $tester->getForm();
    $this->test()->is((string) $form->getErrorSchema(), 'An object with the same "username" already exist.', 'Check username gives unique error');
    $tester->end();
  }

  protected function _runAdminGenModuleSanityCheck($model, $module)
  {
    $this->info('Running admin gen sanity check for module "' . $module . '"');
    $record = Doctrine::getTable($model)
      ->createQuery('a')
      ->fetchOne();

    $this->
      info('Sanity check on "' . $module . '" module')->
      getAndCheck($module, 'index', '/' . $module)->
      get('/' . $module . '/' . $record->getId() . '/edit');

    $this
      ->click('Save')->
        with('response')->begin()->
          isRedirected()->
          followRedirect()->
        end()
    ;
  }

  protected function _generateAdminGenModule($model, $module)
  {
    $this->info('Generating admin gen module "' . $module . '"');
    $task = new sfDoctrineGenerateAdminTask($this->getContext()->getEventDispatcher(), new sfFormatter());
    $task->run(array('application' => 'backend', 'route_or_model' => $model));
  }

  protected function _generateAdminGenModules()
  {
    // Generate the admin generator modules
    foreach ($this->_modules as $model => $module)
    {
      $this->_generateAdminGenModule($model, $module);
    }
  }

  protected function _cleanupAdminGenModules()
  {
    $fs = new sfFilesystem($this->getContext()->getEventDispatcher(), new sfFormatter());
    foreach ($this->_modules as $module)
    {
      $this->info('Removing admin gen module "' . $module . '"');
      $fs->sh('rm -rf ' . sfConfig::get('sf_app_module_dir') . '/' . $module);
    }
    $fs->sh('rm -rf ' . sfConfig::get('sf_test_dir') . '/functional/backend');
  }

  public function __destruct()
  {
    $this->_cleanupAdminGenModules();
  }
}