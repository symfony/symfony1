<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Upgrade propel.ini.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelUpgrade extends sfUpgrade
{
  public function upgrade()
  {
    $file = sfConfig::get('sf_config_dir').'/propel.ini';

    $content = file_get_contents($file);
    $content = str_replace('addon.propel.builder.', 'plugins.sfPropelPlugin.lib.propel.builder.', $content, $count);

    if ($count)
    {
      $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('propel', sprintf('Migrating %s', $file)))));
      file_put_contents($file, $content);
    }

    // add default date and time format
    $content = file_get_contents($file);
    if (false === strpos($content, 'propel.defaultDateFormat'))
    {
      $content .= <<<EOF

propel.defaultTimeStampFormat = Y-m-d H:i:s
propel.defaultTimeFormat = H:i:s
propel.defaultDateFormat = Y-m-d

EOF;
      $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection('propel', sprintf('Migrating %s', $file)))));
      file_put_contents($file, $content);
    }
  }
}
