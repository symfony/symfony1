<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfGeneratorManager helps generate classes, views and templates for scaffolding, admin interface, ...
 *
 * @package    symfony
 * @subpackage generator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfGeneratorManager
{
  protected
    $configuration = null;

  /**
   * Class constructor.
   *
   * @param sfProjectConfiguration $configuration A sfProjectConfiguration instance
   *
   * @see initialize()
   */
  public function __construct(sfProjectConfiguration $configuration)
  {
    $this->initialize($configuration);
  }

  /**
   * Initializes the sfGeneratorManager instance.
   *
   * @param sfProjectConfiguration $configuration A sfProjectConfiguration instance
   */
  public function initialize(sfProjectConfiguration $configuration)
  {
    $this->configuration = $configuration;
  }

  /**
   * Returns the current configuration instance.
   *
   * @return sfProjectConfiguration A sfProjectConfiguration instance
   */
  public function getConfiguration()
  {
    return $this->configuration;
  }

  public function save($path, $content)
  {
    $path = sfConfig::get('sf_module_cache_dir').DIRECTORY_SEPARATOR.$path;

    if (!is_dir(dirname($path)))
    {
      $current_umask = umask(0000);
      @mkdir(dirname($path), 0777, true);
      umask($current_umask);
    }

    return file_put_contents($path, $content);
  }

  /**
   * Generates classes and templates for a given generator class.
   *
   * @param string $generatorClass    The generator class name
   * @param array  $param             An array of parameters
   *
   * @return string The cache for the configuration file
   */
  public function generate($generatorClass, $param)
  {
    $generator = new $generatorClass($this);

    return $generator->generate($param);
  }
}
