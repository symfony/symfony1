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
    $found = false;
    $finder = sfFinder::type('file')->name('*.yml');
    foreach ($finder->in(sfConfig::get('sf_root_dir')) as $file)
    {
      sfYaml::setSpecVersion('1.1');
      $yaml11 = sfYaml::load($file);

      sfYaml::setSpecVersion('1.2');
      $yaml12 = sfYaml::load($file);

      if ($yaml11 != $yaml12)
      {
        $found = true;

        $this->logSection('yaml', 'You must upgrade '.$file, null, 'ERROR');
      }
    }

    if ($found)
    {
      $this->logBlock(array('', 'You must upgrade the YAML files listed above', '(see UPGRADE file for more information)', ''), 'ERROR');
    }
  }
}
