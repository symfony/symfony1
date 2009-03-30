<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The current symfony version.
 */
define('SYMFONY_VERSION', '1.3.0-DEV');

/**
 * sfCoreAutoload class.
 *
 * This class is a singleton as PHP seems to be unable to register 2 autoloaders that are instances
 * of the same class (why?).
 *
 * @package    symfony
 * @subpackage autoload
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfCoreAutoload
{
  static protected
    $registered = false,
    $instance   = null;

  protected
    $baseDir = '';

  protected function __construct()
  {
    $this->baseDir = realpath(dirname(__FILE__).'/..').'/';
  }

  /**
   * Retrieves the singleton instance of this class.
   *
   * @return sfCoreAutoload A sfCoreAutoload implementation instance.
   */
  static public function getInstance()
  {
    if (!isset(self::$instance))
    {
      self::$instance = new sfCoreAutoload();
    }

    return self::$instance;
  }

  /**
   * Register sfCoreAutoload in spl autoloader.
   *
   * @return void
   */
  static public function register()
  {
    if (self::$registered)
    {
      return;
    }

    ini_set('unserialize_callback_func', 'spl_autoload_call');
    if (false === spl_autoload_register(array(self::getInstance(), 'autoload')))
    {
      throw new sfException(sprintf('Unable to register %s::autoload as an autoloading method.', get_class(self::getInstance())));
    }

    self::$registered = true;
  }

  /**
   * Unregister sfCoreAutoload from spl autoloader.
   *
   * @return void
   */
  static public function unregister()
  {
    spl_autoload_unregister(array(self::getInstance(), 'autoload'));
  }

  /**
   * Handles autoloading of classes.
   *
   * @param  string  $class  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  public function autoload($class)
  {
    $class = strtolower($class);

    if (!isset($this->classes[$class]))
    {
      return false;
    }

    require $this->baseDir.$this->classes[$class][0].'/'.$class.'.'.$this->classes[$class][1];

    return true;
  }

  /**
   * Returns the base directory this autoloader is working on.
   *
   * @return base directory
   */
   public function getBaseDir()
   {
     return $this->baseDir;
   }

  /**
   * Rebuilds the association array between class names and paths.
   *
   * This method overrides this file (__FILE__)
   */
  static public function make()
  {
    $libDir = str_replace(DIRECTORY_SEPARATOR, '/', realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'));
    require_once $libDir.'/util/sfFinder.class.php';

    $files = sfFinder::type('file')
      ->prune('plugins')
      ->prune('vendor')
      ->prune('skeleton')
      ->prune('default')
      ->prune('helper')
      ->name('*.php')
      ->in($libDir)
    ;

    sort($files, SORT_STRING);

    $classes = '';
    foreach ($files as $file)
    {
      $dir = str_replace(DIRECTORY_SEPARATOR, '/', dirname($file));
      $dir = $libDir == $dir ? '' : str_replace($libDir.'/', '', $dir);
      if (false !== strpos($file, '.class.php'))
      {
        $classes .= sprintf("    '%s' => array('%s', '%s'),\n", strtolower(basename($file, '.class.php')), $dir, 'class.php');
      }
      else
      {
        $classes .= sprintf("    '%s' => array('%s', '%s'),\n", strtolower(basename($file, '.php')), $dir, 'php');
      }
    }

    $content = preg_replace('/protected \$classes = array *\(.*?\);/s', sprintf("protected \$classes = array(\n%s\n  );", $classes), file_get_contents(__FILE__));

    file_put_contents(__FILE__, $content);
  }

  // Don't edit this property by hand.
  // To update it, use sfCoreAutoload::make()
  protected $classes = array(
    'sfaction' => array('action', 'class.php'),
    'sfactionstack' => array('action', 'class.php'),
    'sfactionstackentry' => array('action', 'class.php'),
    'sfactions' => array('action', 'class.php'),
    'sfcomponent' => array('action', 'class.php'),
    'sfcomponents' => array('action', 'class.php'),
    'sfdata' => array('addon', 'class.php'),
    'sfpager' => array('addon', 'class.php'),
    'sfautoload' => array('autoload', 'class.php'),
    'sfautoloadagain' => array('autoload', 'class.php'),
    'sfcoreautoload' => array('autoload', 'class.php'),
    'sfsimpleautoload' => array('autoload', 'class.php'),
    'sfapccache' => array('cache', 'class.php'),
    'sfcache' => array('cache', 'class.php'),
    'sfeacceleratorcache' => array('cache', 'class.php'),
    'sffilecache' => array('cache', 'class.php'),
    'sffunctioncache' => array('cache', 'class.php'),
    'sfmemcachecache' => array('cache', 'class.php'),
    'sfnocache' => array('cache', 'class.php'),
    'sfsqlitecache' => array('cache', 'class.php'),
    'sfxcachecache' => array('cache', 'class.php'),
    'cli' => array('command', 'php'),
    'sfansicolorformatter' => array('command', 'class.php'),
    'sfcommandapplication' => array('command', 'class.php'),
    'sfcommandargument' => array('command', 'class.php'),
    'sfcommandargumentset' => array('command', 'class.php'),
    'sfcommandargumentsexception' => array('command', 'class.php'),
    'sfcommandexception' => array('command', 'class.php'),
    'sfcommandlogger' => array('command', 'class.php'),
    'sfcommandmanager' => array('command', 'class.php'),
    'sfcommandoption' => array('command', 'class.php'),
    'sfcommandoptionset' => array('command', 'class.php'),
    'sfformatter' => array('command', 'class.php'),
    'sfsymfonycommandapplication' => array('command', 'class.php'),
    'sfapplicationconfiguration' => array('config', 'class.php'),
    'sfautoloadconfighandler' => array('config', 'class.php'),
    'sfcacheconfighandler' => array('config', 'class.php'),
    'sfcompileconfighandler' => array('config', 'class.php'),
    'sfconfig' => array('config', 'class.php'),
    'sfconfigcache' => array('config', 'class.php'),
    'sfconfighandler' => array('config', 'class.php'),
    'sfdatabaseconfighandler' => array('config', 'class.php'),
    'sfdefineenvironmentconfighandler' => array('config', 'class.php'),
    'sffactoryconfighandler' => array('config', 'class.php'),
    'sffilterconfighandler' => array('config', 'class.php'),
    'sfgeneratorconfighandler' => array('config', 'class.php'),
    'sfloader' => array('config', 'class.php'),
    'sfpluginconfiguration' => array('config', 'class.php'),
    'sfpluginconfigurationgeneric' => array('config', 'class.php'),
    'sfprojectconfiguration' => array('config', 'class.php'),
    'sfrootconfighandler' => array('config', 'class.php'),
    'sfroutingconfighandler' => array('config', 'class.php'),
    'sfsecurityconfighandler' => array('config', 'class.php'),
    'sfsimpleyamlconfighandler' => array('config', 'class.php'),
    'sfviewconfighandler' => array('config', 'class.php'),
    'sfyamlconfighandler' => array('config', 'class.php'),
    'sfconsolecontroller' => array('controller', 'class.php'),
    'sfcontroller' => array('controller', 'class.php'),
    'sffrontwebcontroller' => array('controller', 'class.php'),
    'sfwebcontroller' => array('controller', 'class.php'),
    'sfdatabase' => array('database', 'class.php'),
    'sfdatabasemanager' => array('database', 'class.php'),
    'sfmysqldatabase' => array('database', 'class.php'),
    'sfmysqlidatabase' => array('database', 'class.php'),
    'sfpdodatabase' => array('database', 'class.php'),
    'sfpostgresqldatabase' => array('database', 'class.php'),
    'sfdebug' => array('debug', 'class.php'),
    'sftimer' => array('debug', 'class.php'),
    'sftimermanager' => array('debug', 'class.php'),
    'sfwebdebug' => array('debug', 'class.php'),
    'sfwebdebugpanel' => array('debug', 'class.php'),
    'sfwebdebugpanelcache' => array('debug', 'class.php'),
    'sfwebdebugpanelconfig' => array('debug', 'class.php'),
    'sfwebdebugpanellogs' => array('debug', 'class.php'),
    'sfwebdebugpanelmemory' => array('debug', 'class.php'),
    'sfwebdebugpanelsymfonyversion' => array('debug', 'class.php'),
    'sfwebdebugpaneltimer' => array('debug', 'class.php'),
    'sfoutputescaper' => array('escaper', 'class.php'),
    'sfoutputescaperarraydecorator' => array('escaper', 'class.php'),
    'sfoutputescapergetterdecorator' => array('escaper', 'class.php'),
    'sfoutputescaperiteratordecorator' => array('escaper', 'class.php'),
    'sfoutputescaperobjectdecorator' => array('escaper', 'class.php'),
    'sfoutputescapersafe' => array('escaper', 'class.php'),
    'sfevent' => array('event', 'class.php'),
    'sfeventdispatcher' => array('event', 'class.php'),
    'error.atom' => array('exception/data', 'php'),
    'error.css' => array('exception/data', 'php'),
    'error.html' => array('exception/data', 'php'),
    'error.js' => array('exception/data', 'php'),
    'error.json' => array('exception/data', 'php'),
    'error.rdf' => array('exception/data', 'php'),
    'error.txt' => array('exception/data', 'php'),
    'error.xml' => array('exception/data', 'php'),
    'exception.atom' => array('exception/data', 'php'),
    'exception.css' => array('exception/data', 'php'),
    'exception.html' => array('exception/data', 'php'),
    'exception.js' => array('exception/data', 'php'),
    'exception.json' => array('exception/data', 'php'),
    'exception.rdf' => array('exception/data', 'php'),
    'exception.txt' => array('exception/data', 'php'),
    'exception.xml' => array('exception/data', 'php'),
    'unavailable' => array('exception/data', 'php'),
    'sfcacheexception' => array('exception', 'class.php'),
    'sfconfigurationexception' => array('exception', 'class.php'),
    'sfcontrollerexception' => array('exception', 'class.php'),
    'sfdatabaseexception' => array('exception', 'class.php'),
    'sferror404exception' => array('exception', 'class.php'),
    'sfexception' => array('exception', 'class.php'),
    'sffactoryexception' => array('exception', 'class.php'),
    'sffileexception' => array('exception', 'class.php'),
    'sffilterexception' => array('exception', 'class.php'),
    'sfforwardexception' => array('exception', 'class.php'),
    'sfinitializationexception' => array('exception', 'class.php'),
    'sfparseexception' => array('exception', 'class.php'),
    'sfrenderexception' => array('exception', 'class.php'),
    'sfsecurityexception' => array('exception', 'class.php'),
    'sfstopexception' => array('exception', 'class.php'),
    'sfstorageexception' => array('exception', 'class.php'),
    'sfviewexception' => array('exception', 'class.php'),
    'sfbasicsecurityfilter' => array('filter', 'class.php'),
    'sfcachefilter' => array('filter', 'class.php'),
    'sfcommonfilter' => array('filter', 'class.php'),
    'sfexecutionfilter' => array('filter', 'class.php'),
    'sffilter' => array('filter', 'class.php'),
    'sffilterchain' => array('filter', 'class.php'),
    'sfrenderingfilter' => array('filter', 'class.php'),
    'sfform' => array('form', 'class.php'),
    'sfformfield' => array('form', 'class.php'),
    'sfformfieldschema' => array('form', 'class.php'),
    'sfformfilter' => array('form', 'class.php'),
    'sfadmingenerator' => array('generator', 'class.php'),
    'sfcrudgenerator' => array('generator', 'class.php'),
    'sfgenerator' => array('generator', 'class.php'),
    'sfgeneratormanager' => array('generator', 'class.php'),
    'sfmodelgenerator' => array('generator', 'class.php'),
    'sfmodelgeneratorconfiguration' => array('generator', 'class.php'),
    'sfmodelgeneratorconfigurationfield' => array('generator', 'class.php'),
    'sfmodelgeneratorhelper' => array('generator', 'class.php'),
    'mo' => array('i18n/Gettext', 'php'),
    'po' => array('i18n/Gettext', 'php'),
    'tgettext' => array('i18n/Gettext', 'class.php'),
    'sfi18napplicationextract' => array('i18n/extract', 'class.php'),
    'sfi18nextract' => array('i18n/extract', 'class.php'),
    'sfi18nextractorinterface' => array('i18n/extract', 'class.php'),
    'sfi18nmoduleextract' => array('i18n/extract', 'class.php'),
    'sfi18nphpextractor' => array('i18n/extract', 'class.php'),
    'sfi18nyamlextractor' => array('i18n/extract', 'class.php'),
    'sfi18nyamlgeneratorextractor' => array('i18n/extract', 'class.php'),
    'sfi18nyamlvalidateextractor' => array('i18n/extract', 'class.php'),
    'sfchoiceformat' => array('i18n', 'class.php'),
    'sfcultureinfo' => array('i18n', 'class.php'),
    'sfdateformat' => array('i18n', 'class.php'),
    'sfdatetimeformatinfo' => array('i18n', 'class.php'),
    'sfi18n' => array('i18n', 'class.php'),
    'sfimessagesource' => array('i18n', 'class.php'),
    'sfmessageformat' => array('i18n', 'class.php'),
    'sfmessagesource' => array('i18n', 'class.php'),
    'sfmessagesource_aggregate' => array('i18n', 'class.php'),
    'sfmessagesource_database' => array('i18n', 'class.php'),
    'sfmessagesource_file' => array('i18n', 'class.php'),
    'sfmessagesource_mysql' => array('i18n', 'class.php'),
    'sfmessagesource_sqlite' => array('i18n', 'class.php'),
    'sfmessagesource_xliff' => array('i18n', 'class.php'),
    'sfmessagesource_gettext' => array('i18n', 'class.php'),
    'sfnumberformat' => array('i18n', 'class.php'),
    'sfnumberformatinfo' => array('i18n', 'class.php'),
    'sfaggregatelogger' => array('log', 'class.php'),
    'sfconsolelogger' => array('log', 'class.php'),
    'sffilelogger' => array('log', 'class.php'),
    'sflogger' => array('log', 'class.php'),
    'sfloggerinterface' => array('log', 'class.php'),
    'sfloggerwrapper' => array('log', 'class.php'),
    'sfnologger' => array('log', 'class.php'),
    'sfstreamlogger' => array('log', 'class.php'),
    'sfvarlogger' => array('log', 'class.php'),
    'sfwebdebuglogger' => array('log', 'class.php'),
    'sfmailer' => array('mailer', 'class.php'),
    'sfpeardownloader' => array('plugin', 'class.php'),
    'sfpearenvironment' => array('plugin', 'class.php'),
    'sfpearfrontendplugin' => array('plugin', 'class.php'),
    'sfpearrest' => array('plugin', 'class.php'),
    'sfpearrest10' => array('plugin', 'class.php'),
    'sfpearrest11' => array('plugin', 'class.php'),
    'sfpearrestplugin' => array('plugin', 'class.php'),
    'sfplugindependencyexception' => array('plugin', 'class.php'),
    'sfpluginexception' => array('plugin', 'class.php'),
    'sfpluginmanager' => array('plugin', 'class.php'),
    'sfpluginrecursivedependencyexception' => array('plugin', 'class.php'),
    'sfpluginrestexception' => array('plugin', 'class.php'),
    'sfsymfonypluginmanager' => array('plugin', 'class.php'),
    'sfconsolerequest' => array('request', 'class.php'),
    'sfrequest' => array('request', 'class.php'),
    'sfwebrequest' => array('request', 'class.php'),
    'sfconsoleresponse' => array('response', 'class.php'),
    'sfresponse' => array('response', 'class.php'),
    'sfwebresponse' => array('response', 'class.php'),
    'sfnorouting' => array('routing', 'class.php'),
    'sfobjectroute' => array('routing', 'class.php'),
    'sfobjectroutecollection' => array('routing', 'class.php'),
    'sfpathinforouting' => array('routing', 'class.php'),
    'sfpatternrouting' => array('routing', 'class.php'),
    'sfrequestroute' => array('routing', 'class.php'),
    'sfroute' => array('routing', 'class.php'),
    'sfroutecollection' => array('routing', 'class.php'),
    'sfrouting' => array('routing', 'class.php'),
    'sfcachesessionstorage' => array('storage', 'class.php'),
    'sfdatabasesessionstorage' => array('storage', 'class.php'),
    'sfmysqlsessionstorage' => array('storage', 'class.php'),
    'sfmysqlisessionstorage' => array('storage', 'class.php'),
    'sfnostorage' => array('storage', 'class.php'),
    'sfpdosessionstorage' => array('storage', 'class.php'),
    'sfpostgresqlsessionstorage' => array('storage', 'class.php'),
    'sfsessionstorage' => array('storage', 'class.php'),
    'sfsessionteststorage' => array('storage', 'class.php'),
    'sfstorage' => array('storage', 'class.php'),
    'sfapproutestask' => array('task/app', 'class.php'),
    'sfcachecleartask' => array('task/cache', 'class.php'),
    'sfconfigureauthortask' => array('task/configure', 'class.php'),
    'sfconfiguredatabasetask' => array('task/configure', 'class.php'),
    'sfgenerateapptask' => array('task/generator', 'class.php'),
    'sfgeneratemoduletask' => array('task/generator', 'class.php'),
    'sfgenerateprojecttask' => array('task/generator', 'class.php'),
    'sfgeneratetasktask' => array('task/generator', 'class.php'),
    'sfgeneratorbasetask' => array('task/generator', 'class.php'),
    'sfhelptask' => array('task/help', 'class.php'),
    'sflisttask' => array('task/help', 'class.php'),
    'sfi18nextracttask' => array('task/i18n', 'class.php'),
    'sfi18nfindtask' => array('task/i18n', 'class.php'),
    'sflogcleartask' => array('task/log', 'class.php'),
    'sflogrotatetask' => array('task/log', 'class.php'),
    'sfpluginaddchanneltask' => array('task/plugin', 'class.php'),
    'sfpluginbasetask' => array('task/plugin', 'class.php'),
    'sfplugininstalltask' => array('task/plugin', 'class.php'),
    'sfpluginlisttask' => array('task/plugin', 'class.php'),
    'sfpluginpublishassetstask' => array('task/plugin', 'class.php'),
    'sfpluginuninstalltask' => array('task/plugin', 'class.php'),
    'sfpluginupgradetask' => array('task/plugin', 'class.php'),
    'sfprojectclearcontrollerstask' => array('task/project', 'class.php'),
    'sfprojectdeploytask' => array('task/project', 'class.php'),
    'sfprojectdisabletask' => array('task/project', 'class.php'),
    'sfprojectenabletask' => array('task/project', 'class.php'),
    'sfprojectfreezetask' => array('task/project', 'class.php'),
    'sfprojectpermissionstask' => array('task/project', 'class.php'),
    'sfprojectunfreezetask' => array('task/project', 'class.php'),
    'sfupgradeto11task' => array('task/project', 'class.php'),
    'sfupgradeto12task' => array('task/project', 'class.php'),
    'sfupgradeto13task' => array('task/project', 'class.php'),
    'sfcomponentupgrade' => array('task/project/upgrade1.1', 'class.php'),
    'sfconfigfileupgrade' => array('task/project/upgrade1.1', 'class.php'),
    'sfconfigupgrade' => array('task/project/upgrade1.1', 'class.php'),
    'sfenvironmentupgrade' => array('task/project/upgrade1.1', 'class.php'),
    'sffactoriesupgrade' => array('task/project/upgrade1.1', 'class.php'),
    'sfflashupgrade' => array('task/project/upgrade1.1', 'class.php'),
    'sflayoutupgrade' => array('task/project/upgrade1.1', 'class.php'),
    'sfloggerupgrade' => array('task/project/upgrade1.1', 'class.php'),
    'sfpropelupgrade' => array('task/project/upgrade1.1', 'class.php'),
    'sfsettingsupgrade' => array('task/project/upgrade1.1', 'class.php'),
    'sfsingletonupgrade' => array('task/project/upgrade1.1', 'class.php'),
    'sftestupgrade' => array('task/project/upgrade1.1', 'class.php'),
    'sfupgrade' => array('task/project/upgrade1.1', 'class.php'),
    'sfviewcachemanagerupgrade' => array('task/project/upgrade1.1', 'class.php'),
    'sfwebdebugupgrade' => array('task/project/upgrade1.1', 'class.php'),
    'sfconfigurationupgrade' => array('task/project/upgrade1.2', 'class.php'),
    'sffactories12upgrade' => array('task/project/upgrade1.2', 'class.php'),
    'sfpluginassetsupgrade' => array('task/project/upgrade1.2', 'class.php'),
    'sfpropel13upgrade' => array('task/project/upgrade1.2', 'class.php'),
    'sfpropeliniupgrade' => array('task/project/upgrade1.2', 'class.php'),
    'sfbasetask' => array('task', 'class.php'),
    'sfcommandapplicationtask' => array('task', 'class.php'),
    'sffilesystem' => array('task', 'class.php'),
    'sftask' => array('task', 'class.php'),
    'sftestalltask' => array('task/test', 'class.php'),
    'sftestcoveragetask' => array('task/test', 'class.php'),
    'sftestfunctionaltask' => array('task/test', 'class.php'),
    'sftestunittask' => array('task/test', 'class.php'),
    'sftestbrowser' => array('test', 'class.php'),
    'sftestfunctional' => array('test', 'class.php'),
    'sftestfunctionalbase' => array('test', 'class.php'),
    'sftester' => array('test', 'class.php'),
    'sftesterform' => array('test', 'class.php'),
    'sftesterrequest' => array('test', 'class.php'),
    'sftesterresponse' => array('test', 'class.php'),
    'sftesteruser' => array('test', 'class.php'),
    'sftesterviewcache' => array('test', 'class.php'),
    'sfbasicsecurityuser' => array('user', 'class.php'),
    'sfsecurityuser' => array('user', 'class.php'),
    'sfuser' => array('user', 'class.php'),
    'sfbrowser' => array('util', 'class.php'),
    'sfbrowserbase' => array('util', 'class.php'),
    'sfcallable' => array('util', 'class.php'),
    'sfcontext' => array('util', 'class.php'),
    'sfdomcssselector' => array('util', 'class.php'),
    'sffinder' => array('util', 'class.php'),
    'sfinflector' => array('util', 'class.php'),
    'sfnamespacedparameterholder' => array('util', 'class.php'),
    'sfparameterholder' => array('util', 'class.php'),
    'sftoolkit' => array('util', 'class.php'),
    'sfvalidatori18nchoicecountry' => array('validator/i18n', 'class.php'),
    'sfvalidatori18nchoicelanguage' => array('validator/i18n', 'class.php'),
    'sfvalidatorand' => array('validator', 'class.php'),
    'sfvalidatorbase' => array('validator', 'class.php'),
    'sfvalidatorboolean' => array('validator', 'class.php'),
    'sfvalidatorcsrftoken' => array('validator', 'class.php'),
    'sfvalidatorcallback' => array('validator', 'class.php'),
    'sfvalidatorchoice' => array('validator', 'class.php'),
    'sfvalidatorchoicemany' => array('validator', 'class.php'),
    'sfvalidatordate' => array('validator', 'class.php'),
    'sfvalidatordaterange' => array('validator', 'class.php'),
    'sfvalidatordatetime' => array('validator', 'class.php'),
    'sfvalidatordecorator' => array('validator', 'class.php'),
    'sfvalidatoremail' => array('validator', 'class.php'),
    'sfvalidatorerror' => array('validator', 'class.php'),
    'sfvalidatorerrorschema' => array('validator', 'class.php'),
    'sfvalidatorfile' => array('validator', 'class.php'),
    'sfvalidatorfromdescription' => array('validator', 'class.php'),
    'sfvalidatorinteger' => array('validator', 'class.php'),
    'sfvalidatornumber' => array('validator', 'class.php'),
    'sfvalidatoror' => array('validator', 'class.php'),
    'sfvalidatorpass' => array('validator', 'class.php'),
    'sfvalidatorregex' => array('validator', 'class.php'),
    'sfvalidatorschema' => array('validator', 'class.php'),
    'sfvalidatorschemacompare' => array('validator', 'class.php'),
    'sfvalidatorschemafilter' => array('validator', 'class.php'),
    'sfvalidatorschemaforeach' => array('validator', 'class.php'),
    'sfvalidatorstring' => array('validator', 'class.php'),
    'sfvalidatortime' => array('validator', 'class.php'),
    'sfvalidatorurl' => array('validator', 'class.php'),
    'sfphpview' => array('view', 'class.php'),
    'sfpartialview' => array('view', 'class.php'),
    'sfview' => array('view', 'class.php'),
    'sfviewcachemanager' => array('view', 'class.php'),
    'sfviewparameterholder' => array('view', 'class.php'),
    'sfwidgetformi18ndate' => array('widget/i18n', 'class.php'),
    'sfwidgetformi18ndatetime' => array('widget/i18n', 'class.php'),
    'sfwidgetformi18nselectcountry' => array('widget/i18n', 'class.php'),
    'sfwidgetformi18nselectcurrency' => array('widget/i18n', 'class.php'),
    'sfwidgetformi18nselectlanguage' => array('widget/i18n', 'class.php'),
    'sfwidgetformi18ntime' => array('widget/i18n', 'class.php'),
    'sfwidget' => array('widget', 'class.php'),
    'sfwidgetform' => array('widget', 'class.php'),
    'sfwidgetformchoice' => array('widget', 'class.php'),
    'sfwidgetformchoicemany' => array('widget', 'class.php'),
    'sfwidgetformdate' => array('widget', 'class.php'),
    'sfwidgetformdaterange' => array('widget', 'class.php'),
    'sfwidgetformdatetime' => array('widget', 'class.php'),
    'sfwidgetformfilterdate' => array('widget', 'class.php'),
    'sfwidgetformfilterinput' => array('widget', 'class.php'),
    'sfwidgetforminput' => array('widget', 'class.php'),
    'sfwidgetforminputcheckbox' => array('widget', 'class.php'),
    'sfwidgetforminputfile' => array('widget', 'class.php'),
    'sfwidgetforminputfileeditable' => array('widget', 'class.php'),
    'sfwidgetforminputhidden' => array('widget', 'class.php'),
    'sfwidgetforminputpassword' => array('widget', 'class.php'),
    'sfwidgetformschema' => array('widget', 'class.php'),
    'sfwidgetformschemadecorator' => array('widget', 'class.php'),
    'sfwidgetformschemaforeach' => array('widget', 'class.php'),
    'sfwidgetformschemaformatter' => array('widget', 'class.php'),
    'sfwidgetformschemaformatterlist' => array('widget', 'class.php'),
    'sfwidgetformschemaformattertable' => array('widget', 'class.php'),
    'sfwidgetformselect' => array('widget', 'class.php'),
    'sfwidgetformselectcheckbox' => array('widget', 'class.php'),
    'sfwidgetformselectmany' => array('widget', 'class.php'),
    'sfwidgetformselectradio' => array('widget', 'class.php'),
    'sfwidgetformtextarea' => array('widget', 'class.php'),
    'sfwidgetformtime' => array('widget', 'class.php'),
    'sfyaml' => array('yaml', 'php'),
    'sfyamldumper' => array('yaml', 'php'),
    'sfyamlinline' => array('yaml', 'php'),
    'sfyamlparser' => array('yaml', 'php'),

  );
}
