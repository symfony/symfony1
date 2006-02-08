<?php

require_once(sfConfig::get('sf_symfony_lib_dir').'/helper/ValidationHelper.php');

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004 David Heinemeier Hansson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * FormHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     David Heinemeier Hansson
 * @version    SVN: $Id$
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
function options_for_select($options = array(), $selected = '')
{
  if (is_array($selected))
  {
    $valid = array_values($selected);
    $valid = array_map('strval', $valid);
  }

  $html = '';
  foreach ($options as $key => $value)
  {
    $html_options = array('value' => $key);
    if (
        isset($selected)
        &&
        (is_array($selected) && in_array(strval($key), $valid, true))
        ||
        (strval($key) == strval($selected))
       )
    {
      $html_options['selected'] = 'selected';
    }

    $html .= content_tag('option', $value, $html_options)."\n";
  }

  return $html;
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
  $c = new sfCultureInfo(sfContext::getInstance()->getUser()->getCulture());
  $countries = $c->getCountries();
  asort($countries);

  $option_tags = options_for_select($countries, $value);

  return select_tag($name, $option_tags, $options);
}

function select_language_tag($name, $value, $options = array())
{
  $c = new sfCultureInfo(sfContext::getInstance()->getUser()->getCulture());
  $languages = $c->getLanguages();
  asort($languages);

  $option_tags = options_for_select($languages, $value);

  return select_tag($name, $option_tags, $options);
}

function input_tag($name, $value = null, $options = array())
{
  if ($value === null && isset($options['type']) && $options['type'] == 'password')
  {
    $value = null;
  }
  else if ($reqvalue = _get_request_value($name))
  {
    $value = $reqvalue;
  }

  return tag('input', array_merge(array('type' => 'text', 'name' => $name, 'id' => $name, 'value' => $value), _convert_options($options)));
}

