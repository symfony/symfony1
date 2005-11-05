<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
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
    'context'       => $context,
    'params'        => $context->getRequest()->getParameterHolder(),
    'request'       => $context->getRequest(),
    'user'          => $context->getUser(),
    'last_module'   => $lastActionEntry->getModuleName(),
    'last_action'   => $lastActionEntry->getActionName(),
    'first_module'  => $firstActionEntry->getModuleName(),
    'first_action'  => $firstActionEntry->getActionName(),
  ));

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
    require SF_APP_TEMPLATE_DIR.DS.$filename;
  }
  else if ($sep)
  {
    require SF_APP_MODULE_DIR.DS.$type.DS.SF_APP_MODULE_TEMPLATE_DIR_NAME.DS.$filename;
  }
  else
  {
    $current_module = sfContext::getInstance()->getActionStack()->getLastEntry()->getModuleName();
    require SF_APP_DIR.DS.SF_APP_MODULE_DIR_NAME.DS.$current_module.DS.SF_APP_MODULE_TEMPLATE_DIR_NAME.DS.$filename;
  }
}

?>