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
 * ObjectHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

/**
 * Returns a html date control.
 *
 * @param object An object.
 * @param string An object column.
 * @param array Date options.
 * @param bool Date default value.
 *
 * @return string An html string which represents a date control.
 *
 */
function object_input_date_tag($object, $method, $options = array(), $default_value = null)
{
  $options = _parse_attributes($options);

  $value = _get_object_value($object, $method, $default_value);

  return input_date_tag(_convert_method_to_name($method, $options), $value, $options);
}

/**
 * Returns a textarea html tag.
 *
 * @param object An object.
 * @param string An object column.
 * @param array Textarea options.
 * @param bool Textarea default value.
 *
 * @return string An html string which represents a textarea tag.
 *
 */
function object_textarea_tag($object, $method, $options = array(), $default_value = null)
{
  $options = _parse_attributes($options);

  $value = _get_object_value($object, $method, $default_value);

  return textarea_tag(_convert_method_to_name($method, $options), $value, $options);
}

/**
 * Accepts a container of objects, the method name to use for the value,
 * and the method name to use for the display.
 * It returns a string of option tags.
 *
 * NOTE: Only the option tags are returned, you have to wrap this call in a regular HTML select tag.
 */
function objects_for_select($options = array(), $value_method, $text_method = null, $selected = null, $html_options = array())
{
  $select_options = array();
  foreach($options as $option)
  {
    // text method exists?
    if ($text_method && !method_exists($option, $text_method))
    {
      $error = sprintf('Method "%s" doesn\'t exist for object of class "%s"', $text_method, get_class($option));
      throw new sfViewException($error);
    }

    // value method exists?
    if (!method_exists($option, $value_method))
    {
      $error = sprintf('Method "%s" doesn\'t exist for object of class "%s"', $value_method, get_class($option));
      throw new sfViewException($error);
    }

    $value = $option->$value_method();
    $key = ($text_method != null) ? $option->$text_method() : $value;

    $select_options[$value] = $key;
  }

  return options_for_select($select_options, $selected, $html_options);
}

/**
 * Returns a list html tag.
 *
 * @param object An object.
 * @param string An object column.
 * @param array Input options (related_class option is mandatory).
 * @param bool Input default value.
 *
 * @return string A list string which represents an input tag.
 *
 */
function object_select_tag($object, $method, $options = array(), $default_value = null)
{
  $options = _parse_attributes($options);
  $related_class = isset($options['related_class']) ? $options['related_class'] : '';
  if (!isset($options['related_class']) && preg_match('/^get(.+?)Id$/', $method, $match))
  {
    $related_class = $match[1];
  }
  unset($options['related_class']);

  if (isset($options['include_title']))
  {
    $select_options[''] = '-- '._convert_method_to_name($method, $options).' --';
    unset($options['include_title']);
  }

  $select_options = _get_values_for_object_select_tag($object, $related_class);

  $value = _get_object_value($object, $method, $default_value);
  $option_tags = options_for_select($select_options, $value, $options);

  return select_tag(_convert_method_to_name($method, $options), $option_tags, $options);
}

function _get_values_for_object_select_tag($object, $class)
{
  // FIXME: drop Propel dependency

  $select_options = array();

  require_once(sfConfig::get('sf_model_lib_dir').'/'.$class.'Peer.php');
  $objects = call_user_func(array($class.'Peer', 'doSelect'), new Criteria());
  if ($objects)
  {
    // multi primary keys handling
    $multi_primary_keys = is_array($objects[0]->getPrimaryKey()) ? true : false;

    // which method to call?
    $methodToCall = '';
    foreach (array('toString', '__toString', 'getPrimaryKey') as $method)
    {
      if (method_exists($objects[0], $method))
      {
        $methodToCall = $method;
        break;
      }
    }

    // construct select option list
    foreach ($objects as $tmp_object)
    {
      $key   = $multi_primary_keys ? implode('/', $tmp_object->getPrimaryKey()) : $tmp_object->getPrimaryKey();
      $value = $tmp_object->$methodToCall();

      $select_options[$key] = $value;
    }
  }

  return $select_options;
}

function object_select_country_tag($object, $method, $options = array(), $default_value = null)
{
  $options = _parse_attributes($options);

  $value = _get_object_value($object, $method, $default_value);

  return select_country_tag(_convert_method_to_name($method, $options), $value, $options);
}

function object_select_language_tag($object, $method, $options = array(), $default_value = null)
{
  $options = _parse_attributes($options);

  $value = _get_object_value($object, $method, $default_value);

  return select_language_tag(_convert_method_to_name($method, $options), $value, $options);
}

/**
 * Returns a hidden input html tag.
 *
 * @param object An object.
 * @param string An object column.
 * @param array Input options.
 * @param bool Input default value.
 *
 * @return string An html string which represents a hidden input tag.
 *
 */
function object_input_hidden_tag($object, $method, $options = array(), $default_value = null)
{
  $options = _parse_attributes($options);

  $value = _get_object_value($object, $method, $default_value);

  return input_hidden_tag(_convert_method_to_name($method, $options), $value, $options);
}

/**
 * Returns a input html tag.
 *
 * @param object An object.
 * @param string An object column.
 * @param array Input options.
 * @param bool Input default value.
 *
 * @return string An html string which represents an input tag.
 *
 */
function object_input_tag($object, $method, $options = array(), $default_value = null)
{
  $options = _parse_attributes($options);

  $value = _get_object_value($object, $method, $default_value);

  return input_tag(_convert_method_to_name($method, $options), $value, $options);
}

/**
 * Returns a checkbox html tag.
 *
 * @param object An object.
 * @param string An object column.
 * @param array Checkbox options.
 * @param bool Checkbox value.
 *
 * @return string An html string which represents a checkbox tag.
 *
 */
function object_checkbox_tag($object, $method, $options = array(), $default_value = null)
{
  $options = _parse_attributes($options);

  $value = _get_object_value($object, $method, $default_value);
  $value = in_array($value, array(true, 1, 'on', 'true', 't', 'yes', 'y'), true);

  return checkbox_tag(_convert_method_to_name($method, $options), 1, $value, $options);
}

function _convert_method_to_name ($method, &$options)
{
  $name = _get_option($options, 'control_name');

  if (!$name)
  {
    $name = sfInflector::underscore($method);
    $name = preg_replace('/^get_?/', '', $name);
  }

  return $name;
}

// returns default_value if object value is null
function _get_object_value ($object, $method, $default_value = null)
{
  // method exists?
  if (!method_exists($object, $method))
  {
    $error = 'Method "%s" doesn\'t exist for object of class "%s"';
    $error = sprintf($error, $method, get_class($object));

    throw new sfViewException($error);
  }

  $object_value = $object->$method();

  return ($default_value !== null && $object_value === null) ? $default_value : $object_value;
}

?>