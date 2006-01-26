<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFactoryConfigHandler allows you to specify which factory implementation the
 * system will use.
 *
 * @package    symfony
 * @subpackage config
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfFactoryConfigHandler extends sfYamlConfigHandler
{
  /**
   * Execute this configuration handler.
   *
   * @param string An absolute filesystem path to a configuration file.
   *
   * @return string Data to be written to a cache file.
   *
   * @throws <b>sfConfigurationException</b> If a requested configuration file does not exist or is not readable.
   * @throws <b>sfParseException</b> If a requested configuration file is improperly formatted.
   */
  public function execute($configFile, $param = array())
  {
    // parse the yaml
    $config = $this->parseYaml($configFile);

    // get default configuration
    $defaultConfigFile = sfConfig::get('sf_symfony_data_dir').'/config/'.basename($configFile);
    if (is_readable($defaultConfigFile))
    {
      $defaultConfig = $this->parseYaml($defaultConfigFile);
      $defaultConfig = $defaultConfig['default'];
    }
    if (!isset($defaultConfig))
    {
      $defaultConfig = array();
    }

    // get all configuration
    if (isset($config['all']))
    {
      $allConfig = $config['all'];
    }
    if (!isset($allConfig))
    {
      $allConfig = array();
    }

    // merge with environment configuration if needed
    $myConfig = sfToolkit::array_deep_merge($defaultConfig, $allConfig);
    $sf_environment = sfConfig::get('sf_environment');
    if (isset($config[$sf_environment]) && is_array($config[$sf_environment]))
    {
      $myConfig = sfToolkit::array_deep_merge($myConfig, $config[$sf_environment]);
    }

    // init our data and includes arrays
    $includes  = array();
    $inits     = array();
    $instances = array();

    // available list of factories
    $factories = array('controller', 'request', 'response', 'storage', 'user', 'security_filter', 'execution_filter', 'rendering_filter', 'view_cache');

    // let's do our fancy work
    foreach ($factories as $factory)
    {
      // see if the factory exists for this controller
      $keys = $myConfig[$factory];

      if (!isset($keys['class']))
      {
        // missing class key
        $error = sprintf('Configuration file "%s" specifies category "%s" with missing class key', $configFile, $factory);
        throw new sfParseException($error);
      }

      $class = $keys['class'];

      if (isset($keys['file']))
      {
        // we have a file to include
        $file = $this->replaceConstants($keys['file']);
        $file = $this->replacePath($file);

        if (!is_readable($file))
        {
            // factory file doesn't exist
            $error = sprintf('Configuration file "%s" specifies class "%s" with nonexistent or unreadablefile "%s"',
                             $configFile, $class, $file);
            throw new sfParseException($error);
        }

        // append our data
        $includes[] = sprintf("require_once('%s');", $file);
      }

      // parse parameters
      $parameters = (isset($keys['param']) ? var_export($keys['param'], true) : 'null');

      // append new data
      switch ($factory)
      {
        case 'controller':
          // append instance creation
          $instances[] = sprintf("  \$this->controller = sfController::newInstance('%s');", $class);

          // append instance initialization
          $inits[] = "  \$this->controller->initialize(\$this);";
          break;

        case 'request':
          // append instance creation
          $instances[] = sprintf("  \$this->request = sfRequest::newInstance('%s');", $class);

          // append instance initialization
          $inits[] = sprintf("  \$this->request->initialize(\$this, %s);", $parameters);
          break;

        case 'response':
          // append instance creation
          $instances[] = sprintf("  \$this->response = sfResponse::newInstance('%s');", $class);

          // append instance initialization
          $inits[] = sprintf("  \$this->response->initialize(\$this);");
          break;

        case 'security_filter':
          // append creation/initialization in one swipe
          $inits[] = sprintf("\n  if (sfConfig::get('sf_use_security'))\n  {\n" .
                             "    \$this->securityFilter = sfSecurityFilter::newInstance('%s');\n".
                             "    \$this->securityFilter->initialize(\$this, %s);\n  }\n",
                             $class, $parameters);
          break;

        case 'storage':
          // append instance creation
          $instances[] = sprintf("  \$this->storage = sfStorage::newInstance('%s');", $class);

          // append instance initialization
          $inits[] = sprintf("  \$this->storage->initialize(\$this, %s);", $parameters);
          break;

        case 'user':
          // append instance creation
          $instances[] = sprintf("  \$this->user = sfUser::newInstance('%s');", $class);

          // append instance initialization
          $inits[] = sprintf("  \$this->user->initialize(\$this, %s);", $parameters);
          break;

        case 'execution_filter':
          // append execution filter class name
          $inits[] = sprintf("  \$this->controller->setExecutionFilterClassName('%s');", $class);
          break;

        case 'rendering_filter':
          // append rendering filter class name
          $inits[] = sprintf("  \$this->controller->setRenderingFilterClassName('%s');", $class);
          break;

        case 'view_cache':
          // append view cache class name
          $inits[] = sprintf("\n  if (sfConfig::get('sf_cache'))\n  {\n".
                             "    \$this->viewCacheManager->setViewCacheClassName('%s');\n  }",
                             $class);
          break;
      }
    }

    // compile data
    $retval = sprintf("<?php\n".
                      "// auto-generated by sfFactoryConfigHandler\n".
                      "// date: %s\n%s\n%s\n%s\n?>",
                      date('Y/m/d H:i:s'), implode("\n", $includes),
                      implode("\n", $instances), implode("\n", $inits));

    return $retval;
  }
}

?>