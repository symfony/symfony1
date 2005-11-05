<?php

require_once('symfony/helper/ValidationHelper.php');

// +---------------------------------------------------------------------------+
// | This file is part of the SymFony Framework project.                        |
// | Copyright (c) 2004, 2005 Fabien POTENCIER.                                          |
// +---------------------------------------------------------------------------+

/**
 *
 * @package   sf_runtime
 * @subpackage helper
 *
 * @author    Fabien POTENCIER (fabien.potencier@symfony-project.com)
 *  (c) Fabien POTENCIER
 * @since     1.0.0
 * @version   $Id: FormHelper.php 535 2005-10-18 13:01:23Z root $
 */

/*
      # Accepts a container (hash, array, enumerable, your type) and returns a string of option tags. Given a container
      # where the elements respond to first and last (such as a two-element array), the "lasts" serve as option values and
      # the "firsts" as option text. Hashes are turned into this form automatically, so the keys become "firsts" and values
      # become lasts. If +selected+ is specified, the matching "last" or element will get the selected option-tag.  +Selected+
      # may also be an array of values to be selected when using a multiple select.
      #
      # Examples (call, result):
      #   options_for_select([["Dollar", "$"], ["Kroner", "DKK"]])
      #     <option value="$">Dollar</option>\n<option value="DKK">Kroner</option>
      #
      #   options_for_select([ "VISA", "MasterCard" ], "MasterCard")
      #     <option>VISA</option>\n<option selected="selected">MasterCard</option>
      #
      #   options_for_select({ "Basic" => "$20", "Plus" => "$40" }, "$40")
      #     <option value="$20">Basic</option>\n<option value="$40" selected="selected">Plus</option>
      #
      #   options_for_select([ "VISA", "MasterCard", "Discover" ], ["VISA", "Discover"])
      #     <option selected="selected">VISA</option>\n<option>MasterCard</option>\n<option selected="selected">Discover</option>
      #
      # NOTE: Only the option tags are returned, you have to wrap this call in a regular HTML select tag.
*/
function options_for_select($options = array(), $selected = null)
{
  $html_options = '';
  foreach($options as $key => $value)
  {
    $html_options .= '<option value="'.$value.'"';
    if (
      (is_array($selected) && in_array($key, $selected))
      ||
      ($value == $selected)
    )
    {
      $html_options .= ' selected="selected"';
    }
    $html_options .= '>'.$key.'</option>';
  }

  return $html_options;
}

/*
      # Accepts a container of objects, the method name to use for the value, and the method name to use for the display. It returns 
      # a string of option tags. 
      # NOTE: Only the option tags are returned, you have to wrap this call in a regular HTML select tag.
*/
function objects_for_select($options = array(), $value_method, $text_method = null, $selected = null)
{
  $html_options = '';
  foreach($options as $option)
  {
    // text method exists?
    if ($text_method && !method_exists($option, $text_method))
    {
      $error = 'Method "%s" does\'t exists for object of class "%s"';
      $error = sprintf($error, $text_method, get_class($option));

      throw new sfViewException($error);
    }

    // value method exists?
    if (!method_exists($option, $value_method))
    {
      $error = 'Method "%s" does\'t exists for object of class "%s"';
      $error = sprintf($error, $value_method, get_class($option));

      throw new sfViewException($error);
    }

    $value = $option->$value_method();
    $key = ($text_method != null) ? $option->$text_method() : $value;

    $html_options .= '<option value="'.$value.'"';
    if (
      (is_array($selected) && in_array($key, $selected))
      ||
      ($value == $selected)
    )
    {
      $html_options .= ' selected="selected"';
    }
    $html_options .= '>'.$key.'</option>';
  }

  return $html_options;
}

/*
    # Starts a form tag that points the action to an url configured with <tt>url_for_options</tt> just like
    # ActionController::Base#url_for. The method for the form defaults to POST.
    #
    # Options:
    # * <tt>:multipart</tt> - If set to true, the enctype is set to "multipart/form-data".
*/
function form_tag($url_for_options = '', $options = array())
{
  $options = _parse_attributes($options);

  $html_options = $options;
  if (!array_key_exists('method', $html_options))
  {
    $html_options['method'] = 'post';
  }

  if (array_key_exists('multipart', $html_options))
  {
    $html_options['enctype'] = 'multipart/form-data';
    unset($html_options['multipart']);
  }

  $html_options['action'] = url_for($url_for_options);

  return tag('form', $html_options, true);
}

function select_tag($name, $option_tags = null, $options = array())
{
  return content_tag('select', $option_tags, array_merge(array('name' => $name, 'id' => $name), _convert_options($options)));
}

function select_country_tag($name, $value, $options = array())
{
  $c = new CultureInfo(sfContext::getInstance()->getUser()->getCulture());
  $countries = $c->getCountries();
  asort($countries);

  $option_tags = options_for_select(array_flip($countries), $value);

  return select_tag($name, $option_tags, $options);
}

function input_tag($name, $value = null, $options = array())
{
  return tag('input', array_merge(array('type' => 'text', 'name' => $name, 'id' => $name, 'value' => $value), _convert_options($options)));
}

function input_hidden_tag($name, $value = null, $options = array())
{
  $options = _parse_attributes($options);

  $options['type'] = 'hidden';
  return input_tag($name, $value, $options);
}

