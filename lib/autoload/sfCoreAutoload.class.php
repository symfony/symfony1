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
define('SYMFONY_VERSION', '1.1.0-DEV');

/**
 * sfCoreAutoload class.
 *
 * This class is a singleton as PHP seems to be unable to register 2 autoloaders that are instances
 * of the same class (why?).
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfCoreAutoload
{
  static protected
    $instance = null;

  protected function __construct()
  {
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
    ini_set('unserialize_callback_func', 'spl_autoload_call');
    if (!spl_autoload_register(array(self::getInstance(), 'autoload')))
    {
      throw new sfException(sprintf('Unable to register %s::autoload as an autoloading method.', get_class(self::getInstance())));
    }
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
   * @param  string  A class name.
   *
   * @return boolean Returns true if the class has been loaded
   */
  public function autoload($class)
  {
    if (!isset($this->classes[$class]))
    {
      return false;
    }

    require dirname(__FILE__).'/../'.$this->classes[$class].'/'.$class.'.class.php';

    return true;
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

    $classes = array();
    foreach ($files as $file)
    {
      $classes[basename($file, '.class.php')] = str_replace($libDir.'/', '', str_replace(DIRECTORY_SEPARATOR, '/', dirname($file)));
    }

    $content = preg_replace('/protected \$classes = array *\(.*?\)/s', 'protected $classes = '.var_export($classes, true), file_get_contents(__FILE__));

    file_put_contents(__FILE__, $content);
  }

  // Don't edit this property by hand.
  // To update it, use sfCoreAutoload::make()
  protected $classes = array (
  'sfAutoload' => 'autoload',
  'sfCoreAutoload' => 'autoload',
  'sfSimpleAutoload' => 'autoload',
  'sfRichTextEditor' => 'helper',
  'sfRichTextEditorTinyMCE' => 'helper',
  'sfRichTextEditorFCK' => 'helper',
  'sfPDOSessionStorage' => 'storage',
  'sfNoStorage' => 'storage',
  'sfPostgreSQLSessionStorage' => 'storage',
  'sfStorage' => 'storage',
  'sfMySQLSessionStorage' => 'storage',
  'sfSessionTestStorage' => 'storage',
  'sfDatabaseSessionStorage' => 'storage',
  'sfSessionStorage' => 'storage',
  'sfWidgetForm' => 'widget',
  'sfWidgetFormSelectRadio' => 'widget',
  'sfWidgetFormInputHidden' => 'widget',
  'sfWidgetFormI18nSelectCountry' => 'widget/i18n',
  'sfWidgetFormI18nTime' => 'widget/i18n',
  'sfWidgetFormI18nSelectLanguage' => 'widget/i18n',
  'sfWidgetFormI18nDateTime' => 'widget/i18n',
  'sfWidgetFormI18nDate' => 'widget/i18n',
  'sfWidgetFormSchemaForEach' => 'widget',
  'sfWidgetFormSelectMany' => 'widget',
  'sfWidget' => 'widget',
  'sfWidgetFormDate' => 'widget',
  'sfWidgetFormIdentity' => 'widget',
  'sfWidgetFormInput' => 'widget',
  'sfWidgetFormSchemaFormatter' => 'widget',
  'sfWidgetFormSchemaFormatterList' => 'widget',
  'sfWidgetFormDateTime' => 'widget',
  'sfWidgetFormInputPassword' => 'widget',
  'sfWidgetFormSchema' => 'widget',
  'sfWidgetFormSchemaFormatterTable' => 'widget',
  'sfWidgetFormInputCheckbox' => 'widget',
  'sfWidgetFormTextarea' => 'widget',
  'sfWidgetFormTime' => 'widget',
  'sfWidgetFormSelect' => 'widget',
  'sfWidgetFormSchemaDecorator' => 'widget',
  'sfWidgetFormInputFile' => 'widget',
  'sfTestBrowser' => 'test',
  'sfRenderingFilter' => 'filter',
  'sfFilterChain' => 'filter',
  'sfCommonFilter' => 'filter',
  'sfFilter' => 'filter',
  'sfExecutionFilter' => 'filter',
  'sfBasicSecurityFilter' => 'filter',
  'sfCacheFilter' => 'filter',
  'sfCrudGenerator' => 'generator',
  'sfGeneratorManager' => 'generator',
  'sfAdminGenerator' => 'generator',
  'sfGenerator' => 'generator',
  'sfMessageSource_gettext' => 'i18n',
  'sfIMessageSource' => 'i18n',
  'sfMessageSource_Aggregate' => 'i18n',
  'sfCultureInfo' => 'i18n',
  'sfMessageSource_MySQL' => 'i18n',
  'sfI18nYamlExtractor' => 'i18n/extract',
  'sfI18nYamlValidateExtractor' => 'i18n/extract',
  'sfI18nExtractorInterface' => 'i18n/extract',
  'sfI18nYamlGeneratorExtractor' => 'i18n/extract',
  'sfI18nExtract' => 'i18n/extract',
  'sfI18nPhpExtractor' => 'i18n/extract',
  'sfI18nModuleExtract' => 'i18n/extract',
  'sfI18nApplicationExtract' => 'i18n/extract',
  'sfMessageSource_XLIFF' => 'i18n',
  'sfMessageSource_File' => 'i18n',
  'sfMessageSource_SQLite' => 'i18n',
  'sfMessageSource_Database' => 'i18n',
  'sfDateFormat' => 'i18n',
  'sfI18N' => 'i18n',
  'sfDateTimeFormatInfo' => 'i18n',
  'TGettext' => 'i18n/Gettext',
  'sfChoiceFormat' => 'i18n',
  'sfNumberFormat' => 'i18n',
  'sfMessageFormat' => 'i18n',
  'sfNumberFormatInfo' => 'i18n',
  'sfMessageSource' => 'i18n',
  'sfDebug' => 'debug',
  'sfTimer' => 'debug',
  'sfTimerManager' => 'debug',
  'sfWebDebug' => 'debug',
  'sfWebRequest' => 'request',
  'sfRequest' => 'request',
  'sfConsoleRequest' => 'request',
  'sfNamespacedParameterHolder' => 'util',
  'sfDomCssSelector' => 'util',
  'sfToolkit' => 'util',
  'sfCallable' => 'util',
  'sfParameterHolder' => 'util',
  'sfContext' => 'util',
  'sfFinder' => 'util',
  'sfBrowser' => 'util',
  'sfInflector' => 'util',
  'sfBaseTask' => 'task',
  'sfCommandApplicationTask' => 'task',
  'sfTestFunctionalTask' => 'task/test',
  'sfTestAllTask' => 'task/test',
  'sfTestUnitTask' => 'task/test',
  'sfGeneratorBaseTask' => 'task/generator',
  'sfGenerateModuleTask' => 'task/generator',
  'sfGenerateProjectTask' => 'task/generator',
  'sfGenerateAppTask' => 'task/generator',
  'sfI18nExtractTask' => 'task/i18n',
  'sfI18nFindTask' => 'task/i18n',
  'sfConfigureDatabaseTask' => 'task/configure',
  'sfConfigureAuthorTask' => 'task/configure',
  'sfLogClearTask' => 'task/log',
  'sfLogRotateTask' => 'task/log',
  'sfProjectEnableTask' => 'task/project',
  'sfProjectDisableTask' => 'task/project',
  'sfProjectPermissionsTask' => 'task/project',
  'sfProjectFreezeTask' => 'task/project',
  'sfProjectUnfreezeTask' => 'task/project',
  'sfUpgradeTo11Task' => 'task/project',
  'sfProjectDeployTask' => 'task/project',
  'sfProjectClearControllersTask' => 'task/project',
  'sfSingletonUpgrade' => 'task/project/upgrade1.1',
  'sfTestUpgrade' => 'task/project/upgrade1.1',
  'sfViewCacheManagerUpgrade' => 'task/project/upgrade1.1',
  'sfFlashUpgrade' => 'task/project/upgrade1.1',
  'sfFactoriesUpgrade' => 'task/project/upgrade1.1',
  'sfEnvironmentUpgrade' => 'task/project/upgrade1.1',
  'sfWebDebugUpgrade' => 'task/project/upgrade1.1',
  'sfConfigFileUpgrade' => 'task/project/upgrade1.1',
  'sfUpgrade' => 'task/project/upgrade1.1',
  'sfPropelUpgrade' => 'task/project/upgrade1.1',
  'sfConfigUpgrade' => 'task/project/upgrade1.1',
  'sfComponentUpgrade' => 'task/project/upgrade1.1',
  'sfLoggerUpgrade' => 'task/project/upgrade1.1',
  'sfPluginUninstallTask' => 'task/plugin',
  'sfPluginAddChannelTask' => 'task/plugin',
  'sfPluginInstallTask' => 'task/plugin',
  'sfPluginListTask' => 'task/plugin',
  'sfPluginBaseTask' => 'task/plugin',
  'sfPluginUpgradeTask' => 'task/plugin',
  'sfCacheClearTask' => 'task/cache',
  'sfFilesystem' => 'task',
  'sfHelpTask' => 'task/help',
  'sfListTask' => 'task/help',
  'sfTask' => 'task',
  'sfViewCacheManager' => 'view',
  'sfView' => 'view',
  'sfPHPView' => 'view',
  'sfEscapedViewParameterHolder' => 'view',
  'sfPartialView' => 'view',
  'sfViewParameterHolder' => 'view',
  'sfOutputEscaperGetterDecorator' => 'view/escaper',
  'sfOutputEscaperSafe' => 'view/escaper',
  'sfOutputEscaperArrayDecorator' => 'view/escaper',
  'sfOutputEscaper' => 'view/escaper',
  'sfOutputEscaperIteratorDecorator' => 'view/escaper',
  'sfOutputEscaperObjectDecorator' => 'view/escaper',
  'sfAggregateLogger' => 'log',
  'sfStreamLogger' => 'log',
  'sfNoLogger' => 'log',
  'sfLoggerWrapper' => 'log',
  'sfLogger' => 'log',
  'sfWebDebugLogger' => 'log',
  'sfConsoleLogger' => 'log',
  'sfLoggerInterface' => 'log',
  'sfFileLogger' => 'log',
  'sfData' => 'addon',
  'sfPager' => 'addon',
  'sfPearFrontendPlugin' => 'plugin',
  'sfPluginManager' => 'plugin',
  'sfPluginRestException' => 'plugin',
  'sfPearRestPlugin' => 'plugin',
  'sfPearDownloader' => 'plugin',
  'sfPearRest' => 'plugin',
  'sfPearRest11' => 'plugin',
  'sfPearEnvironment' => 'plugin',
  'sfSymfonyPluginManager' => 'plugin',
  'sfPluginDependencyException' => 'plugin',
  'sfPluginRecursiveDependencyException' => 'plugin',
  'sfPearRest10' => 'plugin',
  'sfPluginException' => 'plugin',
  'sfMemcacheCache' => 'cache',
  'sfAPCCache' => 'cache',
  'sfEAcceleratorCache' => 'cache',
  'sfFunctionCache' => 'cache',
  'sfXCacheCache' => 'cache',
  'sfNoCache' => 'cache',
  'sfCache' => 'cache',
  'sfSQLiteCache' => 'cache',
  'sfFileCache' => 'cache',
  'sfMySQLDatabase' => 'database',
  'sfPostgreSQLDatabase' => 'database',
  'sfPDODatabase' => 'database',
  'sfDatabase' => 'database',
  'sfDatabaseManager' => 'database',
  'sfFormFieldSchema' => 'form',
  'sfForm' => 'form',
  'sfFormField' => 'form',
  'sfYamlParser' => 'yaml',
  'sfYamlDumper' => 'yaml',
  'sfYamlInline' => 'yaml',
  'sfYaml' => 'yaml',
  'sfBasicSecurityUser' => 'user',
  'sfUser' => 'user',
  'sfSecurityUser' => 'user',
  'sfControllerException' => 'exception',
  'sfInitializationException' => 'exception',
  'sfFileException' => 'exception',
  'sfDatabaseException' => 'exception',
  'sfFactoryException' => 'exception',
  'sfError404Exception' => 'exception',
  'sfRenderException' => 'exception',
  'sfSecurityException' => 'exception',
  'sfForwardException' => 'exception',
  'sfFilterException' => 'exception',
  'sfParseException' => 'exception',
  'sfStopException' => 'exception',
  'sfViewException' => 'exception',
  'sfConfigurationException' => 'exception',
  'sfCacheException' => 'exception',
  'sfException' => 'exception',
  'sfStorageException' => 'exception',
  'sfEventDispatcher' => 'event',
  'sfEvent' => 'event',
  'sfPathInfoRouting' => 'routing',
  'sfNoRouting' => 'routing',
  'sfPatternRouting' => 'routing',
  'sfRouting' => 'routing',
  'sfRoutingConfigHandler' => 'config',
  'sfGeneratorConfigHandler' => 'config',
  'sfLoader' => 'config',
  'sfConfig' => 'config',
  'sfYamlConfigHandler' => 'config',
  'sfSecurityConfigHandler' => 'config',
  'sfDefineEnvironmentConfigHandler' => 'config',
  'sfCompileConfigHandler' => 'config',
  'sfConfigCache' => 'config',
  'sfSimpleYamlConfigHandler' => 'config',
  'sfRootConfigHandler' => 'config',
  'sfConfigHandler' => 'config',
  'sfFilterConfigHandler' => 'config',
  'sfFactoryConfigHandler' => 'config',
  'sfAutoloadConfigHandler' => 'config',
  'sfApplicationConfiguration' => 'config',
  'sfDatabaseConfigHandler' => 'config',
  'sfProjectConfiguration' => 'config',
  'sfViewConfigHandler' => 'config',
  'sfCacheConfigHandler' => 'config',
  'sfWebResponse' => 'response',
  'sfConsoleResponse' => 'response',
  'sfResponse' => 'response',
  'sfValidatorString' => 'validator',
  'sfValidatorBoolean' => 'validator',
  'sfValidatorEmail' => 'validator',
  'sfValidatorErrorSchema' => 'validator',
  'sfValidatorI18nChoiceCountry' => 'validator/i18n',
  'sfValidatorI18nChoiceLanguage' => 'validator/i18n',
  'sfValidatorUrl' => 'validator',
  'sfValidatorChoice' => 'validator',
  'sfValidatorDecorator' => 'validator',
  'sfValidatorSchemaCompare' => 'validator',
  'sfValidatorError' => 'validator',
  'sfValidatorRegex' => 'validator',
  'sfValidatorCallback' => 'validator',
  'sfValidatorInteger' => 'validator',
  'sfValidatorFromDescription' => 'validator',
  'sfValidatorDate' => 'validator',
  'sfValidatorSchema' => 'validator',
  'sfValidatorAnd' => 'validator',
  'sfValidatorSchemaFilter' => 'validator',
  'sfValidatorBase' => 'validator',
  'sfValidatorSchemaForEach' => 'validator',
  'sfValidatorCSRFToken' => 'validator',
  'sfValidatorFile' => 'validator',
  'sfValidatorChoiceMany' => 'validator',
  'sfValidatorOr' => 'validator',
  'sfValidatorDateTime' => 'validator',
  'sfValidatorPass' => 'validator',
  'sfValidatorNumber' => 'validator',
  'sfSymfonyCommandApplication' => 'command',
  'sfCommandException' => 'command',
  'sfCommandArgumentsException' => 'command',
  'sfAnsiColorFormatter' => 'command',
  'sfCommandArgumentSet' => 'command',
  'sfCommandOption' => 'command',
  'sfCommandArgument' => 'command',
  'sfCommandOptionSet' => 'command',
  'sfFormatter' => 'command',
  'sfCommandManager' => 'command',
  'sfCommandApplication' => 'command',
  'sfCommandLogger' => 'command',
  'sfWebController' => 'controller',
  'sfConsoleController' => 'controller',
  'sfController' => 'controller',
  'sfFrontWebController' => 'controller',
  'sfActions' => 'action',
  'sfActionStackEntry' => 'action',
  'sfAction' => 'action',
  'sfActionStack' => 'action',
  'sfComponent' => 'action',
  'sfComponents' => 'action',
);
}
