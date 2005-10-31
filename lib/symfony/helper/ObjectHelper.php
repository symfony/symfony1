<?php

require_once('symfony/helper/FormHelper.php');

// +---------------------------------------------------------------------------+
// | This file is part of the SymFony Framework project.                        |
// | Copyright (c) 2004, 2005 Fabien POTENCIER.                                          |
// +---------------------------------------------------------------------------+

/**
 *
 * @package   sf_runtime
 * @subpackage helper
 *
 * @author    Fabien POTENCIER (fabien.potencier@symfony-project)
 *  (c) Fabien POTENCIER
 * @since     1.0.0
 * @version   $Id: ObjectHelper.php 493 2005-10-05 07:32:12Z fabien $
 */

/**
 * Returns an html textarea html tag.
 *
 * @param object An object.
 * @param string An object column.
 * @param array Html textarea options.
 * @param bool Html textarea default value.
 *
 * @return string An html string which represents an html textarea tag.
 *
 */
/* FIXME
function object_htmltext_tag($object, $method, $options = array(), $default_value = null)
{
  $ctl = new sfHtmlTextControl();
  $ctl->init();
  $ctl->setName(strtolower($method));
  $ctl->setAttribute('id', strtolower($method));
  if ($value !== null)
  {
    $ctl->setValue($value);
  }
  else
  {
    $ctl->setValue($object->getByName(constant(get_class($object).'Peer::'.strtoupper($method))));
  }

  sfContext::getInstance()->getRequest()->setAttribute('htmltext', $ctl->getJs(), 'helper/asset/auto/javascript');

  $ctl = _setControlCommonAttributes($ctl, $options);

  return $ctl->render();
}
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
  $value = _get_object_value($object, $method, $default_value);

  return input_date_tag(_convert_method_to_name($method), $value, $options);
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
  $value = _get_object_value($object, $method, $default_value);

  return textarea_tag(_convert_method_to_name($method), $value, $options);
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
  if (!isset($options['related_class']) && preg_match('/^get(.+?)Id$/', $method, $match))
  {
    $options['related_class'] = $match[1];
  }

  $value = _get_object_value($object, $method, $default_value);

  $select_options = array();
  $options = _parse_attributes($options);
  if (isset($options['include_blank']))
  {
    $select_options[0] = '';
  }
  else if (isset($options['include_title']))
  {
    $select_options[0] = '-- '._convert_method_to_name($method).' --';
  }

  // FIXME: drop Propel dependency
  $rs = call_user_func_array(array($options['related_class'].'Peer', 'doSelectRS'), array(new Criteria()));
  while ($rs->next())
  {
    $tmp_object = new $options['related_class']();
    $tmp_object->hydrate($rs);
    $select_options[$tmp_object->getPrimaryKey()] = method_exists($tmp_object, 'toString') ? $tmp_object->toString() : $tmp_object->getPrimaryKey();
  }

  $option_tags = options_for_select(array_flip($select_options), $value);

  return select_tag(_convert_method_to_name($method), $option_tags, $options);
}

function object_select_country_tag($object, $method, $options = array(), $default_value = null)
{
  $value = _get_object_value($object, $method, $default_value);

  return select_country_tag(_convert_method_to_name($method), $value, $options);
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
  $value = _get_object_value($object, $method, $default_value);

  return input_hidden_tag(_convert_method_to_name($method), $value, $options);
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
  $value = _get_object_value($object, $method, $default_value);

  return input_tag(_convert_method_to_name($method), $value, $options);
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
  $value = _get_object_value($object, $method, $default_value);
  $value = ($value === true || $value == 'on' || $value == 1) ? 1 : 0;

  return checkbox_tag(_convert_method_to_name($method), 1, $value, $options);
}

function _convert_method_to_name ($method)
{
  $name = sfInflector::underscore($method);
  $name = preg_replace('/^get_?/', '', $name);

  return $name;
}

// returns default_value if object value is null
function _get_object_value ($object, $method, $default_value)
{
  $object_value = call_user_func(array($object, $method));

  return ($default_value !== null && $object_value === null) ? $default_value : $object_value;
}

?>