function input_file_tag($name, $options = array())
{
  $options = _parse_attributes($options);

  $options['type'] = 'file';
  return input_tag($name, null, $options);
}

function input_password_tag($name = 'password', $value = null, $options = array())
{
  $options = _parse_attributes($options);

  $options['type'] = 'password';
  return input_tag($name, $value, $options);
}

function textarea_tag($name, $content = null, $options = array())
{
  $options = _parse_attributes($options);

  // rich control?
  $rich = false;
  if (isset($options['rich']))
  {
    $rich = $options['rich'];
    unset($options['rich']);
  }

  if ($rich)
  {
    throw new sfException('input_date_tag (rich=on) is not yet implemented');
  }

  if (array_key_exists('size', $options))
  {
    list($options['cols'], $options['rows']) = split('x', $options['size'], 2);
    unset($options['size']);
  }

  return content_tag('textarea', htmlspecialchars($content), array_merge(array('name' => $name, 'id' => $name), _convert_options($options)));
}

function checkbox_tag($name, $value = '1', $checked = false, $options = array())
{
  $html_options = array_merge(array('type' => 'checkbox', 'name' => $name, 'id' => $name, 'value' => $value), _convert_options($options));
  if ($checked) $html_options['checked'] = 'checked';

  return tag('input', $html_options);
}

function radiobutton_tag($name, $value, $checked = false, $options = array())
{
  $html_options = array_merge(array('type' => 'radio', 'name' => $name, 'value' => $value), _convert_options($options));
  if ($checked) $html_options['checked'] = 'checked';

  return tag('input', $html_options);
}

function input_upload_tag($name, $options = array())
{
  $options = _parse_attributes($options);

  $options['type'] = 'file';

  return input_tag($name, '', $options);
}

function input_date_tag($name, $value, $options = array())
{
  require_once('i18n/DateFormat.php');

  $options = _parse_attributes($options);

  $context = sfContext::getInstance();
  if (isset($options['culture']))
  {
    $culture = $options['culture'];
    unset($options['culture']);
  }
  else
  {
    $culture = $context->getUser()->getCulture();
  }

  // rich control?
  $rich = false;
  if (isset($options['rich']))
  {
    $rich = $options['rich'];
    unset($options['rich']);
  }

  if (!$rich)
  {
    throw new sfException('input_date_tag (rich=off) is not yet implemented');
  }

  // parse date
  $date = $value;
  if (($date !== null) && ($date != '') && (!is_int($date)))
  {
    $date = strtotime($date);
    if ($date === -1)
    {
      $date = 0;
//      throw new Exception("Unable to parse value of date as date/time value");
    }
  }
  $dateFormat = new DateFormat($culture);
  $value = $dateFormat->format($date, 'd');

  // register our javascripts and stylesheets
  $js = array(
    '/sf/js/calendar/calendar_stripped',
//  '/sf/js/calendar/lang/calendar-'.substr($culture, 0, 2),
    '/sf/js/calendar/lang/calendar-en',
    '/sf/js/calendar/calendar-setup_stripped',
  );
  $context->getRequest()->setAttribute('date', $js, 'helper/asset/auto/javascript');
  $context->getRequest()->setAttribute('date', '/sf/js/calendar/skins/aqua/theme', 'helper/asset/auto/stylesheet');

  // date format
  $dateFormatInfo = DateTimeFormatInfo::getInstance($culture);
  $date_format = strtolower($dateFormatInfo->getShortDatePattern());

  // calendar date format
  $calendar_date_format = $date_format;
  $calendar_date_format = preg_replace('~M~', 'm', $calendar_date_format);
  $calendar_date_format = preg_replace('~y~', 'Y', $calendar_date_format);
  $calendar_date_format = preg_replace('~([mdy])+~i', '%$1', $calendar_date_format);

  $js = '
    document.getElementById("trigger_'.$name.'").disabled = false;
    Calendar.setup({
      inputField : "'.$name.'",
      ifFormat : "'.$calendar_date_format.'",
      button : "trigger_'.$name.'"
    });
  ';

  return
    input_tag($name, $value).
    content_tag('button', '...', array('disabled' => 'disabled', 'onclick' => 'return false', 'id' => 'trigger_'.$name)).
    '('.$date_format.')'.
    content_tag('script', $js, array('type' => 'text/javascript'));
}

function submit_tag($value = 'Save changes', $options = array())
{
  return tag('input', array_merge(array('type' => 'submit', 'name' => 'submit', 'value' => $value), _convert_options($options)));
}

function submit_image_tag($source, $options = array())
{
  return tag('input', array_merge(array('type' => 'image', 'name' => 'submit', 'src' => image_path($source)), _convert_options($options)));
}

function _convert_options($options)
{
  $options = _parse_attributes($options);

  foreach (array('disabled', 'readonly', 'multiple') as $attribute)
  {
    $options = _boolean_attribute($options, $attribute);
  }

  return $options;
}

function _boolean_attribute($options, $attribute)
{
  if (array_key_exists($attribute, $options))
  {
    if ($options[$attribute])
    {
      $options[$attribute] = $attribute;
    }
    else
    {
      unset($options[$attribute]);
    }
  }

  return $options;
}

?>
