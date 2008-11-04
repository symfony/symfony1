<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Upgrades the project configuration class for backward compatibility.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfConfigurationUpgrade extends sfUpgrade
{
  public function upgrade()
  {
    $file = sfConfig::get('sf_config_dir').'/ProjectConfiguration.class.php';
    $content = file_get_contents($file);

    if (!preg_match('/(enablePlugins|disablePlugins|enableAllPluginsExcept)/', $content))
    {
      $content = preg_replace("#(setup\(\)\s+{\s+)#s", "$1  \$this->enableAllPluginsExcept('sfDoctrinePlugin');\n  ", $content, -1, $count);
      if ($count)
      {
        $this->logSection('config', sprintf('Migrating %s', $file));
        file_put_contents($file, $content);
      }
    }
  }
}
