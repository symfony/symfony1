<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfGenerator is the abstract base class for all generators.
 *
 * @package    symfony
 * @subpackage generator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfGenerator
{
  protected
    $generatorClass      = '',
    $generatorManager    = null,
    $generatedModuleName = '',
    $theme               = 'default',
    $moduleName          = '';

  /**
   * Class constructor.
   *
   * @see initialize()
   */
  public function __construct(sfGeneratorManager $generatorManager)
  {
    $this->initialize($generatorManager);
  }

  /**
   * Initializes the current sfGenerator instance.
   *
   * @param sfGeneratorManager A sfGeneratorManager instance
   */
  public function initialize(sfGeneratorManager $generatorManager)
  {
    $this->generatorManager = $generatorManager;
  }

  /**
   * Generates classes and templates.
   *
   * @param array An array of parameters
   *
   * @return string The cache for the configuration file
   */
  abstract public function generate($params = array());

  /**
   * Generates PHP files for a given module name.
   *
   * @param string The name of module name to generate
   * @param array  A list of template files to generate
   */
  protected function generatePhpFiles($generatedModuleName, $files = array())
  {
    foreach ($files as $file)
    {
      $this->getGeneratorManager()->save($generatedModuleName.'/'.$file, $this->evalTemplate($file));
    }
  }

  /**
   * Evaluates a template file.
   *
   * @param string The template file path
   *
   * @return string The evaluated template
   */
  protected function evalTemplate($templateFile)
  {
    $templateFile = sfLoader::getGeneratorTemplate($this->getGeneratorClass(), $this->getTheme(), $templateFile);

    // eval template file
    ob_start();
    require($templateFile);
    $content = ob_get_clean();

    // replace [?php and ?]
    return $this->replacePhpMarks($content);
  }

  /**
   * Replaces PHP marks by <?php ?>.
   *
   * @param string The PHP code
   *
   * @return string The converted PHP code
   */
  protected function replacePhpMarks($text)
  {
    // replace [?php and ?]
    return str_replace(array('[?php', '[?=', '?]'), array('<?php', '<?php echo', '?>'), $text);
  }

  /**
   * Gets the generator class.
   *
   * @return string The generator class
   */
  public function getGeneratorClass()
  {
    return $this->generatorClass;
  }

  /**
   * Sets the generator class.
   *
   * @param string The generator class
   */
  public function setGeneratorClass($generator_class)
  {
    $this->generatorClass = $generator_class;
  }

  /**
   * Gets the sfGeneratorManager instance.
   *
   * @return string The sfGeneratorManager instance
   */
  protected function getGeneratorManager()
  {
    return $this->generatorManager;
  }

  /**
   * Gets the module name of the generated module.
   *
   * @return string The module name
   */
  public function getGeneratedModuleName()
  {
    return $this->generatedModuleName;
  }

  /**
   * Sets the module name of the generated module.
   *
   * @param string The module name
   */
  public function setGeneratedModuleName($module_name)
  {
    $this->generatedModuleName = $module_name;
  }

  /**
   * Gets the module name.
   *
   * @return string The module name
   */
  public function getModuleName()
  {
    return $this->moduleName;
  }

  /**
   * Sets the module name.
   *
   * @param string The module name
   */
  public function setModuleName($module_name)
  {
    $this->moduleName = $module_name;
  }

  /**
   * Gets the theme name.
   *
   * @return string The theme name
   */
  public function getTheme()
  {
    return $this->theme;
  }

  /**
   * Sets the theme name.
   *
   * @param string The theme name
   */
  public function setTheme($theme)
  {
    $this->theme = $theme;
  }
}
