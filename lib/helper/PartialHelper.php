<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PartialHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

function include_partial($name, $vars = array())
{
  // partial is in another module?
  $sep = strpos($name, '/');
  if ($sep)
  {
    $type = strtolower(substr($name, 0, $sep));
    $filename = '_'.substr($name, $sep + 1).'.php';
  }
  else
  {
    $type = '';
    $filename = '_'.$name.'.php';
  }

  $context = sfContext::getInstance();

  $lastActionEntry = $context->getActionStack()->getLastEntry();
  $firstActionEntry = $context->getActionStack()->getFirstEntry();

  // global variables
  $vars = array_merge($vars, array(
    'sf_context'       => $context,
    'sf_params'        => $context->getRequest()->getParameterHolder(),
    'sf_request'       => $context->getRequest(),
    'sf_user'          => $context->getUser(),
    'sf_last_module'   => $lastActionEntry->getModuleName(),
    'sf_last_action'   => $lastActionEntry->getActionName(),
    'sf_first_module'  => $firstActionEntry->getModuleName(),
    'sf_first_action'  => $firstActionEntry->getActionName(),
  ));

  if (sfConfig::get('sf_use_flash'))
  {
    $sf_flash = new sfParameterHolder();
    $sf_flash->add($context->getUser()->getAttributeHolder()->getAll('symfony/flash'));
    $vars['sf_flash'] = $sf_flash;
  }

  // local action variables
  $action = $context->getActionStack()->getLastEntry()->getActionInstance();
  if (method_exists($action, 'getVars'))
  {
    $vars = array_merge($vars, $action->getVars());
  }

  extract($vars);

  // render to client
  if ($sep && $type == 'global')
  {
    $partial = sfConfig::get('sf_app_template_dir').DIRECTORY_SEPARATOR.$filename;
  }
  else if ($sep)
  {
    $partial = sfConfig::get('sf_app_module_dir').DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_template_dir_name').DIRECTORY_SEPARATOR.$filename;
  }
  else
  {
    $current_module = sfContext::getInstance()->getActionStack()->getLastEntry()->getModuleName();
    $partial = sfConfig::get('sf_app_dir').DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_dir_name').DIRECTORY_SEPARATOR.$current_module.DIRECTORY_SEPARATOR.sfConfig::get('sf_app_module_template_dir_name').DIRECTORY_SEPARATOR.$filename;
  }

  if (!is_readable($partial))
  {
    $ok = false;

    $current_module = sfContext::getInstance()->getActionStack()->getLastEntry()->getModuleName();

    // search partial for generated templates in cache
    $partial = sfConfig::get('sf_module_cache_dir').'/auto'.ucfirst($current_module).'/templates/'.$filename;
    if (is_readable($partial))
    {
      $ok = true;
    }
    else
    {
      // search partial in a symfony module directory
      $partial = sfConfig::get('sf_symfony_data_dir').'/modules/'.$current_module.'/templates/'.$filename;
      if (is_readable($partial))
      {
        $ok = true;
      }
    }

    if (!$ok)
    {
      // the partial isn't readable
      $error = 'The partial "%s" does not exist or is unreadable';
      $error = sprintf($error, $filename);

      throw new sfRenderException($error);
    }
  }

  require $partial;
}

?>