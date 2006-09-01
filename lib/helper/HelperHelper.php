<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
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

/**
 * <b>DEPRECATED:</b> use use_helper() instead with the same syntax. 
 */ 
function use_helpers()
{
  if (sfConfig::get('sf_logging_active')) sfContext::getInstance()->getLogger()->err('The function "use_helpers()" is deprecated. Please use "use_helper()"'); 

  foreach (func_get_args() as $helperName)
  {
    use_helper($helperName);
  }
}

function use_helper()
{
  sfLoader::loadHelpers(func_get_args(), sfContext::getInstance()->getModuleName());
}
