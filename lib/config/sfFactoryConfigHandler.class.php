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
   * Executes this configuration handler.
   *
   * @param array An array of absolute filesystem path to a configuration file
   *
   * @return string Data to be written to a cache file
   *
   * @throws <b>sfConfigurationException</b> If a requested configuration file does not exist or is not readable
   * @throws <b>sfParseException</b> If a requested configuration file is improperly formatted
   */
  public function execute($configFiles)
  {
    // parse the yaml
    $myConfig = $this->parseYamls($configFiles);

    $myConfig = sfToolkit::arrayDeepMerge(
      isset($myConfig['default']) && is_array($myConfig['default']) ? $myConfig['default'] : array(),
      isset($myConfig['all']) && is_array($myConfig['all']) ? $myConfig['all'] : array(),
      isset($myConfig[sfConfig::get('sf_environment')]) && is_array($myConfig[sfConfig::get('sf_environment')]) ? $myConfig[sfConfig::get('sf_environment')] : array()
    );

    // init our data and includes arrays
    $includes  = array();
    $instances = array();

    // available list of factories
    $factories = array('logger', 'routing', 'controller', 'request', 'response', 'storage', 'i18n', 'user', 'view_cache');

    // let's do our fancy work
    foreach ($factories as $factory)
    {
      // see if the factory exists for this controller
      $keys = $myConfig[$factory];

      if (!isset($keys['class']))
      {
        // missing class key
        throw new sfParseException(sprintf('Configuration file "%s" specifies category "%s" with missing class key.', $configFiles[0], $factory));
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
          throw new sfParseException(sprintf('Configuration file "%s" specifies class "%s" with nonexistent or unreadable file "%s".', $configFiles[0], $class, $file));
        }

        // append our data
        $includes[] = sprintf("require_once('%s');", $file);
      }

      // parse parameters
      if (isset($keys['param']))
      {
        $parameters = array();
        foreach ($keys['param'] as $key => $value)
        {
          $parameters[$key] = $this->replaceConstants($value);
        }
      }
      else
      {
        $parameters = null;
      }

      // append new data
      switch ($factory)
      {
        case 'controller':
          $instances[] = sprintf("  \$class = sfConfig::get('sf_factory_controller', '%s');\n   \$this->factories['controller'] = new \$class(\$this);", $class);
          break;

        case 'request':
          $instances[] = sprintf("  \$class = sfConfig::get('sf_factory_request', '%s');\n   \$this->factories['request'] = new \$class(\$this->dispatcher, sfConfig::get('sf_factory_request_parameters', %s), sfConfig::get('sf_factory_request_attributes', array()));", $class, var_export($parameters, true));
          break;

        case 'response':
          $parameters = array_merge(array('charset' => sfConfig::get('sf_charset'), 'logging' => sfConfig::get('sf_logging_enabled')), is_array($parameters) ? $parameters : array());
          $instances[] = sprintf("  \$class = sfConfig::get('sf_factory_response', '%s');\n  \$this->factories['response'] = new \$class(\$this->dispatcher, sfConfig::get('sf_factory_response_parameters', %s));", $class, var_export($parameters, true));

          $instances[] = sprintf("  if ('HEAD' == \$this->factories['request']->getMethodName())\n  {  \n    \$this->factories['response']->setHeaderOnly(true);\n  }\n");
          break;

        case 'storage':
          $defaultParameters = array();
          $defaultParameters[] = sprintf("'auto_shutdown' => false, 'session_id' => \$this->getRequest()->getParameter('%s'),", $parameters['session_name']);
          if (is_subclass_of($class, 'sfDatabaseSessionStorage'))
          {
            $defaultParameters[] = sprintf("'database' => \$this->getDatabaseManager()->getDatabase('%s'),", isset($parameters['database']) ? $parameters['database'] : 'default');
            unset($parameters['database']);
          }

          $instances[] = sprintf("  \$class = sfConfig::get('sf_factory_storage', '%s');\n  \$this->factories['storage'] = new \$class(array_merge(array(\n%s\n), sfConfig::get('sf_factory_storage_parameters', %s)));", $class, implode("\n", $defaultParameters), var_export($parameters, true));
          break;

        case 'user':
          $instances[] = sprintf("  \$class = sfConfig::get('sf_factory_user', '%s');\n  \$this->factories['user'] = new \$class(\$this->dispatcher, \$this->factories['storage'], array_merge(array('auto_shutdown' => false, 'culture' => \$this->factories['request']->getParameter('sf_culture'), 'default_culture' => sfConfig::get('sf_default_culture', 'en'), 'use_flash' => sfConfig::get('sf_use_flash'), 'logging' => sfConfig::get('sf_logging_enabled')), sfConfig::get('sf_factory_user_parameters', %s)));", $class, var_export(is_array($parameters) ? $parameters : array(), true));
          break;

        case 'view_cache':
          $instances[] = sprintf("\n  if (sfConfig::get('sf_cache'))\n  {\n".
                             "    \$class = sfConfig::get('sf_factory_view_cache', '%s');\n".
                             "    \$cache = new \$class(sfConfig::get('sf_factory_view_cache_parameters', %s));\n".
                             "    \$this->factories['viewCacheManager'] = new sfViewCacheManager(\$this, \$cache);\n".
                             "  }\n".
                             "  else\n".
                             "  {\n".
                             "    \$this->factories['viewCacheManager'] = null;\n".
                             "  }\n",
                             $class, var_export($parameters, true));
          break;

        case 'i18n':
          if (isset($parameters['cache']))
          {
            $cache = sprintf("    \$cache = new %s(%s);\n", $parameters['cache']['class'], var_export($parameters['cache']['param'], true));
            unset($parameters['cache']);
          }
          else
          {
            $cache = "    \$cache = null;\n";
          }

          $instances[] = sprintf("\n  if (sfConfig::get('sf_i18n'))\n  {\n".
                     "    \$class = sfConfig::get('sf_factory_i18n', '%s');\n".
                     "%s".
                     "    \$this->factories['i18n'] = new \$class(\$this->dispatcher, \$cache, %s);\n".
                     "  }\n"
                     , $class, $cache, var_export($parameters, true)
                     );
          break;

        case 'routing':
          $instances[] = sprintf("  \$class = sfConfig::get('sf_factory_routing', '%s');\n  \$this->factories['routing'] = new \$class(\$this->dispatcher, array_merge(array('auto_shutdown' => false, 'default_module' => sfConfig::get('sf_default_module'), 'default_action' => sfConfig::get('sf_default_action'), 'logging' => sfConfig::get('sf_logging_enabled')), sfConfig::get('sf_factory_routing_parameters', %s)));", $class, var_export(is_array($parameters) ? $parameters : array(), true));
          if (isset($parameters['load_configuration']) && $parameters['load_configuration'])
          {
            $instances[] = "  \$this->factories['routing']->loadConfiguration();\n";
          }
          break;

        case 'logger':
          $loggers = '';
          if (isset($parameters['loggers']))
          {
            foreach ($parameters['loggers'] as $name => $keys)
            {
              if (isset($keys['enabled']) && !$this->replaceConstants($keys['enabled']))
              {
                continue;
              }

              if (!isset($keys['class']))
              {
                // missing class key
                throw new sfParseException(sprintf('Configuration file "%s" specifies logger "%s" with missing class key.', $configFiles[0], $name));
              }

              $condition = true;
              if (isset($keys['param']['condition']))
              {
                $condition = $this->replaceConstants($keys['param']['condition']);
                unset($keys['param']['condition']);
              }

              if ($condition)
              {
                // create logger instance
                $loggers .= sprintf("\n\$logger = new %s(\$this->dispatcher, array_merge(array('auto_shutdown' => false), %s));\n\$this->factories['logger']->addLogger(\$logger);\n", 
                              $keys['class'],
                              isset($keys['param']) ? var_export($keys['param'], true) : 'array()'
                            );
              }
            }

            unset($parameters['loggers']);
          }

          $instances[] = sprintf(
                         "  \$class = sfConfig::get('sf_factory_logger', '%s');\n  \$this->factories['logger'] = new \$class(\$this->dispatcher, array_merge(array('auto_shutdown' => false), sfConfig::get('sf_factory_logger_parameters', %s)));\n".
                         "  %s"
                         , $class, var_export(is_array($parameters) ? $parameters : array(), true), $loggers);
          break;
      }
    }

    // compile data
    $retval = sprintf("<?php\n".
                      "// auto-generated by sfFactoryConfigHandler\n".
                      "// date: %s\n%s\n%s\n",
                      date('Y/m/d H:i:s'), implode("\n", $includes),
                      implode("\n", $instances));

    return $retval;
  }
}
