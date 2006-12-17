<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

abstract class sfData
{
  protected
    $deleteCurrentData = true,
    $object_references = array();

  public function setDeleteCurrentData($boolean)
  {
    $this->deleteCurrentData = $boolean;
  }

  public function getDeleteCurrentData()
  {
    return $this->deleteCurrentData;
  }

  protected function doLoadDataFromFile($fixture_file)
  {
    // import new datas
    $data = sfYaml::load($fixture_file);

    $this->loadDataFromArray($data);
  }

  abstract public function loadDataFromArray($data);

  protected function doLoadData($fixture_files)
  {
    $this->object_references = array();
    $this->maps = array();

    sort($fixture_files);
    foreach ($fixture_files as $fixture_file)
    {
      $this->doLoadDataFromFile($fixture_file);
    }
  }

  protected function getFiles($directory_or_file = null)
  {
    // directory or file?
    $fixture_files = array();
    if (!$directory_or_file)
    {
      $directory_or_file = sfConfig::get('sf_data_dir').'/fixtures';
    }

    if (is_file($directory_or_file))
    {
      $fixture_files[] = $directory_or_file;
    }
    else if (is_dir($directory_or_file))
    {
      $fixture_files = sfFinder::type('file')->name('*.yml')->in($directory_or_file);
    }
    else
    {
      throw new sfInitializationException('You must give a directory or a file.');
    }

    return $fixture_files;
  }
}
