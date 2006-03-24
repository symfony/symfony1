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
function options_for_select($options = array(), $selected = '', $html_options = array())
{
  $html_options = _parse_attributes($html_options);

  if (is_array($selected))
  {
    $valid = array_values($selected);
    $valid = array_map('strval', $valid);
  }

  $html = '';

  if (isset($html_options['include_custom']))
  {
    $html .= content_tag('option', $html_options['include_custom'], array('value' => ''))."\n";
  }
  else if (isset($html_options['include_blank']))
  {
    $html .= content_tag('option', '', array('value' => ''))."\n";
  }

  foreach ($options as $key => $value)
  {
    $option_options = array('value' => $key);
    if (
        isset($selected)
        &&
        (is_array($selected) && in_array(strval($key), $valid, true))
        ||
        (strval($key) == strval($selected))
       )
    {
      $option_options['selected'] = 'selected';
    }

    $html .= content_tag('option', $value, $option_options)."\n";
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

  if (isset($options['countries']) && is_array($options['countries']))
  {
    $diff = array_diff_key($countries, array_flip($options['countries']));
    foreach ($diff as $key => $v)
    {
      unset($countries[$key]);
    }

    unset($options['countries']);
  }

  asort($countries);

  $option_tags = options_for_select($countries, $value);

  return select_tag($name, $option_tags, $options);
}

function select_language_tag($name, $value, $options = array())
{
  $c = new sfCultureInfo(sfContext::getInstance()->getUser()->getCulture());
  $languages = $c->getLanguages();

  if (isset($options['languages']) && is_array($options['languages']))
  {
    $diff = array_diff_key($languages, array_flip($options['languages']));
    foreach ($diff as $key => $v)
    {
      unset($languages[$key]);
    }

    unset($options['languages']);
  }

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
  else if (($reqvalue = _get_request_value($name)) !== null)
  {
    $value = $reqvalue;
  }

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

/**
 * example user css file
 / * user: foo * / => without spaces. 'foo' is the name in the select box
 .cool {
 color: #f00;
 }
 */
function textarea_tag($name, $content = null, $options = array())
{
  if (($reqvalue = _get_request_value($name)) !== null)
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
    if ($rich === true)
    {
      $rich = 'tinymce';
    }
    unset($options['rich']);
  }

  // we need to know the id for things the rich text editor
  // in advance of building the tag
  if (isset($options['id']))
  {
    $id = $options['id'];
    unset($options['id']);
  }
  else
  {
    $id = $name;
  }

  if ($rich == 'tinymce')
  {
    // tinymce installed?
    $js_path = sfConfig::get('sf_rich_text_js_dir') ? '/'.sfConfig::get('sf_rich_text_js_dir').'/tiny_mce.js' : '/sf/js/tinymce/tiny_mce.js';
    if (!is_readable(sfConfig::get('sf_web_dir').$js_path))
    {
      throw new sfConfigurationException('You must install TinyMCE to use this helper (see rich_text_js_dir settings).');
    }

    sfContext::getInstance()->getResponse()->addJavascript($js_path);

    require_once(sfConfig::get('sf_symfony_lib_dir').'/helper/JavascriptHelper.php');

    $tinymce_options = '';
    $style_selector  = '';

    // custom CSS file?
    if (isset($options['css']))
    {
      $css_file = $options['css'];
      unset($options['css']);

      $css_path = stylesheet_path($css_file);

      sfContext::getInstance()->getResponse()->addStylesheet($css_path);

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
  elements: "'.$id.'",
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
  '.(isset($options['tinymce_options']) ? ','.$options['tinymce_options'] : '').'
});';

    return
      content_tag('script', javascript_cdata_section($tinymce_js), array('type' => 'text/javascript')).
      content_tag('textarea', $content, array_merge(array('name' => $name, 'id' => $id), _convert_options($options)));
  }
  elseif ($rich === 'fck')
  {
    $php_file = sfConfig::get('sf_rich_text_fck_js_dir').DIRECTORY_SEPARATOR.'fckeditor.php';

    if (!is_readable(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$php_file))
    {
      throw new sfConfigurationException('You must install FCKEditor to use this helper (see rich_text_fck_js_dir settings).');
    }

    // FCKEditor.php class is written with backward compatibility of PHP4.
    // This reportings are to turn off errors with public properties and already declared constructor
    $error_reporting = ini_get('error_reporting');
    error_reporting(E_ALL);

    require_once(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.$php_file);

    // turn error reporting back to your settings
    error_reporting($error_reporting);

    $fckeditor           = new FCKeditor($name);
    $fckeditor->BasePath = DIRECTORY_SEPARATOR.sfConfig::get('sf_rich_text_fck_js_dir').DIRECTORY_SEPARATOR;
    $fckeditor->Value    = $content;

    if (isset($options['width']))
    {
      $fckeditor->Width = $options['width'];
    }   
    elseif (isset($options['cols']))
    {
      $fckeditor->Width = (string)((int) $options['cols'] * 10).'px';
    }

    if (isset($options['height']))
    {
      $fckeditor->Height = $options['height'];
    }
    elseif (isset($options['rows']))
    {
      $fckeditor->Height = (string)((int) $options['rows'] * 10).'px';
    }

    if (isset($options['tool']))
    {
      $fckeditor->ToolbarSet = $options['tool'];
    }

    $content = $fckeditor->CreateHtml();

    return $content;
  }
  else
  {
    return content_tag('textarea', htmlspecialchars((is_object($content)) ? $content->__toString() : $content), array_merge(array('name' => $name, 'id' => $id), _convert_options($options)));
  }
}

