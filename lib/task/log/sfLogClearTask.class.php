<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Clears log files.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfLogClearTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->aliases = array('log-purge');
    $this->namespace = 'log';
    $this->name = 'clear';
    $this->briefDescription = 'Clears log files';

    $this->detailedDescription = <<<EOF
The [log:clear|INFO] task clears the symfony log files:

  [./symfony log:clear|INFO]

This tasks uses the [logging.yml|COMMENT] file for its configuration.
To clear a log file, the [active|COMMENT] property must be set to [true|COMMENT]
and the [purge|COMMENT] property must also be set to [true|COMMENT].
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $defaultLogging = sfYaml::load(sfConfig::get('sf_symfony_data_dir').'/config/logging.yml');
    $apps = sfFinder::type('dir')->maxdepth(0)->relative()->ignore_version_control()->in('apps');
    $ignore = array('all', 'default');

    foreach ($apps as $app)
    {
      $logging = sfYaml::load(sfConfig::get('sf_app_dir').'/'.$app.'/config/logging.yml');
      $logging = array_merge($defaultLogging, $logging);

      foreach ($logging as $env => $config)
      {
        if (in_array($env, $ignore))
        {
          continue;
        }

        $props = array_merge($defaultLogging['default'], is_array($config) ? $config : array());
        $active = isset($props['active']) ? $props['active'] : true;
        $purge  = isset($props['purge']) ? $props['purge'] : true;
        if ($active && $purge)
        {
          $filename = sfConfig::get('sf_log_dir').'/'.$app.'_'.$env.'.log';
          if (file_exists($filename))
          {
            $this->filesystem->remove($filename);
          }
        }
      }
    }
  }
}
