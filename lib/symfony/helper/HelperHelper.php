<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * HelperHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

function use_helpers()
{
  foreach (func_get_args() as $helperName)
  {
    use_helper($helperName);
  }
}

function use_helper($helperName)
{
  if (is_readable(SF_SYMFONY_LIB_DIR.'/symfony/helper/'.$helperName.'Helper.php'))
  {
    // global helper
    include_once('symfony/helper/'.$helperName.'Helper.php');
  }
  else if (is_readable(SF_APP_MODULE_DIR.'/'.sfContext::getInstance()->getModuleName().'/'.SF_APP_MODULE_LIB_DIR_NAME.'/helper/'.$helperName.'Helper.php'))
  {
    // current module helper
    include_once(SF_APP_MODULE_DIR.'/'.sfContext::getInstance()->getModuleName().'/'.SF_APP_MODULE_LIB_DIR_NAME.'/helper/'.$helperName.'Helper.php');
  }
  else
  {
    // helper in include_path
    include_once('helper/'.$helperName.'Helper.php');
  }
}

?>