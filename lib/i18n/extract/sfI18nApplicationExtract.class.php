<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage i18n
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfI18nApplicationExtract extends sfI18nExtract
{
  protected $extractObjects = array();

  /**
   * Configures the current extract object.
   */
  public function configure()
  {
    $this->extractObjects = array();

    // Modules
    $moduleNames = sfFinder::type('dir')->maxdepth(0)->ignore_version_control()->relative()->in(sfConfig::get('sf_app_dir').'/modules');
    foreach ($moduleNames as $moduleName)
    {
      $moduleExtract = new sfI18nModuleExtract();
      $moduleExtract->initialize('en', array('module' => $moduleName));

      $this->extractObjects[] = $moduleExtract;
    }
  }

  /**
   * Extracts i18n strings.
   *
   * This class must be implemented by subclasses.
   */
  public function extract()
  {
    foreach ($this->extractObjects as $extractObject)
    {
      $extractObject->extract();
    }

    // Add global templates
    $this->extractFromPhpFiles(sfConfig::get('sf_app_template_dir'));

    // Ad global librairies
    $this->extractFromPhpFiles(sfConfig::get('sf_app_lib_dir'));
  }

  /**
   * Gets the current i18n strings.
   *
   * @param array An array of i18n strings
   */
  public function getCurrentMessages()
  {
    return array_unique(array_merge($this->currentMessages, $this->aggregateMessages('getCurrentMessages')));
  }

  /**
   * Gets all i18n strings seen during the extraction process.
   *
   * @param array An array of i18n strings
   */
  public function getAllSeenMessages()
  {
    return array_unique(array_merge($this->allSeenMessages, $this->aggregateMessages('getAllSeenMessages')));
  }

  protected public function aggregateMessages($method)
  {
    $messages = array();
    foreach ($this->extractObjects as $extractObject)
    {
      $messages = array_merge($messages, $extractObject->$method());
    }

    return array_unique($messages);
  }
}
