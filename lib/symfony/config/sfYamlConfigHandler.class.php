<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project>
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
 * @author     Fabien Potencier <fabien.potencier@symfony-project>
 * @version    SVN: $Id$
 */
abstract class sfYamlConfigHandler extends sfConfigHandler
{
  protected
    $config        = null,
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
  protected function & parseYaml ($configFile, $param = array())
  {
    if (!is_readable($configFile))
    {
      // can't read the configuration
      $error = 'Configuration file "%s" does not exist or is not readable';
      $error = sprintf($error, $configFile);

      throw new sfConfigurationException($error);
    }

    // parse our config
    $config = sfYaml::load($configFile);

    if ($config === false)
    {
      // configuration couldn't be parsed
      $error = 'Configuration file "%s" could not be parsed';
      $error = sprintf($error, $configFile);

      throw new sfParseException($error);
    }

    // get a list of the required categories
    $categories = $this->getParameterHolder()->get('required_categories', array());
    foreach ($categories as $category)
    {
      if (!isset($config[$category]))
      {
        $error = 'Configuration file "%s" is missing "%s" category';
        $error = sprintf($error, $configFile, $category);

        throw new sfParseException($error);
      }
    }

    return $config;
  }

  protected function mergeConfigValue($keyName, $category)
  {
    $values = array();

    if (isset($this->defaultConfig['default'][$keyName]) && is_array($this->defaultConfig['default'][$keyName]))
    {
      $values = $this->defaultConfig['default'][$keyName];
    }

    if (isset($this->config['all'][$keyName]) && is_array($this->config['all'][$keyName]))
    {
      $values = array_merge($values, $this->config['all'][$keyName]);
    }

    if ($category && isset($this->config[$category][$keyName]) && is_array($this->config[$category][$keyName]))
    {
      $values = array_merge($values, $this->config[$category][$keyName]);
    }

    return $values;
  }

  protected function getConfigValue($keyName, $category)
  {
    if ($category && isset($this->config[$category][$keyName]))
    {
      return $this->config[$category][$keyName];
    }
    else if (isset($this->config['all'][$keyName]))
    {
      return $this->config['all'][$keyName];
    }
    else if (isset($this->defaultConfig['default'][$keyName]))
    {
      return $this->defaultConfig['default'][$keyName];
    }
  }
}

?>