function checkbox_tag($name, $value = '1', $checked = false, $options = array())
{
  $request = sfContext::getInstance()->getRequest();
  if ($request->hasErrors())
  {
    $checked = $request->getParameter($name, null);
  }
  elseif (($reqvalue = _get_request_value($name)) !== null)
  {
    $checked = $reqvalue;
  }

  $html_options = array_merge(array('type' => 'checkbox', 'name' => $name, 'id' => $name, 'value' => $value), _convert_options($options));
  if ($checked) $html_options['checked'] = 'checked';

  return tag('input', $html_options);
}

function radiobutton_tag($name, $value, $checked = false, $options = array())
{
  if (($reqvalue = _get_request_value($name)) !== null)
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
  $jss = array(
    '/sf/js/calendar/calendar',
//  '/sf/js/calendar/lang/calendar-'.substr($culture, 0, 2),
    '/sf/js/calendar/lang/calendar-en',
    '/sf/js/calendar/calendar-setup',
  );
  foreach ($jss as $js)
  {
    $context->getResponse()->addJavascript($js);
  }
  $context->getResponse()->addStylesheet('/sf/js/calendar/skins/aqua/theme');

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

function select_day_tag($name, $value, $options = array(), $html_options = array())
{
  $select_options = array();

  if (_get_option($options, 'include_blank'))
  {
    $select_options[''] = '';
  }
  else if ($include_custom = _get_option($options, 'include_custom'))
  {
    $select_options[''] = $include_custom;
  }

  for ($x = 1; $x < 32; $x++)
  {
    $select_options[$x] = _add_zeros($x, 2);
  }

  $option_tags = options_for_select($select_options, $value);

  return select_tag($name, $option_tags, $html_options);
}

function select_month_tag($name, $value, $options = array(), $html_options = array())
{

  $culture = _get_option($options, 'culture', sfContext::getInstance()->getUser()->getCulture());

  $I18n_arr = _get_I18n_date_locales($culture);

  $select_options = array();

  if (_get_option($options, 'include_blank'))
  {
    $select_options[''] = '';
  }
  else if ($include_custom = _get_option($options, 'include_custom'))
  {
    $select_options[''] = $include_custom;
  }

  if (_get_option($options, 'use_month_numbers')) 
  {
    for ($k = 1; $k < 13; $k++) 
    {
      $select_options[$k] = _add_zeros($k, 2);
    }
  }
  else
  {  
    if (_get_option($options, 'use_short_month')) 
    {
      $month_names = $I18n_arr['dateFormatInfo']->getAbbreviatedMonthNames();
    }
    else
    {
      $month_names = $I18n_arr['dateFormatInfo']->getMonthNames();
    }

    $add_month_numbers = _get_option($options, 'add_month_numbers');
    foreach ($month_names as $k => $v) 
    {
      $select_options[$k + 1] = ($add_month_numbers) ? ($k + 1 . ' - ' . $v) : $v;
    }
  }

  $option_tags = options_for_select($select_options, $value);

  return select_tag($name, $option_tags, $html_options);
}

function select_year_tag($name, $value, $options = array(), $html_options = array())
{
  $select_options = array();

  if (_get_option($options, 'include_blank'))
  {
    $select_options[''] = '';
  }
  else if ($include_custom = _get_option($options, 'include_custom'))
  {
    $select_options[''] = $include_custom;
  }

  if (strlen($value) > 0 && is_numeric($value))
  {
    $year_origin = $value;
  }
  else
  {
    $year_origin = date('Y');
  }

  $year_start = _get_option($options, 'year_start', $year_origin - 5);
  $year_end = _get_option($options, 'year_end', $year_origin + 5);

  $ascending = ($year_start < $year_end);
  $until_year = ($ascending) ? $year_end + 1 : $year_end - 1;

  for ($x = $year_start; $x != $until_year; ($ascending) ? $x++ : $x--)
  {
    $select_options[$x] = $x;
  }

  $option_tags = options_for_select($select_options, $value);

  return select_tag($name, $option_tags, $html_options);
}

/**
 * Enter description here...
 *
 * @param string $name
 * @param string $value (proper date format: array('year'=>2005, 'month'=>1, 'day'=1) or timestamp or english date text)
 * @param array $options
 * @return string
 */
function select_date_tag($name, $value, $options = array(), $html_options = array())
{
  $html_options = _parse_attributes($html_options);
  $options = _parse_attributes($options);

  $culture = _get_option($options, 'culture', sfContext::getInstance()->getUser()->getCulture());
  //set it back for month tag
  $option['culture'] = $culture;

  $I18n_arr = _get_I18n_date_locales($culture);

  $date_seperator = _get_option($options, 'date_seperator', $I18n_arr['date_seperator']);

  $discard_month = _get_option($options, 'discard_month');
  $discard_day = _get_option($options, 'discard_day');
  $discard_year = _get_option($options, 'discard_year');

  //discarding month automatically discards day
  if ($discard_month) 
    $discard_day = true;

  $order = _get_option($options, 'order');

  $tags = array();

  if (is_array($order) && count($order) == 3)
  {
    foreach ($order as $k => $v)
    {
      $tags[] = $v[0]; //'day' => 'd' | 'month' => 'm'
    }
  }
  else
  {
    $tags = $I18n_arr['date_order'];
  }

  if ($include_custom = _get_option($options, 'include_custom'))
  {
    $include_custom_month = (is_array($include_custom))
        ? ((isset($include_custom['month'])) ? array('include_custom'=>$include_custom['month']) : array()) 
        : array('include_custom'=>$include_custom);

    $include_custom_day = (is_array($include_custom))
        ? ((isset($include_custom['day'])) ? array('include_custom'=>$include_custom['day']) : array()) 
        : array('include_custom'=>$include_custom);

    $include_custom_year = (is_array($include_custom))
        ? ((isset($include_custom['year'])) ? array('include_custom'=>$include_custom['year']) : array()) 
        : array('include_custom'=>$include_custom);
  }
  else
  {
    $include_custom_month = array();
    $include_custom_day = array();
    $include_custom_year = array();
  }

  $html_options['id'] = $name . '_month';
  $m = ($discard_month != true) ? select_month_tag($name . '[month]', _parse_value_for_date($value, 'month', 'm'), $options + $include_custom_month, $html_options) : '';

  $html_options['id'] = $name . '_day';
  $d = ($discard_day != true) ? select_day_tag($name . '[day]', _parse_value_for_date($value, 'day', 'd'), $options + $include_custom_day, $html_options) : '';

  $html_options['id'] = $name . '_year';
  $y = ($discard_year != true) ? select_year_tag($name . '[year]', _parse_value_for_date($value, 'year', 'Y'), $options + $include_custom_year, $html_options) : '';

  //we have $tags = array ('m','d','y')
  foreach ($tags as $k => $v)
  {
    $tags[$k] = $$v;
  }

  return implode($date_seperator, $tags);
}

function select_second_tag($name, $value, $options = array(), $html_options = array())
{
  $select_options = array();

  if (_get_option($options, 'include_blank'))
  {
    $select_options[''] = '';
  }
  else if ($include_custom = _get_option($options, 'include_custom'))
  {
    $select_options[''] = $include_custom;
  }

  $second_step = _get_option($options, 'second_step', 1);
  for ($x = 0; $x < 60; $x += $second_step)
  {
    $select_options[$x] = _add_zeros($x, 2);
  }

  $option_tags = options_for_select($select_options, $value);

  return select_tag($name, $option_tags, $html_options);
}

function select_minute_tag($name, $value, $options = array(), $html_options = array())
{
  $select_options = array();

  if (_get_option($options, 'include_blank'))
  {
    $select_options[''] = '';
  }
  else if ($include_custom = _get_option($options, 'include_custom'))
  {
    $select_options[''] = $include_custom;
  }

  $minute_step = _get_option($options, 'minute_step', 1);
  for ($x = 0; $x < 60; $x += $minute_step)
  {
    $select_options[$x] = _add_zeros($x, 2);
  }

  $option_tags = options_for_select($select_options, $value);

  return select_tag($name, $option_tags, $html_options);
}

function select_hour_tag($name, $value, $options = array(), $html_options = array())
{
  $select_options = array();

  if (_get_option($options, 'include_blank'))
  {
    $select_options[''] = '';
  }
  else if ($include_custom = _get_option($options, 'include_custom'))
  {
    $select_options[''] = $include_custom;
  }

  $_12hour_time = _get_option($options, '12hour_time');

  $start_hour = ($_12hour_time) ? 1 : 0;
  $end_hour = ($_12hour_time) ? 12 : 23;

  for ($x = $start_hour; $x <= $end_hour; $x++)
  {
    $select_options[$x] = _add_zeros($x, 2);
  }

  $option_tags = options_for_select($select_options, $value);

  return select_tag($name, $option_tags, $html_options);
}

function select_ampm_tag($name, $value, $options = array(), $html_options = array())
{
  $select_options = array();

  if (_get_option($options, 'include_blank'))
  {
    $select_options[''] = '';
  }
  else if ($include_custom = _get_option($options, 'include_custom'))
  {
    $select_options[''] = $include_custom;
  }

  $select_options['AM'] = 'AM';
  $select_options['PM'] = 'PM';

  $option_tags = options_for_select($select_options, $value);

  return select_tag($name, $option_tags, $html_options);
}

/**
 * Enter description here...
 *
 * @param string $name
 * @param string $value (proper time format: array('hour'=>0, 'minute'=>0, 'second'=0) or timestamp or english date text)
 * @param array $options
 * @return string
 */
function select_time_tag($name, $value, $options = array(), $html_options = array())
{
  $html_options = _parse_attributes($html_options);
  $options = _parse_attributes($options);

  $time_seperator = _get_option($options, 'time_seperator', ':');
  $ampm_seperator = _get_option($options, 'ampm_seperator', '');
  $include_second = _get_option($options, 'include_second');
  $_12hour_time = _get_option($options, '12hour_time');

  $options['12hour_time'] = $_12hour_time; //set it back. hour tag needs it.

  if ($include_custom = _get_option($options, 'include_custom'))
  {
    $include_custom_hour = (is_array($include_custom))
        ? ((isset($include_custom['hour'])) ? array('include_custom'=>$include_custom['hour']) : array()) 
        : array('include_custom'=>$include_custom);

    $include_custom_minute = (is_array($include_custom))
        ? ((isset($include_custom['minute'])) ? array('include_custom'=>$include_custom['minute']) : array()) 
        : array('include_custom'=>$include_custom);

    $include_custom_second = (is_array($include_custom))
        ? ((isset($include_custom['second'])) ? array('include_custom'=>$include_custom['second']) : array()) 
        : array('include_custom'=>$include_custom);

    $include_custom_ampm = (is_array($include_custom))
        ? ((isset($include_custom['ampm'])) ? array('include_custom'=>$include_custom['ampm']) : array()) 
        : array('include_custom'=>$include_custom);
  }
  else
  {
    $include_custom_hour = array();
    $include_custom_minute = array();
    $include_custom_second = array();
    $include_custom_ampm = array();
  }

  $tags = array();

  $html_options['id'] = $name . '_hour';
  $tags[] = select_hour_tag($name . '[hour]', _parse_value_for_date($value, 'hour', ($_12hour_time) ? 'h' : 'H'), $options + $include_custom_hour, $html_options);

  $html_options['id'] = $name . '_minute';
  $tags[] = select_minute_tag($name . '[minute]', _parse_value_for_date($value, 'minute', 'i'), $options + $include_custom_minute, $html_options);

  if ($include_second)
  {
    $html_options['id'] = $name . '_second';
    $tags[] = select_second_tag($name . "[second]" , _parse_value_for_date($value, 'second', 's'), $options + $include_custom_second, $html_options);
  }

  $time = implode($time_seperator, $tags);

  if ($_12hour_time)
  {
    $html_options['id'] = $name . '_ampm';
    $time .=  $ampm_seperator . select_ampm_tag($name . "[ampm]" , _parse_value_for_date($value, 'ampm', 'A'), $options + $include_custom_ampm, $html_options);
  }

  return $time;
}

/**
 * Enter description here...
 *
 * @param string $name
 * @param string $value (proper datetime format YYYY-MM-DD HH:MM:SS)
 * @param array $options
 * @return string
 */
function select_datetime_tag($name, $value, $options = array(), $html_options = array())
{
  $options = _parse_attributes($options);
  $datetime_seperator = _get_option($options, 'datetime_seperator', '');

  $date = select_date_tag($name, $value, $options, $html_options);
  $time = select_time_tag($name, $value, $options, $html_options);

  return $date.$datetime_seperator.$time;
}

function _add_zeros($string, $strlen)
{
  if ($strlen > strlen($string))
  {
    for ($x = strlen($string); $x < $strlen; $x++)
    {
      $string = '0'.$string;
    }
  }

  return $string;
}

function _get_I18n_date_locales($culture = '')
{
  if (empty($culture))
  {
    $culture = sfContext::getInstance()->getUser()->getCulture();
  }

  $ret_val = array();
  $ret_val['culture'] = $culture;

  $dateFormatInfo = sfDateTimeFormatInfo::getInstance($culture);
  $date_format = strtolower($dateFormatInfo->getShortDatePattern());

  $ret_val['dateFormatInfo'] = $dateFormatInfo;
    
  $match_pattern = "/([dmy]+)(.*?)([dmy]+)(.*?)([dmy]+)/";
  if (!preg_match($match_pattern, $date_format, $match_arr))
  {
    //if matching fails use en shortdate
    preg_match($match_pattern, 'm/d/yy', $match_arr);
  }

  $ret_val['date_seperator'] = $match_arr[2];

  //unset all but [dmy]+
  unset($match_arr[0], $match_arr[2], $match_arr[4]);
  
  $cnt = 0;
  foreach ($match_arr as $k => $v)
  {
    $ret_val['date_order'][$cnt++] = $v[0]; //$arr[date_order][0] = 'm'; [1] = 'd'; [2] = 'y';
  }
  
  return $ret_val;
}

/**
* _parse_value_for_date function can parse any date field from $value given as:
*  - an array('year'=>2000, 'month'=> 1, ..
*  - a timestamp
*  - english text presentation of date (i.e '14:23', '03:30 AM', '2005-12-25' Refer to strtotime function in PHP manual)
*/
function _parse_value_for_date($value, $name, $format_char)
{
  if (is_array($value))
  {
    return (isset($value[$name])) ? $value[$name] : '';
  }
  else if (is_numeric($value))
  {
    return date($format_char, $value);
  }
  else if ($value == '' || ($name == 'ampm' && ($value == 'AM' || $value == 'PM')))
  {
    return $value;
  }

  return date($format_char, strtotime($value));
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
