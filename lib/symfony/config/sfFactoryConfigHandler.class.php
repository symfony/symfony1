<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project>
 * (c) 2004, 2005 Sean Kerr.
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
 * @author     Fabien Potencier <fabien.potencier@symfony-project>
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
  public function & execute ($configFile, $param = array())
  {
    // parse the yaml
    $config = $this->parseYaml($configFile);

    // get default configuration
    $defaultConfigFile = SF_SYMFONY_DATA_DIR.'/symfony/config/'.basename($configFile);
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
    if (isset($config[SF_ENVIRONMENT]) && is_array($config[SF_ENVIRONMENT]))
    {
      $myConfig = sfToolkit::array_deep_merge($myConfig, $config[SF_ENVIRONMENT]);
    }

    // init our data and includes arrays
    $includes  = array();
    $inits     = array();
    $instances = array();

    // available list of factories
    $factories = array('controller', 'request', 'storage', 'user', 'security_filter', 'execution_filter', 'view_cache');

    // let's do our fancy work
    foreach ($factories as $factory)
    {
      // see if the factory exists for this controller
      $keys = $myConfig[$factory];

      if (!isset($keys['class']))
      {
        // missing class key
        $error = 'Configuration file "%s" specifies category "%s" with missing class key';
        $error = sprintf($error, $configFile, $factory);

        throw new sfParseException($error);
      }

      $class = $keys['class'];

      if (isset($keys['file']))
      {
        // we have a file to include
        $file =& $keys['file'];
        $file =  $this->replaceConstants($file);
        $file =  $this->replacePath($file);

        if (!is_readable($file))
        {
            // factory file doesn't exist
            $error = 'Configuration file "%s" specifies class "%s" with nonexistent or unreadablefile "%s"';
            $error = sprintf($error, $configFile, $class, $file);

            throw new sfParseException($error);
        }

        // append our data
        $tmp        = "require_once('%s');";
        $includes[] = sprintf($tmp, $file);
      }

      // parse parameters
      $parameters = (isset($keys['param']) ? var_export($keys['param'], true) : 'null');

      // append new data
      switch ($factory)
      {
        case 'controller':
          // append instance creation
          $tmp         = "  \$this->controller = sfController::newInstance('%s');";
          $instances[] = sprintf($tmp, $class);

          // append instance initialization
          $tmp     = "  \$this->controller->initialize(\$this, %s);";
          $inits[] = sprintf($tmp, $parameters);

          break;

        case 'request':
          // append instance creation
          $tmp         = "  \$this->request = sfRequest::newInstance('%s');";
          $instances[] = sprintf($tmp, $class);

          // append instance initialization
          $tmp     = "  \$this->request->initialize(\$this, %s);";
          $inits[] = sprintf($tmp, $parameters);

          break;

        case 'security_filter':
          // append creation/initialization in one swipe
          $tmp     = "\n  if (SF_USE_SECURITY)\n  {\n" .
                     "    \$this->securityFilter = sfSecurityFilter::newInstance('%s');\n".
                     "    \$this->securityFilter->initialize(\$this, %s);\n  }\n";
          $inits[] = sprintf($tmp, $class, $parameters);

          break;

        case 'storage':
          // append instance creation
          $tmp         = "  \$this->storage = sfStorage::newInstance('%s');";
          $instances[] = sprintf($tmp, $class);

          // append instance initialization
          $tmp     = "  \$this->storage->initialize(\$this, %s);";
          $inits[] = sprintf($tmp, $parameters);

          break;

        case 'user':
          // append instance creation
          $tmp         = "  \$this->user = sfUser::newInstance('%s');";
          $instances[] = sprintf($tmp, $class);

          // append instance initialization
          $tmp     = "  \$this->user->initialize(\$this, %s);";
          $inits[] = sprintf($tmp, $parameters);

          break;

        case 'execution_filter':
          // append execution filter class name
          $tmp     = "  \$this->controller->setExecutionFilterClassName('%s');";
          $inits[] = sprintf($tmp, $class);

          break;

        case 'view_cache':
          // append view cache class name
          $tmp     = "\n  if (SF_CACHE)\n  {\n".
                     "    \$this->viewCacheManager->setViewCacheClassName('%s');\n  }\n";
          $inits[] = sprintf($tmp, $class, $parameters);

          break;
      }
    }

    // compile data
    $retval = "<?php\n".
              "// auth-generated by sfFactoryConfigHandler\n".
              "// date: %s\n%s\n%s\n%s\n?>";
    $retval = sprintf($retval, date('m/d/Y H:i:s'), implode("\n", $includes), implode("\n", $instances), implode("\n", $inits));

    return $retval;
  }
}

?>