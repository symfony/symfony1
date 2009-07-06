<?php

/*
 * This file is part of the symfony package.
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDoctrinePluginConfiguration Class
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineConnectionListener.class.php 11878 2008-09-30 20:14:40Z Jonathan.Wage $
 */
class sfDoctrinePluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    sfConfig::set('sf_orm', 'doctrine');

    if (!sfConfig::get('sf_admin_module_web_dir'))
    {
      sfConfig::set('sf_admin_module_web_dir', '/sfDoctrinePlugin');
    }

    if (sfConfig::get('sf_web_debug'))
    {
      require_once dirname(__FILE__).'/../lib/debug/sfWebDebugPanelDoctrine.class.php';

      $this->dispatcher->connect('debug.web.load_panels', array('sfWebDebugPanelDoctrine', 'listenToAddPanelEvent'));
    }

    if (!sfConfig::has('sf_doctrine_dir'))
    {
      // for BC
      if (sfConfig::has('sfDoctrinePlugin_doctrine_lib_path'))
      {
        sfConfig::set('sf_doctrine_dir', realpath(dirname(sfConfig::get('sfDoctrinePlugin_doctrine_lib_path'))));
      }
      else
      {
        sfConfig::set('sf_doctrine_dir', realpath(dirname(__FILE__).'/../lib/vendor/doctrine'));
      }
    }

    require_once sfConfig::get('sf_doctrine_dir').'/Doctrine.php';
    spl_autoload_register(array('Doctrine', 'autoload'));

    $manager = Doctrine_Manager::getInstance();
    $manager->setAttribute(Doctrine::ATTR_EXPORT, Doctrine::EXPORT_ALL);
    $manager->setAttribute(Doctrine::ATTR_VALIDATE, Doctrine::VALIDATE_ALL);
    $manager->setAttribute(Doctrine::ATTR_RECURSIVE_MERGE_FIXTURES, true);
    $manager->setAttribute(Doctrine::ATTR_AUTO_ACCESSOR_OVERRIDE, true);
    $manager->setAttribute(Doctrine::ATTR_AUTOLOAD_TABLE_CLASSES, true);

    if (method_exists($this->configuration, 'configureDoctrine'))
    {
      $this->configuration->configureDoctrine($manager);
    }
  }
}
