<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Lists YAML files that use the deprecated Boolean notations.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfTasksUpgrade.class.php 20941 2009-08-08 14:11:51Z Kris.Wallsmith $
 */
class sfYamlUpgrade extends sfUpgrade
{
  public function upgrade()
  {
    $specVersion = sfYaml::getSpecVersion();

    $queue = array();
    $success = true;

    $finder = sfFinder::type('file')->name('*.yml')->prune('vendor');
    foreach ($finder->in(sfConfig::get('sf_root_dir')) as $file)
    {
      // attempt to upgrade booleans
      $original = file_get_contents($file);
      $upgraded = sfToolkit::pregtr($original, array(
        '/^([^:]+: +)(?:on|y(?:es)?|\+)(\s*)$/im' => '\\1true\\2',
        '/^([^:]+: +)(?:off|no?|-)(\s*)$/im'      => '\\1false\\2',
      ));

      $this->logSection('yaml', 'Trying to upgrade '.sfDebug::shortenFilePath($file));

      sfYaml::setSpecVersion('1.1');
      $yaml11 = sfYaml::load($original);

      sfYaml::setSpecVersion('1.2');
      $yaml12 = sfYaml::load($upgraded);

      if ($yaml11 == $yaml12)
      {
        if ($original != $upgraded)
        {
          $queue[$file] = $upgraded;
        }
      }
      else
      {
        $this->logSection('yaml', 'Unable to upgrade '.sfDebug::shortenFilePath($file));

        // force project to use YAML 1.1 spec
        if ('1.1' != $specVersion)
        {
          $class = sfClassManipulator::fromFile(sfConfig::get('sf_config_dir').'/ProjectConfiguration.class.php');

          $original = $class->getCode();
          $modified = $class->wrapMethod('setup', 'sfYaml::setSpecVersion(\'1.1\');');

          if ($original != $modified)
          {
            $this->logSection('yaml', 'Forcing YAML 1.1 spec');

            $this->getFilesystem()->touch($class->getFile());
            $class->save();
          }
          else
          {
            $this->logBlock(array('', 'Unable to either upgrade YAML files or force 1.1 spec.', '(see UPGRADE file for more information)', ''), 'ERROR');
          }
        }

        $success = false;
        break;
      }
    }

    if ($success)
    {
      // upgrades were all successful, write changes to the filesystem
      foreach ($queue as $file => $contents)
      {
        $this->getFilesystem()->touch($file);
        file_put_contents($file, $contents);
      }

      // remove 1.1 spec setting
      if ('1.1' == $specVersion)
      {
        $file = sfConfig::get('sf_config_dir').'/ProjectConfiguration.class.php';
        $original = file_get_contents($file);
        $modified = preg_replace('/^\s*sfYaml::setSpecVersion\(\'1\.1\'\);\n/im', '', $original);

        if ($original != $modified)
        {
          $this->logSection('yaml', 'Removing setting of YAML 1.1 spec');

          $this->getFilesystem()->touch($file);
          file_put_contents($file, $modified);
        }
      }
    }
  }
}
