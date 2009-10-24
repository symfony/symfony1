<?php

require_once 'propel/engine/database/model/Behavior.php';

/**
 * Base behavior class.
 * 
 * @package     sfPropelPlugin
 * @subpackage  behavior
 * @author      Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version     SVN: $Id$
 */
abstract class SfPropelBehaviorBase extends Behavior
{
  protected
    $buildProperties = null;

  /**
   * Returns a build property from propel.ini.
   * 
   * @param string $name
   * 
   * @return mixed
   */
  protected function getBuildProperty($name)
  {
    if (null === $this->buildProperties)
    {
      $this->buildProperties = new Properties();
      $this->buildProperties->load(new PhingFile(sfConfig::get('sf_config_dir').'/propel.ini'));
    }

    return $this->buildProperties->getProperty($name);
  }

  /**
   * Returns true if the current behavior has been disabled.
   * 
   * @return boolean
   */
  protected function isDisabled()
  {
    return 'true' == $this->getParameter('disabled');
  }
}
