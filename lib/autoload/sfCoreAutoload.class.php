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

    require $this->baseDir.$this->classes[$class].'.class.php';

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
      ->name('*\.class\.php')
      ->in($libDir)
    ;

    sort($files, SORT_STRING);

    $classes = array();
    foreach ($files as $file)
    {
      $class = basename($file, '.class.php');
      $classes[strtolower($class)] = str_replace($libDir.'/', '', str_replace(DIRECTORY_SEPARATOR, '/', dirname($file))).'/'.$class;
    }

    $content = preg_replace('/protected \$classes = array *\(.*?\)/s', 'protected $classes = '.var_export($classes, true), file_get_contents(__FILE__));

    file_put_contents(__FILE__, $content);
  }

  // Don't edit this property by hand.
  // To update it, use sfCoreAutoload::make()
  protected $classes = array (
  'sfaction' => 'action/sfAction',
  'sfactionstack' => 'action/sfActionStack',
  'sfactionstackentry' => 'action/sfActionStackEntry',
  'sfactions' => 'action/sfActions',
  'sfcomponent' => 'action/sfComponent',
  'sfcomponents' => 'action/sfComponents',
  'sfdata' => 'addon/sfData',
  'sfpager' => 'addon/sfPager',
  'sfautoload' => 'autoload/sfAutoload',
  'sfcoreautoload' => 'autoload/sfCoreAutoload',
  'sfsimpleautoload' => 'autoload/sfSimpleAutoload',
  'sfapccache' => 'cache/sfAPCCache',
  'sfcache' => 'cache/sfCache',
  'sfeacceleratorcache' => 'cache/sfEAcceleratorCache',
  'sffilecache' => 'cache/sfFileCache',
  'sffunctioncache' => 'cache/sfFunctionCache',
  'sfmemcachecache' => 'cache/sfMemcacheCache',
  'sfnocache' => 'cache/sfNoCache',
  'sfsqlitecache' => 'cache/sfSQLiteCache',
  'sfxcachecache' => 'cache/sfXCacheCache',
  'sfansicolorformatter' => 'command/sfAnsiColorFormatter',
  'sfcommandapplication' => 'command/sfCommandApplication',
  'sfcommandargument' => 'command/sfCommandArgument',
  'sfcommandargumentset' => 'command/sfCommandArgumentSet',
  'sfcommandargumentsexception' => 'command/sfCommandArgumentsException',
  'sfcommandexception' => 'command/sfCommandException',
  'sfcommandlogger' => 'command/sfCommandLogger',
  'sfcommandmanager' => 'command/sfCommandManager',
  'sfcommandoption' => 'command/sfCommandOption',
  'sfcommandoptionset' => 'command/sfCommandOptionSet',
  'sfformatter' => 'command/sfFormatter',
  'sfsymfonycommandapplication' => 'command/sfSymfonyCommandApplication',
  'sfapplicationconfiguration' => 'config/sfApplicationConfiguration',
  'sfautoloadconfighandler' => 'config/sfAutoloadConfigHandler',
  'sfcacheconfighandler' => 'config/sfCacheConfigHandler',
  'sfcompileconfighandler' => 'config/sfCompileConfigHandler',
  'sfconfig' => 'config/sfConfig',
  'sfconfigcache' => 'config/sfConfigCache',
  'sfconfighandler' => 'config/sfConfigHandler',
  'sfdatabaseconfighandler' => 'config/sfDatabaseConfigHandler',
  'sfdefineenvironmentconfighandler' => 'config/sfDefineEnvironmentConfigHandler',
  'sffactoryconfighandler' => 'config/sfFactoryConfigHandler',
  'sffilterconfighandler' => 'config/sfFilterConfigHandler',
  'sfgeneratorconfighandler' => 'config/sfGeneratorConfigHandler',
  'sfloader' => 'config/sfLoader',
  'sfpluginconfiguration' => 'config/sfPluginConfiguration',
  'sfpluginconfigurationgeneric' => 'config/sfPluginConfigurationGeneric',
  'sfprojectconfiguration' => 'config/sfProjectConfiguration',
  'sfrootconfighandler' => 'config/sfRootConfigHandler',
  'sfroutingconfighandler' => 'config/sfRoutingConfigHandler',
  'sfsecurityconfighandler' => 'config/sfSecurityConfigHandler',
  'sfsimpleyamlconfighandler' => 'config/sfSimpleYamlConfigHandler',
  'sfviewconfighandler' => 'config/sfViewConfigHandler',
  'sfyamlconfighandler' => 'config/sfYamlConfigHandler',
  'sfconsolecontroller' => 'controller/sfConsoleController',
  'sfcontroller' => 'controller/sfController',
  'sffrontwebcontroller' => 'controller/sfFrontWebController',
  'sfwebcontroller' => 'controller/sfWebController',
  'sfdatabase' => 'database/sfDatabase',
  'sfdatabasemanager' => 'database/sfDatabaseManager',
  'sfmysqldatabase' => 'database/sfMySQLDatabase',
  'sfmysqlidatabase' => 'database/sfMySQLiDatabase',
  'sfpdodatabase' => 'database/sfPDODatabase',
  'sfpostgresqldatabase' => 'database/sfPostgreSQLDatabase',
  'sfdebug' => 'debug/sfDebug',
  'sftimer' => 'debug/sfTimer',
  'sftimermanager' => 'debug/sfTimerManager',
  'sfwebdebug' => 'debug/sfWebDebug',
  'sfwebdebugpanel' => 'debug/sfWebDebugPanel',
  'sfwebdebugpanelcache' => 'debug/sfWebDebugPanelCache',
  'sfwebdebugpanelconfig' => 'debug/sfWebDebugPanelConfig',
  'sfwebdebugpanellogs' => 'debug/sfWebDebugPanelLogs',
  'sfwebdebugpanelmemory' => 'debug/sfWebDebugPanelMemory',
  'sfwebdebugpanelsymfonyversion' => 'debug/sfWebDebugPanelSymfonyVersion',
  'sfwebdebugpaneltimer' => 'debug/sfWebDebugPanelTimer',
  'sfevent' => 'event/sfEvent',
  'sfeventdispatcher' => 'event/sfEventDispatcher',
  'sfcacheexception' => 'exception/sfCacheException',
  'sfconfigurationexception' => 'exception/sfConfigurationException',
  'sfcontrollerexception' => 'exception/sfControllerException',
  'sfdatabaseexception' => 'exception/sfDatabaseException',
  'sferror404exception' => 'exception/sfError404Exception',
  'sfexception' => 'exception/sfException',
  'sffactoryexception' => 'exception/sfFactoryException',
  'sffileexception' => 'exception/sfFileException',
  'sffilterexception' => 'exception/sfFilterException',
  'sfforwardexception' => 'exception/sfForwardException',
  'sfinitializationexception' => 'exception/sfInitializationException',
  'sfparseexception' => 'exception/sfParseException',
  'sfrenderexception' => 'exception/sfRenderException',
  'sfsecurityexception' => 'exception/sfSecurityException',
  'sfstopexception' => 'exception/sfStopException',
  'sfstorageexception' => 'exception/sfStorageException',
  'sfviewexception' => 'exception/sfViewException',
  'sfbasicsecurityfilter' => 'filter/sfBasicSecurityFilter',
  'sfcachefilter' => 'filter/sfCacheFilter',
  'sfcommonfilter' => 'filter/sfCommonFilter',
  'sfexecutionfilter' => 'filter/sfExecutionFilter',
  'sffilter' => 'filter/sfFilter',
  'sffilterchain' => 'filter/sfFilterChain',
  'sfrenderingfilter' => 'filter/sfRenderingFilter',
  'sfform' => 'form/sfForm',
  'sfformfield' => 'form/sfFormField',
  'sfformfieldschema' => 'form/sfFormFieldSchema',
  'sfformfilter' => 'form/sfFormFilter',
  'sfadmingenerator' => 'generator/sfAdminGenerator',
  'sfcrudgenerator' => 'generator/sfCrudGenerator',
  'sfgenerator' => 'generator/sfGenerator',
  'sfgeneratormanager' => 'generator/sfGeneratorManager',
  'sfmodelgenerator' => 'generator/sfModelGenerator',
  'sfmodelgeneratorconfiguration' => 'generator/sfModelGeneratorConfiguration',
  'sfmodelgeneratorconfigurationfield' => 'generator/sfModelGeneratorConfigurationField',
  'sfmodelgeneratorhelper' => 'generator/sfModelGeneratorHelper',
  'sfrichtexteditor' => 'helper/sfRichTextEditor',
  'sfrichtexteditorfck' => 'helper/sfRichTextEditorFCK',
  'sfrichtexteditortinymce' => 'helper/sfRichTextEditorTinyMCE',
  'tgettext' => 'i18n/Gettext/TGettext',
  'sfi18napplicationextract' => 'i18n/extract/sfI18nApplicationExtract',
  'sfi18nextract' => 'i18n/extract/sfI18nExtract',
  'sfi18nextractorinterface' => 'i18n/extract/sfI18nExtractorInterface',
  'sfi18nmoduleextract' => 'i18n/extract/sfI18nModuleExtract',
  'sfi18nphpextractor' => 'i18n/extract/sfI18nPhpExtractor',
  'sfi18nyamlextractor' => 'i18n/extract/sfI18nYamlExtractor',
  'sfi18nyamlgeneratorextractor' => 'i18n/extract/sfI18nYamlGeneratorExtractor',
  'sfi18nyamlvalidateextractor' => 'i18n/extract/sfI18nYamlValidateExtractor',
  'sfchoiceformat' => 'i18n/sfChoiceFormat',
  'sfcultureinfo' => 'i18n/sfCultureInfo',
  'sfdateformat' => 'i18n/sfDateFormat',
  'sfdatetimeformatinfo' => 'i18n/sfDateTimeFormatInfo',
  'sfi18n' => 'i18n/sfI18N',
  'sfimessagesource' => 'i18n/sfIMessageSource',
  'sfmessageformat' => 'i18n/sfMessageFormat',
  'sfmessagesource' => 'i18n/sfMessageSource',
  'sfmessagesource_aggregate' => 'i18n/sfMessageSource_Aggregate',
  'sfmessagesource_database' => 'i18n/sfMessageSource_Database',
  'sfmessagesource_file' => 'i18n/sfMessageSource_File',
  'sfmessagesource_mysql' => 'i18n/sfMessageSource_MySQL',
  'sfmessagesource_sqlite' => 'i18n/sfMessageSource_SQLite',
  'sfmessagesource_xliff' => 'i18n/sfMessageSource_XLIFF',
  'sfmessagesource_gettext' => 'i18n/sfMessageSource_gettext',
  'sfnumberformat' => 'i18n/sfNumberFormat',
  'sfnumberformatinfo' => 'i18n/sfNumberFormatInfo',
  'sfaggregatelogger' => 'log/sfAggregateLogger',
  'sfconsolelogger' => 'log/sfConsoleLogger',
  'sffilelogger' => 'log/sfFileLogger',
  'sflogger' => 'log/sfLogger',
  'sfloggerinterface' => 'log/sfLoggerInterface',
  'sfloggerwrapper' => 'log/sfLoggerWrapper',
  'sfnologger' => 'log/sfNoLogger',
  'sfstreamlogger' => 'log/sfStreamLogger',
  'sfvarlogger' => 'log/sfVarLogger',
  'sfwebdebuglogger' => 'log/sfWebDebugLogger',
  'sfmailer' => 'mailer/sfMailer',
  'sfpeardownloader' => 'plugin/sfPearDownloader',
  'sfpearenvironment' => 'plugin/sfPearEnvironment',
  'sfpearfrontendplugin' => 'plugin/sfPearFrontendPlugin',
  'sfpearrest' => 'plugin/sfPearRest',
  'sfpearrest10' => 'plugin/sfPearRest10',
  'sfpearrest11' => 'plugin/sfPearRest11',
  'sfpearrestplugin' => 'plugin/sfPearRestPlugin',
  'sfplugindependencyexception' => 'plugin/sfPluginDependencyException',
  'sfpluginexception' => 'plugin/sfPluginException',
  'sfpluginmanager' => 'plugin/sfPluginManager',
  'sfpluginrecursivedependencyexception' => 'plugin/sfPluginRecursiveDependencyException',
  'sfpluginrestexception' => 'plugin/sfPluginRestException',
  'sfsymfonypluginmanager' => 'plugin/sfSymfonyPluginManager',
  'sfconsolerequest' => 'request/sfConsoleRequest',
  'sfrequest' => 'request/sfRequest',
  'sfwebrequest' => 'request/sfWebRequest',
  'sfconsoleresponse' => 'response/sfConsoleResponse',
  'sfresponse' => 'response/sfResponse',
  'sfwebresponse' => 'response/sfWebResponse',
  'sfnorouting' => 'routing/sfNoRouting',
  'sfobjectroute' => 'routing/sfObjectRoute',
  'sfobjectroutecollection' => 'routing/sfObjectRouteCollection',
  'sfpathinforouting' => 'routing/sfPathInfoRouting',
  'sfpatternrouting' => 'routing/sfPatternRouting',
  'sfrequestroute' => 'routing/sfRequestRoute',
  'sfroute' => 'routing/sfRoute',
  'sfroutecollection' => 'routing/sfRouteCollection',
  'sfrouting' => 'routing/sfRouting',
  'sfcachesessionstorage' => 'storage/sfCacheSessionStorage',
  'sfdatabasesessionstorage' => 'storage/sfDatabaseSessionStorage',
  'sfmysqlsessionstorage' => 'storage/sfMySQLSessionStorage',
  'sfmysqlisessionstorage' => 'storage/sfMySQLiSessionStorage',
  'sfnostorage' => 'storage/sfNoStorage',
  'sfpdosessionstorage' => 'storage/sfPDOSessionStorage',
  'sfpostgresqlsessionstorage' => 'storage/sfPostgreSQLSessionStorage',
  'sfsessionstorage' => 'storage/sfSessionStorage',
  'sfsessionteststorage' => 'storage/sfSessionTestStorage',
  'sfstorage' => 'storage/sfStorage',
  'sfapproutestask' => 'task/app/sfAppRoutesTask',
  'sfcachecleartask' => 'task/cache/sfCacheClearTask',
  'sfconfigureauthortask' => 'task/configure/sfConfigureAuthorTask',
  'sfconfiguredatabasetask' => 'task/configure/sfConfigureDatabaseTask',
  'sfgenerateapptask' => 'task/generator/sfGenerateAppTask',
  'sfgeneratemoduletask' => 'task/generator/sfGenerateModuleTask',
  'sfgenerateprojecttask' => 'task/generator/sfGenerateProjectTask',
  'sfgeneratetasktask' => 'task/generator/sfGenerateTaskTask',
  'sfgeneratorbasetask' => 'task/generator/sfGeneratorBaseTask',
  'sfhelptask' => 'task/help/sfHelpTask',
  'sflisttask' => 'task/help/sfListTask',
  'sfi18nextracttask' => 'task/i18n/sfI18nExtractTask',
  'sfi18nfindtask' => 'task/i18n/sfI18nFindTask',
  'sflogcleartask' => 'task/log/sfLogClearTask',
  'sflogrotatetask' => 'task/log/sfLogRotateTask',
  'sfpluginaddchanneltask' => 'task/plugin/sfPluginAddChannelTask',
  'sfpluginbasetask' => 'task/plugin/sfPluginBaseTask',
  'sfplugininstalltask' => 'task/plugin/sfPluginInstallTask',
  'sfpluginlisttask' => 'task/plugin/sfPluginListTask',
  'sfpluginpublishassetstask' => 'task/plugin/sfPluginPublishAssetsTask',
  'sfpluginuninstalltask' => 'task/plugin/sfPluginUninstallTask',
  'sfpluginupgradetask' => 'task/plugin/sfPluginUpgradeTask',
  'sfprojectclearcontrollerstask' => 'task/project/sfProjectClearControllersTask',
  'sfprojectdeploytask' => 'task/project/sfProjectDeployTask',
  'sfprojectdisabletask' => 'task/project/sfProjectDisableTask',
  'sfprojectenabletask' => 'task/project/sfProjectEnableTask',
  'sfprojectfreezetask' => 'task/project/sfProjectFreezeTask',
  'sfprojectpermissionstask' => 'task/project/sfProjectPermissionsTask',
  'sfprojectunfreezetask' => 'task/project/sfProjectUnfreezeTask',
  'sfupgradeto11task' => 'task/project/sfUpgradeTo11Task',
  'sfupgradeto12task' => 'task/project/sfUpgradeTo12Task',
  'sfupgradeto13task' => 'task/project/sfUpgradeTo13Task',
  'sfcomponentupgrade' => 'task/project/upgrade1.1/sfComponentUpgrade',
  'sfconfigfileupgrade' => 'task/project/upgrade1.1/sfConfigFileUpgrade',
  'sfconfigupgrade' => 'task/project/upgrade1.1/sfConfigUpgrade',
  'sfenvironmentupgrade' => 'task/project/upgrade1.1/sfEnvironmentUpgrade',
  'sffactoriesupgrade' => 'task/project/upgrade1.1/sfFactoriesUpgrade',
  'sfflashupgrade' => 'task/project/upgrade1.1/sfFlashUpgrade',
  'sflayoutupgrade' => 'task/project/upgrade1.1/sfLayoutUpgrade',
  'sfloggerupgrade' => 'task/project/upgrade1.1/sfLoggerUpgrade',
  'sfpropelupgrade' => 'task/project/upgrade1.1/sfPropelUpgrade',
  'sfsettingsupgrade' => 'task/project/upgrade1.1/sfSettingsUpgrade',
  'sfsingletonupgrade' => 'task/project/upgrade1.1/sfSingletonUpgrade',
  'sftestupgrade' => 'task/project/upgrade1.1/sfTestUpgrade',
  'sfupgrade' => 'task/project/upgrade1.1/sfUpgrade',
  'sfviewcachemanagerupgrade' => 'task/project/upgrade1.1/sfViewCacheManagerUpgrade',
  'sfwebdebugupgrade' => 'task/project/upgrade1.1/sfWebDebugUpgrade',
  'sfconfigurationupgrade' => 'task/project/upgrade1.2/sfConfigurationUpgrade',
  'sffactories12upgrade' => 'task/project/upgrade1.2/sfFactories12Upgrade',
  'sfpluginassetsupgrade' => 'task/project/upgrade1.2/sfPluginAssetsUpgrade',
  'sfpropel13upgrade' => 'task/project/upgrade1.2/sfPropel13Upgrade',
  'sfpropeliniupgrade' => 'task/project/upgrade1.2/sfPropelIniUpgrade',
  'sfbasetask' => 'task/sfBaseTask',
  'sfcommandapplicationtask' => 'task/sfCommandApplicationTask',
  'sffilesystem' => 'task/sfFilesystem',
  'sftask' => 'task/sfTask',
  'sftestalltask' => 'task/test/sfTestAllTask',
  'sftestcoveragetask' => 'task/test/sfTestCoverageTask',
  'sftestfunctionaltask' => 'task/test/sfTestFunctionalTask',
  'sftestunittask' => 'task/test/sfTestUnitTask',
  'sftestbrowser' => 'test/sfTestBrowser',
  'sftestfunctional' => 'test/sfTestFunctional',
  'sftestfunctionalbase' => 'test/sfTestFunctionalBase',
  'sftester' => 'test/sfTester',
  'sftesterform' => 'test/sfTesterForm',
  'sftesterrequest' => 'test/sfTesterRequest',
  'sftesterresponse' => 'test/sfTesterResponse',
  'sftesteruser' => 'test/sfTesterUser',
  'sftesterviewcache' => 'test/sfTesterViewCache',
  'sfbasicsecurityuser' => 'user/sfBasicSecurityUser',
  'sfsecurityuser' => 'user/sfSecurityUser',
  'sfuser' => 'user/sfUser',
  'sfbrowser' => 'util/sfBrowser',
  'sfbrowserbase' => 'util/sfBrowserBase',
  'sfcallable' => 'util/sfCallable',
  'sfcontext' => 'util/sfContext',
  'sfdomcssselector' => 'util/sfDomCssSelector',
  'sffinder' => 'util/sfFinder',
  'sfinflector' => 'util/sfInflector',
  'sfnamespacedparameterholder' => 'util/sfNamespacedParameterHolder',
  'sfparameterholder' => 'util/sfParameterHolder',
  'sftoolkit' => 'util/sfToolkit',
  'sfvalidatori18nchoicecountry' => 'validator/i18n/sfValidatorI18nChoiceCountry',
  'sfvalidatori18nchoicelanguage' => 'validator/i18n/sfValidatorI18nChoiceLanguage',
  'sfvalidatorand' => 'validator/sfValidatorAnd',
  'sfvalidatorbase' => 'validator/sfValidatorBase',
  'sfvalidatorboolean' => 'validator/sfValidatorBoolean',
  'sfvalidatorcsrftoken' => 'validator/sfValidatorCSRFToken',
  'sfvalidatorcallback' => 'validator/sfValidatorCallback',
  'sfvalidatorchoice' => 'validator/sfValidatorChoice',
  'sfvalidatorchoicemany' => 'validator/sfValidatorChoiceMany',
  'sfvalidatordate' => 'validator/sfValidatorDate',
  'sfvalidatordaterange' => 'validator/sfValidatorDateRange',
  'sfvalidatordatetime' => 'validator/sfValidatorDateTime',
  'sfvalidatordecorator' => 'validator/sfValidatorDecorator',
  'sfvalidatoremail' => 'validator/sfValidatorEmail',
  'sfvalidatorerror' => 'validator/sfValidatorError',
  'sfvalidatorerrorschema' => 'validator/sfValidatorErrorSchema',
  'sfvalidatorfile' => 'validator/sfValidatorFile',
  'sfvalidatorfromdescription' => 'validator/sfValidatorFromDescription',
  'sfvalidatorinteger' => 'validator/sfValidatorInteger',
  'sfvalidatornumber' => 'validator/sfValidatorNumber',
  'sfvalidatoror' => 'validator/sfValidatorOr',
  'sfvalidatorpass' => 'validator/sfValidatorPass',
  'sfvalidatorregex' => 'validator/sfValidatorRegex',
  'sfvalidatorschema' => 'validator/sfValidatorSchema',
  'sfvalidatorschemacompare' => 'validator/sfValidatorSchemaCompare',
  'sfvalidatorschemafilter' => 'validator/sfValidatorSchemaFilter',
  'sfvalidatorschemaforeach' => 'validator/sfValidatorSchemaForEach',
  'sfvalidatorstring' => 'validator/sfValidatorString',
  'sfvalidatortime' => 'validator/sfValidatorTime',
  'sfvalidatorurl' => 'validator/sfValidatorUrl',
  'sfoutputescaper' => 'view/escaper/sfOutputEscaper',
  'sfoutputescaperarraydecorator' => 'view/escaper/sfOutputEscaperArrayDecorator',
  'sfoutputescapergetterdecorator' => 'view/escaper/sfOutputEscaperGetterDecorator',
  'sfoutputescaperiteratordecorator' => 'view/escaper/sfOutputEscaperIteratorDecorator',
  'sfoutputescaperobjectdecorator' => 'view/escaper/sfOutputEscaperObjectDecorator',
  'sfoutputescapersafe' => 'view/escaper/sfOutputEscaperSafe',
  'sfphpview' => 'view/sfPHPView',
  'sfpartialview' => 'view/sfPartialView',
  'sfview' => 'view/sfView',
  'sfviewcachemanager' => 'view/sfViewCacheManager',
  'sfviewparameterholder' => 'view/sfViewParameterHolder',
  'sfwidgetformi18ndate' => 'widget/i18n/sfWidgetFormI18nDate',
  'sfwidgetformi18ndatetime' => 'widget/i18n/sfWidgetFormI18nDateTime',
  'sfwidgetformi18nselectcountry' => 'widget/i18n/sfWidgetFormI18nSelectCountry',
  'sfwidgetformi18nselectcurrency' => 'widget/i18n/sfWidgetFormI18nSelectCurrency',
  'sfwidgetformi18nselectlanguage' => 'widget/i18n/sfWidgetFormI18nSelectLanguage',
  'sfwidgetformi18ntime' => 'widget/i18n/sfWidgetFormI18nTime',
  'sfwidget' => 'widget/sfWidget',
  'sfwidgetform' => 'widget/sfWidgetForm',
  'sfwidgetformchoice' => 'widget/sfWidgetFormChoice',
  'sfwidgetformchoicemany' => 'widget/sfWidgetFormChoiceMany',
  'sfwidgetformdate' => 'widget/sfWidgetFormDate',
  'sfwidgetformdaterange' => 'widget/sfWidgetFormDateRange',
  'sfwidgetformdatetime' => 'widget/sfWidgetFormDateTime',
  'sfwidgetformfilterdate' => 'widget/sfWidgetFormFilterDate',
  'sfwidgetformfilterinput' => 'widget/sfWidgetFormFilterInput',
  'sfwidgetforminput' => 'widget/sfWidgetFormInput',
  'sfwidgetforminputcheckbox' => 'widget/sfWidgetFormInputCheckbox',
  'sfwidgetforminputfile' => 'widget/sfWidgetFormInputFile',
  'sfwidgetforminputfileeditable' => 'widget/sfWidgetFormInputFileEditable',
  'sfwidgetforminputhidden' => 'widget/sfWidgetFormInputHidden',
  'sfwidgetforminputpassword' => 'widget/sfWidgetFormInputPassword',
  'sfwidgetformschema' => 'widget/sfWidgetFormSchema',
  'sfwidgetformschemadecorator' => 'widget/sfWidgetFormSchemaDecorator',
  'sfwidgetformschemaforeach' => 'widget/sfWidgetFormSchemaForEach',
  'sfwidgetformschemaformatter' => 'widget/sfWidgetFormSchemaFormatter',
  'sfwidgetformschemaformatterlist' => 'widget/sfWidgetFormSchemaFormatterList',
  'sfwidgetformschemaformattertable' => 'widget/sfWidgetFormSchemaFormatterTable',
  'sfwidgetformselect' => 'widget/sfWidgetFormSelect',
  'sfwidgetformselectcheckbox' => 'widget/sfWidgetFormSelectCheckbox',
  'sfwidgetformselectmany' => 'widget/sfWidgetFormSelectMany',
  'sfwidgetformselectradio' => 'widget/sfWidgetFormSelectRadio',
  'sfwidgetformtextarea' => 'widget/sfWidgetFormTextarea',
  'sfwidgetformtime' => 'widget/sfWidgetFormTime',
  'sfyaml' => 'yaml/sfYaml',
  'sfyamldumper' => 'yaml/sfYamlDumper',
  'sfyamlinline' => 'yaml/sfYamlInline',
  'sfyamlparser' => 'yaml/sfYamlParser',
);
}
