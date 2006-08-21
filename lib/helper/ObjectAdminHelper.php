<?php

require_once(sfConfig::get('sf_symfony_lib_dir').'/helper/FormHelper.php');
require_once(sfConfig::get('sf_symfony_lib_dir').'/helper/JavascriptHelper.php');

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
  if (sfConfig::get('sf_logging_active'))
  {
    sfContext::getInstance()->getLogger()->err('This function is deprecated. Please use object_admin_input_file_tag.');
  }

  return object_admin_input_file_tag($object, $method, $options);
}

function object_admin_input_file_tag($object, $method, $options = array())
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

  return input_file_tag($name, $options)."\n<br />".$html;
}

function object_admin_double_list($object, $method, $options = array())
{
  $options = _parse_attributes($options);

  $options['multiple'] = true;
  if (!isset($options['class']))
  {
    $options['class'] = 'multiple';
  }
  if (!isset($options['size']))
  {
    $options['size'] = 10;
  }
  $label_all   = isset($options['unassociated_label']) ? $options['unassociated_label'] : 'Unassociated';
  $label_assoc = isset($options['associated_label'])   ? $options['associated_label']   : 'Associated';

  // get the lists of objects
  $through_class = _get_option($options, 'through_class');
  $sort         = _get_option($options, 'sort');
  unset($options['through_class']);
  unset($options['sort']);
  $objects_associated = sfPropelManyToMany::getRelatedObjects($object, $through_class);
  $all_objects = sfPropelManyToMany::getAllObjects($object, $through_class);
  $objects_unassociated = array();
  $associated_ids = array_map(create_function('$o', 'return $o->getPrimaryKey();'), $objects_associated);
  foreach ($all_objects as $object)
  {
    if (!in_array($object->getPrimaryKey(), $associated_ids))
    {
      $objects_unassociated[] = $object;
    }
  }

  // override field name
  unset($options['control_name']);
  $name  = _convert_method_to_name($method, $options);
  $name1 = 'unassociated_'.$name;
  $name2 = 'associated_'.$name;
  $select1 = select_tag($name1, options_for_select(_get_options_from_objects($objects_unassociated), '', $options), $options);
  $options['class'] = 'multiple-selected';
  $select2 = select_tag($name2, options_for_select(_get_options_from_objects($objects_associated), '', $options), $options);

  $html =
'<div>
  <div style="float: left">
    <div style="font-weight: bold; padding-bottom: 0.5em">%s</div>
    %s
  </div>
  <div style="float: left">
    %s<br />
    %s
  </div>
  <div style="float: left">
    <div style="font-weight: bold; padding-bottom: 0.5em">%s</div>
    %s
  </div>
  <br style="clear: both" />
</div>
';

  return sprintf($html,
    $label_all,
    $select1,
    submit_image_tag('/sf/images/sf_admin/next.png', "style=\"border: 0\" onclick=\"double_list_move(\$('{$name1}'), \$('{$name2}')); return false;\""),
    submit_image_tag('/sf/images/sf_admin/previous.png', "style=\"border: 0\" onclick=\"double_list_move(\$('{$name2}'), \$('{$name1}')); return false;\""),
    $label_assoc,
    $select2
  );
}

function object_admin_select_list($object, $method, $options = array())
{
  $options = _parse_attributes($options);

  $options['multiple'] = true;
  if (!isset($options['class'])) $options['class'] = 'multiple';
  if (!isset($options['size']))  $options['size'] = 10;

  // get the lists of objects
  $through_class = _get_option($options, 'through_class');
  $sort          = _get_option($options, 'sort');
  $objects = sfPropelManyToMany::getAllObjects($object, $through_class);
  $objects_associated = sfPropelManyToMany::getRelatedObjects($object, $through_class);
  $ids = array_map(create_function('$o', 'return $o->getPrimaryKey();'), $objects_associated);
  unset($options['through_class']);
  unset($options['sort']);

  // override field name
  unset($options['control_name']);
  $name = 'associated_'._convert_method_to_name($method, $options);

  return select_tag($name, options_for_select(_get_options_from_objects($objects), $ids, $options), $options);
}

function object_admin_check_list($object, $method, $options = array())
{
  $options = _parse_attributes($options);

  // get the lists of objects
  $through_class = _get_option($options, 'through_class');
  $sort          = _get_option($options, 'sort');
  unset($options['through_class']);
  unset($options['sort']);
  $objects = sfPropelManyToMany::getAllObjects($object, $through_class);
  $objects_associated = sfPropelManyToMany::getRelatedObjects($object, $through_class);
  $assoc_ids = array_map(create_function('$o', 'return $o->getPrimaryKey();'), $objects_associated);

  // override field name
  unset($options['control_name']);
  $name = 'associated_'._convert_method_to_name($method, $options).'[]';
  $html = '';

  if (!empty($objects))
  {
    // which method to call?
    $methodToCall = '';
    foreach (array('toString', '__toString', 'getPrimaryKey') as $method)
    {
      if (is_callable(array($objects[0], $method)))
      {
        $methodToCall = $method;
        break;
      }
    }

    $html .= "<ul class=\"checklist\">\n";
    foreach ($objects as $related_object)
    {
      $html .= '<li>'.checkbox_tag($name, $related_object->getPrimaryKey(), in_array($related_object->getPrimaryKey(), $assoc_ids)).' '.$related_object->$methodToCall()."</li>\n";
    }
    $html .= "</ul>\n";
  }

  return $html;
}
