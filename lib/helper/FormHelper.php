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

/**
* Returns a formatted set of <option> tags based on optional <i>$options</i> array variable.
*
* The options_for_select helper is usually called in conjunction with the select_tag helper, as it is relatively
* useless on its own. By passing an array of <i>$options</i>, the helper will automatically generate <option> tags
* using the array key as the value and the array value as the display title. Additionally the options_for_select tag is
* smart enough to detect nested arrays as <optgroup> tags.  If the helper detects that the array value is an array itself,
* it creates an <optgroup> tag with the name of the group being the key and the contents of the <optgroup> being the array.
*
* <b>Options:</b>
* - include_blank - Includes a blank <option> tag at the beginning of the string with an empty value
* - include_custom - Includes an <option> tag with a custom display title at the beginning of the string with an empty value
*
* <b>Examples:</b>
* <code>
*  echo select_tag('person', options_for_select(array(1 => 'Larry', 2 => 'Moe', 3 => 'Curly')));
* </code>
*
* <code>
*  $card_list = array('VISA' => 'Visa', 'MAST' => 'MasterCard', 'AMEX' => 'American Express', 'DISC' => 'Discover');
*  echo select_tag('cc_type', options_for_select($card_list), 'AMEX', array('include_custom' => '-- Select Credit Card Type --'));
* </code>
*
* <code>
*  $optgroup_array = array(1 => 'Joe', 2 => 'Sue', 'Group A' => array(3 => 'Mary', 4 => 'Tom'), 'Group B' => array(5 => 'Bill', 6 =>'Andy'));
*  echo select_tag('employee', options_for_select($optgroup_array, null, array('include_blank' => true)), array('class' => 'mystyle'));
* </code>
*
* @param  array dataset to create <option> tags and <optgroup> tags from
* @param  string selected option value
* @param  array  additional HTML compliant <option> tag parameters
* @return string populated with <option> tags derived from the <i>$options</i> array variable
* @see select_tag
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
    if (is_array($value))
    {
      $optgroup_html_options = $html_options;
      unset($optgroup_html_options['include_custom']);
      unset($optgroup_html_options['include_blank']);
      $html .= content_tag('optgroup', options_for_select($value, $selected, $optgroup_html_options), array('label' => $key));
    }
    else 
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
  }

  return $html;
}

/**
* Returns an HTML <form> tag that points to a valid action, route or URL as defined by <i>$url_for_options</i>.
*
* By default, the form tag is generated in POST format, but can easily be configured along with any additional
* HTML parameters via the optional <i>$options</i> variable. If you are using file uploads, be sure to set the 
* <i>multipart</i> option to true.
*
* <b>Options:</b>
* - multipart - When set to true, enctype is set to "multipart/form-data".
*
* <b>Examples:</b>
*   <code><?php echo form_tag('@myroute'); ?></code>
*   <code><?php echo form_tag('/module/action', array('name' => 'myformname', 'multipart' => true)); ?></code>
*
* @param  string valid action, route or URL
* @param  array optional HTML parameters for the <form> tag
* @return string opening HTML <form> tag with options
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

/**
* Returns a <select> tag, optionally comprised of <option> tags.
*
* The select tag does not generate <option> tags by default.  
* To do so, you must populate the <i>$option_tags</i> variable with a string of valid HTML compliant <option> tags.
* Fortunately, Symfony provides a handy helper function to convert an array of data into option tags (see options_for_select). 
* If you need to create a "multiple" select tag (ability to select multiple options), set the <i>multiple</i> option to true.  
* Doing so will automatically convert the name field to an array type variable (i.e. name="name" becomes name="name[]").
* 
* <b>Options:</b>
* - multiple - If set to true, the select tag will allow multiple options to be selected at once.
*
* <b>Examples:</b>
* <code>
*  $person_list = array(1 => 'Larry', 2 => 'Moe', 3 => 'Curly');
*  echo select_tag('person', options_for_select($person_list, $sf_params->get('person')), array('class' => 'full'));
* </code>
*
* <code>
*  echo select_tag('department', options_for_select($department_list), array('multiple' => true));
* </code>
*
* <code>
*  echo select_tag('url', options_for_select($url_list), array('onChange' => 'Javascript:this.form.submit();'));
* </code>
*
* @param  string field name 
* @param  string contains a string of valid <option></option> tags
* @param  array  additional HTML compliant <select> tag parameters
* @return string <select> tag optionally comprised of <option> tags.
* @see options_for_select, content_tag
*/
function select_tag($name, $option_tags = null, $options = array())
{
  $options = _convert_options($options);
  $id = $name;
  if (isset($options['multiple']) && $options['multiple'] && substr($name, -2) !== '[]')
  {
    $name .= '[]';
  }

  return content_tag('select', $option_tags, array_merge(array('name' => $name, 'id' => get_name_from_id($id)), $options));
}

