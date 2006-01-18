<?php

require_once(sfConfig::get('sf_symfony_lib_dir').'/helper/FormHelper.php');

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ObjectHelper for admin generator.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

function object_edit_collection($object, $method, $options = array())
{
  $objects = $object->$method();

  $layout = 'stacked';
  if (isset($options['layout']))
  {
    $layout = $options['layout'];
    unset($options['layout']);
  }

  return var_export($objects);
}

?>