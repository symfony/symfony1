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

function object_admin_input_upload_tag($object, $method, $options = array())
{
  $options = _parse_attributes($options);
  $name    = _convert_method_to_name($method, $options);

  $html = '';

  if ($object->$method())
  {
    if (isset($options['include_link']) && $options['include_link'])
    {
      $image_path = image_path('/'.sfConfig::get('sf_upload_dir_name').'/'.$options['include_link'].'/'.$object->$method());
      $image_text = isset($options['include_text']) ? __($options['include_text']) : __('[show file]');

      $html .= sprintf('<a onclick="window.open(this.href);return false;" href="%s">%s</a>', $image_path, $image_text)."\n";
    }

    if (isset($options['include_remove']) && $options['include_remove'])
    {
      $html .= checkbox_tag(strpos($name, ']') !== false ? substr($name, 0, -1).'_remove]' : $name).' '.($options['include_remove'] != true ? __($options['include_remove']) : __('remove file'))."\n";
    }
  }

  unset($options['include_link']);
  unset($options['include_text']);
  unset($options['include_remove']);

  return input_upload_tag($name, $options)."\n<br />".$html;
}

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