/**
* Returns a <select> tag populated with all the countries in the world.
*
* The select_country_tag builds off the traditional select_tag function, and is conveniently populated with 
* all the countries in the world (sorted alphabetically). Each option in the list has a two-character country 
* code for its value and the country's name as its display title.  The country data is retrieved via the sfCultureInfo
* class, which stores a wide variety of i18n and i10n settings for various countries and cultures throughout the world.
* Here's an example of an <option> tag generated by the select_country_tag:
*
* <samp>
*  <option value="US">United States</option>
* </samp>
*
* <b>Examples:</b>
* <code>
*  echo select_country_tag('country', 'FR');
* </code>
*
* @param  string field name 
* @param  string selected field value (two-character country code)
* @param  array  additional HTML compliant <select> tag parameters
* @return string <select> tag populated with all the countries in the world.
* @see select_tag, options_for_select, sfCultureInfo
*/
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

/**
* Returns a <select> tag populated with all the languages in the world (or almost).
*
* The select_language_tag builds off the traditional select_tag function, and is conveniently populated with 
* all the languages in the world (sorted alphabetically). Each option in the list has a two or three character 
* language/culture code for its value and the language's name as its display title.  The country data is 
* retrieved via the sfCultureInfo class, which stores a wide variety of i18n and i10n settings for various 
* countries and cultures throughout the world. Here's an example of an <option> tag generated by the select_country_tag:
*
* <samp>
*  <option value="en">English</option>
* </samp>
*
* <b>Examples:</b>
* <code>
*  echo select_language_tag('language', 'de');
* </code>
*
* @param  string field name 
* @param  string selected field value (two or threecharacter language/culture code)
* @param  array  additional HTML compliant <select> tag parameters
* @return string <select> tag populated with all the languages in the world.
* @see select_tag, options_for_select, sfCultureInfo
*/
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

/**
* Returns an XHTML compliant <input> tag with type="text".
*
* The input_tag helper generates your basic XHTML <input> tag and can utilize any standard <input> tag parameters 
* passed in the optional <i>$options</i> variable.
*
* <b>Examples:</b>
* <code>
*  echo input_tag('name');
* </code>
*
* <code>
*  echo input_tag('amount', $sf_params->get('amount'), array('size' => 8, 'maxlength' => 8));
* </code>
*
* @param  string field name 
* @param  string selected field value
* @param  array  additional HTML compliant <input> tag parameters
* @return string XHTML compliant <input> tag with type="text"
*/
function input_tag($name, $value = null, $options = array())
{
  return tag('input', array_merge(array('type' => 'text', 'name' => $name, 'id' => get_name_from_id($name, $value), 'value' => $value), _convert_options($options)));
}

/**
* Returns an XHTML compliant <input> tag with type="hidden".
*
* Similar to the input_tag helper, the input_hidden_tag helper generates an XHTML <input> tag and can utilize 
* any standard <input> tag parameters passed in the optional <i>$options</i> variable.  The only difference is 
* that it creates the tag with type="hidden", meaning that is not visible on the page.
*
* <b>Examples:</b>
* <code>
*  echo input_hidden_tag('id', $id);
* </code>
*
* @param  string field name 
* @param  string populated field value
* @param  array  additional HTML compliant <input> tag parameters
* @return string XHTML compliant <input> tag with type="hidden"
*/
function input_hidden_tag($name, $value = null, $options = array())
{
  $options = _parse_attributes($options);

  $options['type'] = 'hidden';
  return input_tag($name, $value, $options);
}

