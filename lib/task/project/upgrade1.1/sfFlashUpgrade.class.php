<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Upgrade flash.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfFlashUpgrade extends sfUpgrade
{
  public function upgrade()
  {
    $this->upgradeActions();
    $this->upgradeTemplates();
    $this->upgradeFilters();
  }

  protected function upgradeActions()
  {
    $phpFinder = sfFinder::type('file')->prune('model')->name('*.php');
    $dirs = array_merge(
      glob(sfConfig::get('sf_root_dir').'/apps/*/modules/*/lib'),
      glob(sfConfig::get('sf_root_dir').'/apps/*/modules/*/actions'),
      array(
        sfConfig::get('sf_root_dir').'/apps/lib',
        sfConfig::get('sf_root_dir').'/lib',
      )
    );

    foreach ($phpFinder->in($dirs) as $file)
    {
      $content = file_get_contents($file);
      $content = str_replace(
        array('$this->setFlash', '$this->getFlash', '$this->hasFlash'),
        array('$this->getUser()->setFlash', '$this->getUser()->getFlash', '$this->getUser()->hasFlash'),
        $content, $count
      );
      if ($count)
      {
        $this->log($this->formatSection('flash', sprintf('Migrating %s', $file)));
        file_put_contents($file, $content);
      }
    }
  }

  protected function upgradeTemplates()
  {
    $phpFinder = sfFinder::type('file')->name('*.php');
    $dirs = array_merge(
      glob(sfConfig::get('sf_root_dir').'/apps/*/modules/*/templates'),
      glob(sfConfig::get('sf_root_dir').'/apps/*/templates')
    );

    foreach ($phpFinder->in($dirs) as $file)
    {
      $content = file_get_contents($file);
      $content = str_replace(
        array('$sf_flash->set', '$sf_flash->get', '$sf_flash->has'),
        array('$sf_user->setFlash', '$sf_user->getFlash', '$sf_user->hasFlash'),
        $content, $count
      );
      if ($count)
      {
        $this->log($this->formatSection('flash', sprintf('Migrating %s', $file)));
        file_put_contents($file, $content);
      }
    }
  }

  protected function upgradeFilters()
  {
    $filtersFinder = sfFinder::type('file')->name('filters.yml');
    $dirs = array_merge(
      glob(sfConfig::get('sf_root_dir').'/apps/*/modules/*/config'),
      glob(sfConfig::get('sf_root_dir').'/apps/*/config'),
      glob(sfConfig::get('sf_root_dir').'/config')
    );

    foreach ($filtersFinder->in($dirs) as $file)
    {
      $content = file_get_contents($file);
      $content = preg_replace("#flash\:\s+~\s*\n#s", '', $content, -1, $count);
      if ($count)
      {
        $this->log($this->formatSection('flash', sprintf('Migrating %s', $file)));
        file_put_contents($file, $content);
      }
    }
  }
}