function input_hidden_tag($name, $value = null, $options = array())
{
  if ($reqvalue = _get_request_value($name))
  {
    $value = $reqvalue;
  }

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

/**
 * example user css file
 / * user: foo * / => without spaces. 'foo' is the name in the select box
 .cool {
 color: #f00;
 }
 */
function textarea_tag($name, $content = null, $options = array())
{
  if ($reqvalue = _get_request_value($name))
  {
    $content = $reqvalue;
  }

  $options = _parse_attributes($options);

  if (array_key_exists('size', $options))
  {
    list($options['cols'], $options['rows']) = split('x', $options['size'], 2);
    unset($options['size']);
  }

  // rich control?
  $rich = false;
  if (isset($options['rich']))
  {
    $rich = $options['rich'];
    unset($options['rich']);
  }

  if ($rich)
  {

    // tinymce installed?
    $js_path = sfConfig::get('sf_rich_text_js_dir') ? '/'.sfConfig::get('sf_rich_text_js_dir').'/tiny_mce.js' : '/sf/js/tinymce/tiny_mce.js';
    if (!is_readable(sfConfig::get('sf_web_dir').$js_path))
    {
      throw new sfConfigurationException('You must install Tiny MCE to use this helper (see rich_text_js_dir settings).');
    }

    sfContext::getInstance()->getRequest()->setAttribute('tinymce', $js_path, 'helper/asset/auto/javascript');

    require_once(sfConfig::get('sf_symfony_lib_dir').'/helper/JavascriptHelper.php');

    $tinymce_options = '';
    $style_selector  = '';

    // custom CSS file?
    if (isset($options['css']))
    {
      $css_file = $options['css'];
      unset($options['css']);

      $css_path = stylesheet_path($css_file);

      sfContext::getInstance()->getRequest()->setAttribute('tinymce', $css_path, 'helper/asset/auto/stylesheet');

      $css    = file_get_contents(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$css_path);
      $styles = array();
      preg_match_all('#^/\*\s*user:\s*(.+?)\s*\*/\s*\015?\012\s*\.([^\s]+)#Smi', $css, $matches, PREG_SET_ORDER);
      foreach ($matches as $match)
      {
        $styles[] = $match[1].'='.$match[2];
      }

      $tinymce_options .= '  content_css: "'.$css_path.'",'."\n";
      $tinymce_options .= '  theme_advanced_styles: "'.implode(';', $styles).'"'."\n";
      $style_selector   = 'styleselect,separator,';
    }

    $tinymce_js = '
tinyMCE.init({
  mode: "exact",
  language: "en",
  elements: "'.$name.'",
  plugins: "table,advimage,advlink,flash",
  theme: "advanced",
  theme_advanced_toolbar_location: "top",
  theme_advanced_toolbar_align: "left",
  theme_advanced_path_location: "bottom",
  theme_advanced_buttons1: "'.$style_selector.'justifyleft,justifycenter,justifyright,justifyfull,separator,bold,italic,strikethrough,separator,sub,sup,separator,charmap",
  theme_advanced_buttons2: "bullist,numlist,separator,outdent,indent,separator,undo,redo,separator,link,unlink,image,flash,separator,cleanup,removeformat,separator,code",
  theme_advanced_buttons3: "tablecontrols",
  extended_valid_elements: "img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name]",
  relative_urls: false,
  debug: false
  '.($tinymce_options ? ','.$tinymce_options : '').'
});';

    return
      content_tag('script', javascript_cdata_section($tinymce_js), array('type' => 'text/javascript')).
      content_tag('textarea', $content, array_merge(array('name' => $name, 'id' => $name), _convert_options($options)));
  }
  else
  {
    return content_tag('textarea', htmlspecialchars((is_object($content)) ? $content->__toString() : $content), array_merge(array('name' => $name, 'id' => $name), _convert_options($options)));
  }
}

function checkbox_tag($name, $value = '1', $checked = false, $options = array())
{
  if ($reqvalue = _get_request_value($name))
  {
    $checked = $reqvalue;
  }

  $html_options = array_merge(array('type' => 'checkbox', 'name' => $name, 'id' => $name, 'value' => $value), _convert_options($options));
  if ($checked) $html_options['checked'] = 'checked';

  return tag('input', $html_options);
}

function radiobutton_tag($name, $value, $checked = false, $options = array())
{
  if ($reqvalue = _get_request_value($name))
  {
    $checked = $reqvalue;
  }

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
  if (($value !== null) && ($value != '') && (!is_int($value)))
  {
    $value = strtotime($value);
    if ($value === -1)
    {
      $value = 0;
//      throw new Exception("Unable to parse value of date as date/time value");
    }
    else
    {
      $dateFormat = new sfDateFormat($culture);
      $value = $dateFormat->format($value, 'd');
    }
  }

  // register our javascripts and stylesheets
  $js = array(
    '/sf/js/calendar/calendar',
//  '/sf/js/calendar/lang/calendar-'.substr($culture, 0, 2),
    '/sf/js/calendar/lang/calendar-en',
    '/sf/js/calendar/calendar-setup',
  );
  $context->getRequest()->setAttribute('date', $js, 'helper/asset/auto/javascript');
  $context->getRequest()->setAttribute('date', '/sf/js/calendar/skins/aqua/theme', 'helper/asset/auto/stylesheet');

  // date format
  $dateFormatInfo = sfDateTimeFormatInfo::getInstance($culture);
  $date_format = strtolower($dateFormatInfo->getShortDatePattern());

  // calendar date format
  $calendar_date_format = $date_format;
  $calendar_date_format = strtr($calendar_date_format, array('M' => 'm', 'y' => 'Y'));
  $calendar_date_format = preg_replace('/([mdy])+/i', '%\\1', $calendar_date_format);

  $js = '
    document.getElementById("trigger_'.$name.'").disabled = false;
    Calendar.setup({
      inputField : "'.$name.'",
      ifFormat : "'.$calendar_date_format.'",
      button : "trigger_'.$name.'"
    });
  ';

  // construct html
  $html = input_tag($name, $value);

  // calendar button
  $calendar_button = '...';
  $calendar_button_type = 'txt';
  if (isset($options['calendar_button_img']))
  {
    $calendar_button = $options['calendar_button_img'];
    $calendar_button_type = 'img';
    unset($options['calendar_button']);
  }
  else if (isset($options['calendar_button_txt']))
  {
    $calendar_button = $options['calendar_button_txt'];
    $calendar_button_type = 'txt';
    unset($options['calendar_button']);
  }

  if ($calendar_button_type == 'img')
  {
    $html .= image_tag($calendar_button, array('id' => 'trigger_'.$name, 'style' => 'cursor: pointer', 'align' => 'absmiddle'));
  }
  else
  {
    $html .= content_tag('button', $calendar_button, array('disabled' => 'disabled', 'onclick' => 'return false', 'id' => 'trigger_'.$name));
  }

  if (isset($options['with_format']))
  {
    $html .= '('.$date_format.')';
    unset($options['with_format']);
  }

  // add javascript
  $html .= content_tag('script', $js, array('type' => 'text/javascript'));

  return $html;
}

function submit_tag($value = 'Save changes', $options = array())
{
  return tag('input', array_merge(array('type' => 'submit', 'name' => 'commit', 'value' => $value), _convert_options($options)));
}

function reset_tag($value = 'Reset', $options = array())
{
  return tag('input', array_merge(array('type' => 'reset', 'name' => 'reset', 'value' => $value), _convert_options($options)));
}

function submit_image_tag($source, $options = array())
{
  return tag('input', array_merge(array('type' => 'image', 'name' => 'commit', 'src' => image_path($source)), _convert_options($options)));
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

function _get_request_value($name)
{
  return sfContext::getInstance()->getRequest()->getParameter($name);
}

?>