/**
* Returns an XHTML compliant <input> tag with type="file".
*
* Similar to the input_tag helper, the input_hidden_tag helper generates your basic XHTML <input> tag and can utilize
* any standard <input> tag parameters passed in the optional <i>$options</i> variable.  The only difference is that it 
* creates the tag with type="file", meaning that next to the field will be a "browse" (or similar) button. 
* This gives the user the ability to choose a file from there computer to upload to the web server.  Remember, if you 
* plan to upload files to your website, be sure to set the <i>multipart</i> option form_tag helper function to true 
* or your files will not be properly uploaded to the web server.
*
* <b>Examples:</b>
* <code>
*  echo input_file_tag('filename', array('size' => 30));
* </code>
*
* @param  string field name 
* @param  array  additional HTML compliant <input> tag parameters
* @return string XHTML compliant <input> tag with type="file"
* @see input_tag, form_tag
*/
function input_file_tag($name, $options = array())
{
  $options = _parse_attributes($options);

  $options['type'] = 'file';
  return input_tag($name, null, $options);
}

/**
* Returns an XHTML compliant <input> tag with type="password".
*
* Similar to the input_tag helper, the input_hidden_tag helper generates your basic XHTML <input> tag and can utilize
* any standard <input> tag parameters passed in the optional <i>$options</i> variable.  The only difference is that it 
* creates the tag with type="password", meaning that the text entered into this field will not be visible to the end user.
* In most cases it is replaced by ********.  Even though this text is not readable, it is recommended that you do not 
* populate the optional <i>$value</i> option with a plain-text password or any other sensitive information, as this is a 
* potential security risk.
*
* <b>Examples:</b>
* <code>
*  echo input_password_tag('password');
*  echo input_password_tag('password_confirm');
* </code>
*
* @param  string field name
* @param  string populated field value
* @param  array  additional HTML compliant <input> tag parameters
* @return string XHTML compliant <input> tag with type="password"
* @see input_tag
*/
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

    if (isset($options['tinymce_options']))
    {
      unset($options['tinymce_options']);
    }

    return
      content_tag('script', javascript_cdata_section($tinymce_js), array('type' => 'text/javascript')).
      content_tag('textarea', $content, array_merge(array('name' => $name, 'id' => get_name_from_id($id, null)), _convert_options($options)));
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
    $fckeditor->BasePath = '/'.sfConfig::get('sf_rich_text_fck_js_dir').'/';
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

    if (isset($options['config']))
    {
      $fckeditor->Config['CustomConfigurationsPath'] = javascript_path($options['config']);
    }

    $content = $fckeditor->CreateHtml();

    return $content;
  }
  else
  {
    return content_tag('textarea', (is_object($content)) ? $content->__toString() : $content, array_merge(array('name' => $name, 'id' => get_name_from_id($id, null)), _convert_options($options)));
  }
}

function checkbox_tag($name, $value = '1', $checked = false, $options = array())
{
  $html_options = array_merge(array('type' => 'checkbox', 'name' => $name, 'id' => get_name_from_id($name, $value), 'value' => $value), _convert_options($options));
  if ($checked) $html_options['checked'] = 'checked';

  return tag('input', $html_options);
}

function radiobutton_tag($name, $value, $checked = false, $options = array())
{
  $html_options = array_merge(array('type' => 'radio', 'name' => $name, 'id' => get_name_from_id($name, $value), 'value' => $value), _convert_options($options));
  if ($checked) $html_options['checked'] = 'checked';

  return tag('input', $html_options);
}

function input_upload_tag($name, $options = array())
{
  $options = _parse_attributes($options);

  $options['type'] = 'file';

  return input_tag($name, '', $options);
}

