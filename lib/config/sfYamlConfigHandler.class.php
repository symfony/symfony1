<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfYamlConfigHandler is a base class for YAML (.yml) configuration handlers. This class
 * provides a central location for parsing YAML files and detecting required categories.
 *
 * @package    symfony
 * @subpackage config
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfYamlConfigHandler extends sfConfigHandler
{
  protected
    $yamlConfig    = null,
    $defaultConfig = null;

  /**
   * Parse a YAML (.yml) configuration file.
   *
   * @param string An absolute filesystem path to a configuration file.
   *
   * @return string A parsed .yml configuration.
   *
   * @throws sfConfigurationException If a requested configuration file does not exist or is not readable.
   * @throws sfParseException If a requested configuration file is improperly formatted.
   */
  protected function parseYaml($configFile, $param = array())
  {
    if (!is_readable($configFile))
    {
      // can't read the configuration
      $error = sprintf('Configuration file "%s" does not exist or is not readable', $configFile);
      throw new sfConfigurationException($error);
    }

    // parse our config
    $config = sfYaml::load($configFile);

    if ($config === false)
    {
      // configuration couldn't be parsed
      $error = sprintf('Configuration file "%s" could not be parsed', $configFile);
      throw new sfParseException($error);
    }

    // get a list of the required categories
    $categories = $this->getParameterHolder()->get('required_categories', array());
    foreach ($categories as $category)
    {
      if (!isset($config[$category]))
      {
        $error = sprintf('Configuration file "%s" is missing "%s" category', $configFile, $category);
        throw new sfParseException($error);
      }
    }

    return $config;
  }

  public function mergeConfigurations($environment, $files)
  {
    $configs = array();

    foreach ($files as $file)
    {
      if (is_readable($file[1]))
      {
        $config = $this->parseYaml($file[1]);

        if (isset($config[$file[0]]))
        {
          $configs[] = $config[$file[0]];
        }

        if (isset($config[$environment]) && is_array($config[$environment]))
        {
          $configs[] = $config[$environment];
        }
      }
    }

    if ($configs)
    {
      $config = call_user_func_array(array('sfToolkit', 'arrayDeepMerge'), $configs);

      return $config;
    }
    else
    {
      return array();
    }
  }

  protected function mergeConfigValue($keyName, $category)
  {
    $values = array();

    if (isset($this->defaultConfig['default'][$keyName]) && is_array($this->defaultConfig['default'][$keyName]))
    {
      $values = $this->defaultConfig['default'][$keyName];
    }

    if (isset($this->yamlConfig['all'][$keyName]) && is_array($this->yamlConfig['all'][$keyName]))
    {
      $values = array_merge($values, $this->yamlConfig['all'][$keyName]);
    }

    if ($category && isset($this->yamlConfig[$category][$keyName]) && is_array($this->yamlConfig[$category][$keyName]))
    {
      $values = array_merge($values, $this->yamlConfig[$category][$keyName]);
    }

    return $values;
  }

  protected function getConfigValue($keyName, $category, $defaultValue = null)
  {
    if ($category && isset($this->yamlConfig[$category][$keyName]))
    {
      return $this->yamlConfig[$category][$keyName];
    }
    else if (isset($this->yamlConfig['all'][$keyName]))
    {
      return $this->yamlConfig['all'][$keyName];
    }
    else if (isset($this->defaultConfig['default'][$keyName]))
    {
      return $this->defaultConfig['default'][$keyName];
    }

    return $defaultValue;
  }
}

?>