function input_date_range_tag($name, $value, $options = array())
{
  $options = _parse_attributes($options);

  $before = '';
  if (isset($options['before']))
  {
    $before = $options['before'];
    unset($options['before']);
  }

  $middle = '';
  if (isset($options['middle']))
  {
    $middle = $options['middle'];
    unset($options['middle']);
  }

  $after = '';
  if (isset($options['after']))
  {
    $after = $options['after'];
    unset($options['after']);
  }

  return $before.
         input_date_tag($name.'[from]', $value['from'], $options).
         $middle.
         input_date_tag($name.'[to]', $value['to'], $options).
         $after;
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
  if (($value === null) || ($value === ''))
  {
    $value = '';
  }
  else
  {
    $dateFormat = new sfDateFormat($culture);
    $value = $dateFormat->format($value, 'd');
  }

  // register our javascripts and stylesheets
  $langFile = '/sf/js/calendar/lang/calendar-'.strtolower(substr($culture, 0, 2));
  $jss = array(
    '/sf/js/calendar/calendar',
    is_readable(sfConfig::get('sf_symfony_data_dir').'/web/'.$langFile.'.js') ? $langFile : '/sf/js/calendar/lang/calendar-en',
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
  if (!isset($options['size']))
  {
    $options['size'] = 9;
  }
  $html = input_tag($name, $value, $options);

  // calendar button
  $calendar_button = '...';
  $calendar_button_type = 'txt';
  if (isset($options['calendar_button_img']))
  {
    $calendar_button = $options['calendar_button_img'];
    $calendar_button_type = 'img';
    unset($options['calendar_button_img']);
  }
  else if (isset($options['calendar_button_txt']))
  {
    $calendar_button = $options['calendar_button_txt'];
    $calendar_button_type = 'txt';
    unset($options['calendar_button_txt']);
  }

  if ($calendar_button_type == 'img')
  {
    $html .= image_tag($calendar_button, array('id' => 'trigger_'.$name, 'style' => 'cursor: pointer; vertical-align: middle'));
  }
  else
  {
    $html .= content_tag('button', $calendar_button, array('type' => 'button', 'disabled' => 'disabled', 'onclick' => 'return false', 'id' => 'trigger_'.$name));
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

function select_day_tag($name, $value = null, $options = array(), $html_options = array())
{
  if ($value === null)
  {
    $value = date('j');
  }
    
  $options = _parse_attributes($options);

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
    $select_options[$x] = _prepend_zeros($x, 2);
  }

  return select_tag($name, options_for_select($select_options, $value), $html_options);
}

function select_month_tag($name, $value = null, $options = array(), $html_options = array())
{
  if ($value === null)
  {
    $value = date('n');
  }
    
  $options = _parse_attributes($options);

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
      $select_options[$k] = _prepend_zeros($k, 2);
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

  return select_tag($name, options_for_select($select_options, $value), $html_options);
}

function select_year_tag($name, $value = null, $options = array(), $html_options = array())
{
  if ($value === null)
  {
    $value = date('Y');
  }
    
  $options = _parse_attributes($options);

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

  return select_tag($name, options_for_select($select_options, $value), $html_options);
}

/**
 * Enter description here...
 *
 * @param string $name
 * @param string $value (proper date format: array('year'=>2005, 'month'=>1, 'day'=1) or timestamp or english date text)
 * @param array $options
 * @param array $html_options
 * @return string
 */
function select_date_tag($name, $value = null, $options = array(), $html_options = array())
{
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
  {
    $discard_day = true;
  }

  $order = _get_option($options, 'order');
  $tags = array();

  if (is_array($order) && count($order) == 3)
  {
    foreach ($order as $v)
    {
      $tags[] = $v[0];
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

  $month_name = $name . '[month]';
  $m = (!$discard_month) ? select_month_tag($month_name, _parse_value_for_date($value, 'month', 'm'), $options + $include_custom_month, $html_options) : '';


  $day_name = $name . '[day]';
  $d = (!$discard_day) ? select_day_tag($day_name, _parse_value_for_date($value, 'day', 'd'), $options + $include_custom_day, $html_options) : '';

  $year_name = $name . '[year]';
  $y = (!$discard_year) ? select_year_tag($year_name, _parse_value_for_date($value, 'year', 'Y'), $options + $include_custom_year, $html_options) : '';

  // we have $tags = array ('m','d','y')
  foreach ($tags as $k => $v)
  {
    // $tags['m|d|y'] = $m|$d|$y
    $tags[$k] = $$v;
  }

  return implode($date_seperator, $tags);
}

function select_second_tag($name, $value = null, $options = array(), $html_options = array())
{
  if ($value === null)
  {
    $value = date('s');
  }
  
  $options = _parse_attributes($options);
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
    $select_options[$x] = _prepend_zeros($x, 2);
  }

  return select_tag($name, options_for_select($select_options, $value), $html_options);
}

function select_minute_tag($name, $value = null, $options = array(), $html_options = array())
{
  if ($value === null)
  {
    $value = date('i');
  }
    
  $options = _parse_attributes($options);
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
    $select_options[$x] = _prepend_zeros($x, 2);
  }

  return select_tag($name, options_for_select($select_options, $value), $html_options);
}

function select_hour_tag($name, $value = null, $options = array(), $html_options = array())
{
  if ($value === null)
  {
    $value = date('h');
  }
    
  $options = _parse_attributes($options);
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
    $select_options[$x] = _prepend_zeros($x, 2);
  }

  return select_tag($name, options_for_select($select_options, $value), $html_options);
}

function select_ampm_tag($name, $value = null, $options = array(), $html_options = array())
{
  if ($value === null)
  {
    $value = date('A');
  }
    
  $options = _parse_attributes($options);
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

  return select_tag($name, options_for_select($select_options, $value), $html_options);
}

/**
 * Enter description here...
 *
 * @param string $name
 * @param string $value (proper time format: array('hour'=>0, 'minute'=>0, 'second'=0) or timestamp or english date text)
 * @param array $options
 * @param array $html_options
 * @return string
 */
function select_time_tag($name, $value = null, $options = array(), $html_options = array())
{
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

  $hour_name = $name . '[hour]';
  $tags[] = select_hour_tag($hour_name, _parse_value_for_date($value, 'hour', ($_12hour_time) ? 'h' : 'H'), $options + $include_custom_hour, $html_options);

  $minute_name = $name . '[minute]';
  $tags[] = select_minute_tag($minute_name, _parse_value_for_date($value, 'minute', 'i'), $options + $include_custom_minute, $html_options);

  if ($include_second)
  {
    $second_name = $name . '[second]';
    $tags[] = select_second_tag($second_name, _parse_value_for_date($value, 'second', 's'), $options + $include_custom_second, $html_options);
  }

  $time = implode($time_seperator, $tags);

  if ($_12hour_time)
  {
    $ampm_name = $name . "[ampm]";
    $time .=  $ampm_seperator . select_ampm_tag($ampm_name, _parse_value_for_date($value, 'ampm', 'A'), $options + $include_custom_ampm, $html_options);
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
function select_datetime_tag($name, $value = null, $options = array(), $html_options = array())
{
  $options = _parse_attributes($options);
  $datetime_seperator = _get_option($options, 'datetime_seperator', '');

  $date = select_date_tag($name, $value, $options, $html_options);
  $time = select_time_tag($name, $value, $options, $html_options);

  return $date.$datetime_seperator.$time;
}

function label_for($id, $label, $options = array())
{
  $options = _parse_attributes($options);

  return content_tag('label', $label, array_merge(array('for' => get_name_from_id($id, null)), $options));
}

function get_name_from_id($name, $value = null)
{
  // check to see if we have an array variable for a field name
  if (strstr($name, '['))
  {
    $name = str_replace(
        array('[]', '][', '[', ']'),
        array((($value != null) ? '_'.$value : ''), '_', '_', ''),
        $name
    );
  }

  return $name;
}

function _prepend_zeros($string, $strlen)
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

function _get_I18n_date_locales($culture = null)
{
  if (!$culture)
  {
    $culture = sfContext::getInstance()->getUser()->getCulture();
  }

  $retval = array('culture'=>$culture);

  $dateFormatInfo = sfDateTimeFormatInfo::getInstance($culture);
  $date_format = strtolower($dateFormatInfo->getShortDatePattern());

  $retval['dateFormatInfo'] = $dateFormatInfo;
    
  $match_pattern = "/([dmy]+)(.*?)([dmy]+)(.*?)([dmy]+)/";
  if (!preg_match($match_pattern, $date_format, $match_arr))
  {
    //if matching fails use en shortdate
    preg_match($match_pattern, 'm/d/yy', $match_arr);
  }

  $retval['date_seperator'] = $match_arr[2];

  //unset all but [dmy]+
  unset($match_arr[0], $match_arr[2], $match_arr[4]);
  
  $retval['date_order'] = array();
  foreach ($match_arr as $v)
  {
    // 'm/d/yy' => $retval[date_order] = array ('m', 'd', 'y');
    $retval['date_order'][] = $v[0];
  }
  
  return $retval;
}

/*
      # _parse_value_for_date can parse any date field from $value given as:
      # 1. $value = array('year'=>2000, 'month'=>1, 'day'=>1) and $key = 'year|month|day'
      # 2. $value = timestamp and $format_char = 'h|H|i|s|A|d|m|Y'
      # 3. english text presentation of date (i.e '14:23', '03:30 AM', '2005-12-25' Refer to strtotime function in PHP manual)
*/
function _parse_value_for_date($value, $key, $format_char)
{
  if (is_array($value))
  {
    return (isset($value[$key])) ? $value[$key] : '';
  }
  else if (is_numeric($value))
  {
    return date($format_char, $value);
  }
  else if ($value == '' || ($key == 'ampm' && ($value == 'AM' || $value == 'PM')))
  {
    return $value;
  }
  else if (empty($value))
  {
    $value = date('Y-m-d H:i:s');
  }

  // english text presentation
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
