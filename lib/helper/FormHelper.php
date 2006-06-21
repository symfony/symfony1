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
 * - include_blank  - Includes a blank <option> tag at the beginning of the string with an empty value
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
 * HTML parameters via the optional <i>$options</i> parameter. If you are using file uploads, be sure to set the 
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
 * To do so, you must populate the <i>$option_tags</i> parameter with a string of valid HTML compliant <option> tags.
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

  return content_tag('select', $option_tags, array_merge(array('name' => $name, 'id' => get_id_from_name($id)), $options));
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
 * passed in the optional <i>$options</i> parameter.
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
  return tag('input', array_merge(array('type' => 'text', 'name' => $name, 'id' => get_id_from_name($name, $value), 'value' => $value), _convert_options($options)));
}

/**
 * Returns an XHTML compliant <input> tag with type="hidden".
 *
 * Similar to the input_tag helper, the input_hidden_tag helper generates an XHTML <input> tag and can utilize 
 * any standard <input> tag parameters passed in the optional <i>$options</i> parameter.  The only difference is 
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
 * any standard <input> tag parameters passed in the optional <i>$options</i> parameter.  The only difference is that it 
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
 * any standard <input> tag parameters passed in the optional <i>$options</i> parameter.  The only difference is that it 
 * creates the tag with type="password", meaning that the text entered into this field will not be visible to the end user.
 * In most cases it is replaced by  * * * * * * * *.  Even though this text is not readable, it is recommended that you do not 
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
 * Returns a <textarea> tag, optionally wrapped with an inline rich-text JavaScript editor.
 *
 * The texarea_tag helper generates a standard HTML <textarea> tag and can be manipulated with
 * any number of standard HTML parameters via the <i>$options</i> array variable.  However, the 
 * textarea tag also has the unique capability of being transformed into a WYSIWYG rich-text editor
 * such as TinyMCE (http://tinymce.moxiecode.com) or FCKEditor (http://www.fckeditor.net) very 
 * easily with the use of some specific options:
 *
 * <b>Options:</b>
 *  - rich - Enables TinyMCE or FCKEditor with the value <i>tinymce</i> or <i>fck</i> respectively
 *
 * <b>TinyMCE Specific Options:</b>
 *  - css - Path to the TinyMCE editor stylesheet
 *
 *    <b>Css example:</b>
 *    <code>
 *    / * user: foo * / => without spaces. 'foo' is the name in the select box
 *    .foobar
 *    {
 *      color: #f00;
 *    }
 *    </code>
 *
 * <b>FCKEditor Specific Options:</b>
 *  - tool   - Sets the FCKEditor toolbar style
 *  - config - Sets custom path to the FCKEditor configuration file
 *
 * <b>Examples:</b>
 * <code>
 *  echo textarea_tag('notes');
 * </code>
 *
 * <code>
 *  echo textarea_tag('description', 'This is a description', array('rows' => 10, 'cols' => 50));
 * </code> 
 *
 * @param  string field name
 * @param  string populated field value
 * @param  array  additional HTML compliant <textarea> tag parameters
 * @return string <textarea> tag optionally wrapped with a rich-text WYSIWYG editor
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
      content_tag('textarea', $content, array_merge(array('name' => $name, 'id' => get_id_from_name($id, null)), _convert_options($options)));
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
    return content_tag('textarea', (is_object($content)) ? $content->__toString() : $content, array_merge(array('name' => $name, 'id' => get_id_from_name($id, null)), _convert_options($options)));
  }
}

/**
 * Returns an XHTML compliant <input> tag with type="checkbox".
 *
 * When creating multiple checkboxes with the same name, be sure to use an array for the
 * <i>$name</i> parameter (i.e. 'name[]').  The checkbox_tag is smart enough to create unique ID's
 * based on the <i>$value</i> parameter like so:
 *
 * <samp>
 *  <input type="checkbox" name="status[]" id="status_3" value="3" />
 *  <input type="checkbox" name="status[]" id="status_4" value="4" />
 * </samp>
 * 
 * <b>Examples:</b>
 * <code>
 *  echo checkbox_tag('newsletter', 1, $sf_params->get('newsletter'));
 * </code>
 *
 * <code>
 *  echo checkbox_tag('option_a', 'yes', true, array('class' => 'style_a'));
 * </code>
 *
 * <code>
 *  // one request variable with an array of checkbox values
 *  echo checkbox_tag('choice[]', 1);
 *  echo checkbox_tag('choice[]', 2);
 *  echo checkbox_tag('choice[]', 3);
 *  echo checkbox_tag('choice[]', 4); 
 * </code>
 *
 * <code>
 *  // assuming you have Prototype.js enabled, you could do this
 *  echo checkbox_tag('show_tos', 1, false, array('onclick' => "Element.toggle('tos'); return false;"));
 * </code>
 *
 * @param  string field name 
 * @param  string checkbox value (if checked)
 * @param  bool   is the checkbox checked? (1 or 0)
 * @param  array  additional HTML compliant <input> tag parameters
 * @return string XHTML compliant <input> tag with type="checkbox"
 */
function checkbox_tag($name, $value = '1', $checked = false, $options = array())
{
  $html_options = array_merge(array('type' => 'checkbox', 'name' => $name, 'id' => get_id_from_name($name, $value), 'value' => $value), _convert_options($options));
  if ($checked) $html_options['checked'] = 'checked';

  return tag('input', $html_options);
}

/**
 * Returns an XHTML compliant <input> tag with type="radio".
 * 
 * <b>Examples:</b>
 * <code>
 *  echo ' Yes ' . radiobutton_tag('newsletter', 1);
 *  echo ' No ' . radiobutton_tag('newsletter', 0); 
 * </code>
 *
 * @param  string field name 
 * @param  string radio button value (if selected)
 * @param  bool   is the radio button selected? (1 or 0)
 * @param  array  additional HTML compliant <input> tag parameters
 * @return string XHTML compliant <input> tag with type="radio"
 */
function radiobutton_tag($name, $value, $checked = false, $options = array())
{
  $html_options = array_merge(array('type' => 'radio', 'name' => $name, 'id' => get_id_from_name($name, $value), 'value' => $value), _convert_options($options));
  if ($checked) $html_options['checked'] = 'checked';

  return tag('input', $html_options);
}

/**
 * Returns an XHTML compliant <input> tag with type="file".
 *
 * Alias for input_file_tag
 *
 * <b>DEPRECIATED:</b> Use input_file_tag
 * @see input_file_tag
 */
function input_upload_tag($name, $options = array())
{
  if (sfConfig::get('sf_logging_active'))
  {
    sfContext::getInstance()->getLogger()->err('This function is deprecated. Please use input_file_tag.');
  }
  
  return input_file_tag($name, $options);
}

/**
 * Returns two XHTML compliant <input> tags to be used as a free-text date fields for a date range.
 * 
 * Built on the input_date_tag, the input_date_range_tag combines two input tags that allow the user
 * to specify a from and to date.  
 * You can easily implement a JavaScript calendar by enabling the 'rich' option in the 
 * <i>$options</i> parameter.  This includes a button next to the field that when clicked, 
 * will open an inline JavaScript calendar.  When a date is selected, it will automatically
 * populate the <input> tag with the proper date, formatted to the user's culture setting.
 *
 * <b>Note:</b> The <i>$name</i> parameter will automatically converted to array names. 
 * For example, a <i>$name</i> of "date" becomes date[from] and date[to]
 * 
 * <b>Options:</b>
 * - rich - If set to true, includes an inline JavaScript calendar can auto-populate the date field with the chosen date
 * - before - string to be displayed before the input_date_range_tag
 * - middle - string to be displayed between the from and to tags
 * - after - string to be displayed after the input_date_range_tag
 *
 * <b>Examples:</b>
 * <code>
 *  $date = array('from' => '2006-05-15', 'to' => '2006-06-15');
 *  echo input_date_range_tag('date', $date, array('rich' => true));
 * </code>
 *
 * <code>
 *  echo input_date_range_tag('date', null, array('middle' => ' through ', 'rich' => true));
 * </code>
 *
 * @param  string field name 
 * @param  array  dates: $value['from'] and $value['to']
 * @param  array  additional HTML compliant <input> tag parameters
 * @return string XHTML compliant <input> tag with optional JS calendar integration
 * @see input_date_tag
 */
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

/**
 * Returns an XHTML compliant <input> tag to be used as a free-text date field.
 * 
 * You can easily implement a JavaScript calendar by enabling the 'rich' option in the 
 * <i>$options</i> parameter.  This includes a button next to the field that when clicked, 
 * will open an inline JavaScript calendar.  When a date is selected, it will automatically
 * populate the <input> tag with the proper date, formatted to the user's culture setting. 
 * Symfony also conveniently offers the input_date_range_tag, that allows you to specify a to
 * and from date.
 *
 * <b>Options:</b>
 * - rich - If set to true, includes an inline JavaScript calendar can auto-populate the date field with the chosen date
 *
 * <b>Examples:</b>
 * <code>
 *  echo input_date_tag('date', null, array('rich' => true));
 * </code>
 *
 * @param  string field name 
 * @param  string date
 * @param  array  additional HTML compliant <input> tag parameters
 * @return string XHTML compliant <input> tag with optional JS calendar integration
 * @see input_date_range_tag
 */
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

/**
 * Returns an XHTML compliant <input> tag with type="submit".
 * 
 * By default, this helper creates a submit tag with a name of <em>commit</em> to avoid
 * conflicts with other parts of the framework.  It is recommended that you do not use the name
 * "submit" for submit tags unless absolutely necessary. Also, the default <i>$value</i> parameter
 * (title of the button) is set to "Save changes", which can be easily overwritten by passing a 
 * <i>$value</i> parameter.
 *
 * <b>Examples:</b>
 * <code>
 *  echo submit_tag();
 * </code>
 *
 * <code>
 *  echo submit_tag('Update Record');
 * </code>
 *
 * @param  string field value (title of submit button)
 * @param  array  additional HTML compliant <input> tag parameters
 * @return string XHTML compliant <input> tag with type="submit"
 */
function submit_tag($value = 'Save changes', $options = array())
{
  return tag('input', array_merge(array('type' => 'submit', 'name' => 'commit', 'value' => $value), _convert_options($options)));
}

/**
 * Returns an XHTML compliant <input> tag with type="reset".
 *
 * By default, this helper creates a submit tag with a name of <em>reset</em>.  Also, the default 
 * <i>$value</i> parameter (title of the button) is set to "Reset" which can be easily overwritten 
 * by passing a <i>$value</i> parameter.
 *
 * <b>Examples:</b>
 * <code>
 *  echo reset_tag();
 * </code>
 *
 * <code>
 *  echo reset_tag('Start Over');
 * </code>
 *
 * @param  string field value (title of reset button)
 * @param  array  additional HTML compliant <input> tag parameters
 * @return string XHTML compliant <input> tag with type="reset"
 */
function reset_tag($value = 'Reset', $options = array())
{
  return tag('input', array_merge(array('type' => 'reset', 'name' => 'reset', 'value' => $value), _convert_options($options)));
}

/**
 * Returns an XHTML compliant <input> tag with type="image".
 *
 * The submit_image_tag is very similar to the submit_tag, the only difference being that it uses an image
 * for the submit button instead of the browser-generated default button. The image is defined by the 
 * <i>$source</i> parameter and must be a valid image, either local or remote (URL). By default, this 
 * helper creates a submit tag with a name of <em>commit</em> to avoid conflicts with other parts of the 
 * framework.  It is recommended that you do not use the name "submit" for submit tags unless absolutely necessary.
 *
 * <b>Examples:</b>
 * <code>
 *  // Assuming your image is in the /web/images/ directory
 *  echo submit_image_tag('my_submit_button.gif');
 * </code>
 *
 * <code>
 *  echo submit_image_tag('http://mydomain.com/my_submit_button.gif');
 * </code>
 *
 * @param  string path to image file
 * @param  array  additional HTML compliant <input> tag parameters
 * @return string XHTML compliant <input> tag with type="image"
 */
function submit_image_tag($source, $options = array())
{
  return tag('input', array_merge(array('type' => 'image', 'name' => 'commit', 'src' => image_path($source)), _convert_options($options)));
}

/**
 * Returns a <select> tag populated with all the days of the month (1 - 31).
 *
 * By default, the <i>$value</i> parameter is set to today's day. To override this, simply pass an integer 
 * (1 - 31) to the <i>$value</i> parameter. You can also set the <i>$value</i> parameter to null which will disable
 * the <i>$value</i>, however this will only be useful if you pass 'include_blank' or 'include_custom' to the <i>$options</i>
 * parameter. For convenience, Symfony also offers the select_date_tag helper function which combines the 
 * select_year_tag, select_month_tag, and select_day_tag functions into a single helper.
 *
 * <b>Options:</b>
 * - include_blank  - Includes a blank <option> tag at the beginning of the string with an empty value.
 * - include_custom - Includes an <option> tag with a custom display title at the beginning of the string with an empty value.
 *
 * <b>Examples:</b>
 * <code>
 *  echo submit_day_tag('day', 14);
 * </code>
 *
 * @param  string field name
 * @param  integer selected value (1 - 31)
 * @param  array  additional HTML compliant <select> tag parameters
 * @return string <select> tag populated with all the days of the month (1 - 31).
 * @see select_date_tag, select datetime_tag
 */
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

/**
 * Returns a <select> tag populated with all the months of the year (1 - 12).
 *
 * By default, the <i>$value</i> parameter is set to today's month. To override this, simply pass an integer 
 * (1 - 12) to the <i>$value</i> parameter. You can also set the <i>$value</i> parameter to null which will disable
 * the <i>$value</i>, however this will only be useful if you pass 'include_blank' or 'include_custom' to the <i>$options</i>
 * parameter. Also, the each month's display title is set to return its respective full month name, which can be easily 
 * overridden by passing the 'use_short_names' or 'use_month_numbers' options to the <i>$options</i> parameter.
 * For convenience, Symfony also offers the select_date_tag helper function which combines the 
 * select_year_tag, select_month_tag, and select_day_tag functions into a single helper.
 *
 * <b>Options:</b>
 * - include_blank     - Includes a blank <option> tag at the beginning of the string with an empty value
 * - include_custom    - Includes an <option> tag with a custom display title at the beginning of the string with an empty value
 * - use_month_numbers - If set to true, will show the month's numerical value (1 - 12) instead of the months full name.
 * - use_short_month   - If set to true, will show the month's short name (i.e. Jan, Feb, Mar) instead of its full name.
 *  
 * <b>Examples:</b>
 * <code>
 *  echo submit_month_tag('month', 5, array('use_short_month' => true));
 * </code>
 *
 * <code>
 *  echo submit_month_tag('month', null, array('use_month_numbers' => true, 'include_blank' => true));
 * </code>
 *
 * @param  string field name
 * @param  integer selected value (1 - 12)
 * @param  array  additional HTML compliant <select> tag parameters
 * @return string <select> tag populated with all the months of the year (1 - 12).
 * @see select_date_tag, select datetime_tag
 */
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

/**
 * Returns a <select> tag populated with a range of years.
 *
 * By default, the <i>$value</i> parameter is set to today's year. To override this, simply pass a four-digit integer (YYYY)
 * to the <i>$value</i> parameter. You can also set the <i>$value</i> parameter to null which will disable
 * the <i>$value</i>, however this will only be useful if you pass 'include_blank' or 'include_custom' to the <i>$options</i>
 * parameter. Also, the default selectable range of years is set to show five years back and five years forward from today's year.
 * For instance, if today's year is 2006, the default 'year_start' option will be set to 2001 and the 'year_end' option will be set
 * to 2011.  These start and end dates can easily be overwritten by setting the 'year_start' and 'year_end' options in the <i>$options</i>
 * parameter. For convenience, Symfony also offers the select_date_tag helper function which combines the 
 * select_year_tag, select_month_tag, and select_day_tag functions into a single helper.
 *
 * <b>Options:</b>
 * - include_blank  - Includes a blank <option> tag at the beginning of the string with an empty value
 * - include_custom - Includes an <option> tag with a custom display title at the beginning of the string with an empty value
 * - year_start     - If set, the range of years will begin at this four-digit date (i.e. 1979)
 * - year_end       - If set, the range of years will end at this four-digit date (i.e. 2025)
 *  
 * <b>Examples:</b>
 * <code>
 *  echo submit_year_tag('year');
 * </code>
 *
 * <code>
 *  $year_start = date('Y', strtotime('-10 years'));
 *  $year_end = date('Y', strtotime('+10 years'));
 *  echo submit_year_tag('year', null, array('year_start' => $year_start, 'year_end' => $year_end));
 * </code>
 *
 * @param  string field name
 * @param  integer selected value within the range of years.
 * @param  array  additional HTML compliant <select> tag parameters
 * @return string <select> tag populated with a range of years.
 * @see select_date_tag, select datetime_tag
 */
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
 * Returns three <select> tags populated with a range of months, days, and years.
 *
 * By default, the <i>$value</i> parameter is set to today's month, day and year. To override this, simply pass a valid date
 * or a correctly formatted date array (see example) to the <i>$value</i> parameter. You can also set the <i>$value</i> 
 * parameter to null which will disable the <i>$value</i>, however this will only be useful if you pass 'include_blank' or 
 * 'include_custom' to the <i>$options</i> parameter. Also, the default selectable range of years is set to show five years 
 * back and five years forward from today's year. For instance, if today's year is 2006, the default 'year_start' option will 
 * be set to 2001 and the 'year_end' option will be set to 2011.  These start and end dates can easily be overwritten by 
 * setting the 'year_start' and 'year_end' options in the <i>$options</i> parameter. 
 *
 * <b>Note:</b> The <i>$name</i> parameter will automatically converted to array names. For example, a <i>$name</i> of "date" becomes:
 * <samp>
 *  <select name="date[month]">...</select>
 *  <select name="date[day]">...</select>
 *  <select name="date[year]">...</select>
 * </samp>
 *  
 * <b>Options:</b>
 * - include_blank     - Includes a blank <option> tag at the beginning of the string with an empty value.
 * - include_custom    - Includes an <option> tag with a custom display title at the beginning of the string with an empty value.
 * - discard_month     - If set to true, will only return select tags for day and year.
 * - discard_day       - If set to true, will only return select tags for month and year.
 * - discard_year      - If set to true, will only return select tags for month and day.
 * - use_month_numbers - If set to true, will show the month's numerical value (1 - 12) instead of the months full name.
 * - use_short_month   - If set to true, will show the month's short name (i.e. Jan, Feb, Mar) instead of its full name.
 * - year_start        - If set, the range of years will begin at this four-digit date (i.e. 1979)
 * - year_end          - If set, the range of years will end at this four-digit date (i.e. 2025)
 * - date_seperator    - Includes a string of defined text between each generated select tag
 *  
 * <b>Examples:</b>
 * <code>
 *  echo submit_date_tag('date');
 * </code>
 *
 * <code>
 *  echo select_date_tag('date', '2006-10-30');
 * </code>
 *
 * <code>
 *  $date = array('year' => '1979', 'month' => 10, 'day' => 30);
 *  echo select_date_tag('date', $date, array('year_start' => $date['year'] - 10, 'year_end' => $date['year'] + 10));
 * </code>
 *
 * @param  string field name (automatically becomes an array of parts: name[year], name[month], year[day])
 * @param  mixed  accepts a valid date string or properly formatted date array
 * @param  array  additional HTML compliant <select> tag parameters
 * @return string three <select> tags populated with a months, days and years
 * @see select datetime_tag, select_month_tag, select_date_tag, select_year_tag
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

/**
 * Returns a <select> tag populated with 60 seconds (0 - 59).
 *
 * By default, the <i>$value</i> parameter is set to the current second (right now). To override this, simply pass an integer 
 * (0 - 59) to the <i>$value</i> parameter. You can also set the <i>$value</i> parameter to null which will disable
 * the <i>$value</i>, however this will only be useful if you pass 'include_blank' or 'include_custom' to the <i>$options</i>
 * parameter. In many cases, you have no need for all 60 seconds in a minute.  the 'second_step' option in the 
 * <i>$options</i> parameter gives you the ability to define intervals to display.  So for instance you could define 15 as your 
 * 'minute_step' interval and the select tag would return the values 0, 15, 30, and 45. For convenience, Symfony also offers the 
 * select_time_tag select_datetime_tag helper functions which combine other date and time helpers to easily build date and time select boxes.
 *
 * <b>Options:</b>
 * - include_blank  - Includes a blank <option> tag at the beginning of the string with an empty value.
 * - include_custom - Includes an <option> tag with a custom display title at the beginning of the string with an empty value.
 * - second_step    - If set, the seconds will be incremented in blocks of X, where X = 'second_step'
 * 
 * <b>Examples:</b>
 * <code>
 *  echo submit_second_tag('second');
 * </code>
 *
 * <code>
 *  echo submit_second_tag('second', 15, array('second_step' => 15));
 * </code>
 *
 * @param  string field name
 * @param  integer selected value (0 - 59)
 * @param  array  additional HTML compliant <select> tag parameters
 * @return string <select> tag populated with 60 seconds (0 - 59).
 * @see select_time_tag, select datetime_tag
 */
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

/**
 * Returns a <select> tag populated with 60 minutes (0 - 59).
 *
 * By default, the <i>$value</i> parameter is set to the current minute. To override this, simply pass an integer 
 * (0 - 59) to the <i>$value</i> parameter. You can also set the <i>$value</i> parameter to null which will disable
 * the <i>$value</i>, however this will only be useful if you pass 'include_blank' or 'include_custom' to the <i>$options</i>
 * parameter. In many cases, you have no need for all 60 minutes in an hour.  the 'minute_step' option in the 
 * <i>$options</i> parameter gives you the ability to define intervals to display.  So for instance you could define 15 as your 
 * 'minute_step' interval and the select tag would return the values 0, 15, 30, and 45. For convenience, Symfony also offers the 
 * select_time_tag select_datetime_tag helper functions which combine other date and time helpers to easily build date and time select boxes.
 *
 * <b>Options:</b>
 * - include_blank  - Includes a blank <option> tag at the beginning of the string with an empty value.
 * - include_custom - Includes an <option> tag with a custom display title at the beginning of the string with an empty value.
 * - minute_step    - If set, the minutes will be incremented in blocks of X, where X = 'minute_step'
 * 
 * <b>Examples:</b>
 * <code>
 *  echo submit_minute_tag('minute');
 * </code>
 *
 * <code>
 *  echo submit_minute_tag('minute', 15, array('minute_step' => 15));
 * </code>
 *
 * @param  string field name
 * @param  integer selected value (0 - 59)
 * @param  array  additional HTML compliant <select> tag parameters
 * @return string <select> tag populated with 60 minutes (0 - 59).
 * @see select_time_tag, select datetime_tag
 */
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

/**
 * Returns a <select> tag populated with 24 hours (0 - 23), or optionally 12 hours (1 - 12).
 *
 * By default, the <i>$value</i> parameter is set to the current hour. To override this, simply pass an integer 
 * (0 - 23 or 1 - 12 if '12hour_time' = true) to the <i>$value</i> parameter. You can also set the <i>$value</i> parameter to null which will disable
 * the <i>$value</i>, however this will only be useful if you pass 'include_blank' or 'include_custom' to the <i>$options</i>
 * parameter. For convenience, Symfony also offers the select_time_tag select_datetime_tag helper functions
 * which combine other date and time helpers to easily build date and time select boxes.
 *
 * <b>Options:</b>
 * - include_blank  - Includes a blank <option> tag at the beginning of the string with an empty value.
 * - include_custom - Includes an <option> tag with a custom display title at the beginning of the string with an empty value.
 * - 12hour_time    - If set to true, will return integers 1 through 12 instead of the default 0 through 23 as well as an AM/PM select box.
 * 
 * <b>Examples:</b>
 * <code>
 *  echo submit_hour_tag('hour');
 * </code>
 *
 * <code>
 *  echo submit_hour_tag('hour', 6, array('12hour_time' => true));
 * </code>
 *
 * @param  string field name
 * @param  integer selected value (0 - 23 or 1 - 12 if '12hour_time' = true)
 * @param  array  additional HTML compliant <select> tag parameters
 * @return string <select> tag populated with 24 hours (0 - 23), or optionally 12 hours (1 - 12).
 * @see select_time_tag, select datetime_tag
 */
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

/**
 * Returns a <select> tag populated with AM and PM options for use with 12-Hour time.
 *
 * By default, the <i>$value</i> parameter is set to the correct AM/PM setting based on the current time. 
 * To override this, simply pass either AM or PM to the <i>$value</i> parameter. You can also set the 
 * <i>$value</i> parameter to null which will disable the <i>$value</i>, however this will only be 
 * useful if you pass 'include_blank' or 'include_custom' to the <i>$options</i> parameter. For 
 * convenience, Symfony also offers the select_time_tag select_datetime_tag helper functions
 * which combine other date and time helpers to easily build date and time select boxes.
 *
 * <b>Options:</b>
 * - include_blank  - Includes a blank <option> tag at the beginning of the string with an empty value.
 * - include_custom - Includes an <option> tag with a custom display title at the beginning of the string with an empty value.
 * 
 * <b>Examples:</b>
 * <code>
 *  echo submit_ampm_tag('ampm');
 * </code>
 *
 * <code>
 *  echo submit_ampm_tag('ampm', 'PM', array('include_blank' => true));
 * </code>
 *
 * @param  string field name
 * @param  integer selected value (AM or PM)
 * @param  array  additional HTML compliant <select> tag parameters
 * @return string <select> tag populated with AM and PM options for use with 12-Hour time.
 * @see select_time_tag, select datetime_tag
 */
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
 * Returns three <select> tags populated with hours, minutes, and optionally seconds.
 *
 * By default, the <i>$value</i> parameter is set to the current hour and minute. To override this, simply pass a valid time
 * or a correctly formatted time array (see example) to the <i>$value</i> parameter. You can also set the <i>$value</i> 
 * parameter to null which will disable the <i>$value</i>, however this will only be useful if you pass 'include_blank' or 
 * 'include_custom' to the <i>$options</i> parameter. To include seconds to the result, use set the 'include_second' option in the 
 * <i>$options</i> parameter to true. <b>Note:</b> The <i>$name</i> parameter will automatically converted to array names. 
 * For example, a <i>$name</i> of "time" becomes:
 * <samp>
 *  <select name="time[hour]">...</select>
 *  <select name="time[minute]">...</select>
 *  <select name="time[second]">...</select>
 * </samp>
 *  
 * <b>Options:</b>
 * - include_blank  - Includes a blank <option> tag at the beginning of the string with an empty value.
 * - include_custom - Includes an <option> tag with a custom display title at the beginning of the string with an empty value.
 * - include_second - If set to true, includes the "seconds" select tag as part of the result.
 * - second_step    - If set, the seconds will be incremented in blocks of X, where X = 'second_step'
 * - minute_step    - If set, the minutes will be incremented in blocks of X, where X = 'minute_step'
 * - 12hour_time    - If set to true, will return integers 1 through 12 instead of the default 0 through 23 as well as an AM/PM select box.
 * - time_seperator - Includes a string of defined text between each generated select tag
 * - ampm_seperator - Includes a string of defined text between the minute/second select box and the AM/PM select box 
 *  
 * <b>Examples:</b>
 * <code>
 *  echo submit_time_tag('time');
 * </code>
 *
 * <code>
 *  echo select_time_tag('date', '09:31');
 * </code>
 *
 * <code>
 *  $time = array('hour' => '15', 'minute' => 46, 'second' => 01);
 *  echo select_time_tag('time', $time, array('include_second' => true, '12hour_time' => true));
 * </code>
 *
 * @param  string field name (automatically becomes an array of parts: name[hour], name[minute], year[second])
 * @param  mixed  accepts a valid time string or properly formatted time array
 * @param  array  additional HTML compliant <select> tag parameters
 * @return string three <select> tags populated with a hours, minutes and optionally seconds.
 * @see select datetime_tag, select_hour_tag, select_minute_tag, select_second_tag
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
 * Returns a variable number of <select> tags populated with date and time related select boxes.
 *
 * The select_datetime_tag is the culmination of both the select_date_tag and the select_time_tag.
 * By default, the <i>$value</i> parameter is set to the current date and time. To override this, simply pass a valid 
 * date, time, datetime string or correctly formatted array (see example) to the <i>$value</i> parameter. 
 * You can also set the <i>$value</i> parameter to null which will disable the <i>$value</i>, however this 
 * will only be useful if you pass 'include_blank' or 'include_custom' to the <i>$options</i> parameter. 
 * To include seconds to the result, use set the 'include_second' option in the <i>$options</i> parameter to true. 
 * <b>Note:</b> The <i>$name</i> parameter will automatically converted to array names. 
 * For example, a <i>$name</i> of "datetime" becomes:
 * <samp>
 *  <select name="datetime[month]">...</select>
 *  <select name="datetime[day]">...</select>
 *  <select name="datetime[year]">...</select>
 *  <select name="datetime[hour]">...</select>
 *  <select name="datetime[minute]">...</select>
 *  <select name="datetime[second]">...</select>
 * </samp>
 *  
 * <b>Options:</b>
 * - include_blank     - Includes a blank <option> tag at the beginning of the string with an empty value.
 * - include_custom    - Includes an <option> tag with a custom display title at the beginning of the string with an empty value.
 * - include_second    - If set to true, includes the "seconds" select tag as part of the result.
 * - discard_month     - If set to true, will only return select tags for day and year.
 * - discard_day       - If set to true, will only return select tags for month and year.
 * - discard_year      - If set to true, will only return select tags for month and day.
 * - use_month_numbers - If set to true, will show the month's numerical value (1 - 12) instead of the months full name.
 * - use_short_month   - If set to true, will show the month's short name (i.e. Jan, Feb, Mar) instead of its full name. 
 * - year_start        - If set, the range of years will begin at this four-digit date (i.e. 1979)
 * - year_end          - If set, the range of years will end at this four-digit date (i.e. 2025)
 * - second_step       - If set, the seconds will be incremented in blocks of X, where X = 'second_step'
 * - minute_step       - If set, the minutes will be incremented in blocks of X, where X = 'minute_step'
 * - 12hour_time       - If set to true, will return integers 1 through 12 instead of the default 0 through 23.
 * - date_seperator    - Includes a string of defined text between each generated select tag
 * - time_seperator    - Includes a string of defined text between each generated select tag
 * - ampm_seperator    - Includes a string of defined text between the minute/second select box and the AM/PM select box 
 *  
 * <b>Examples:</b>
 * <code>
 *  echo submit_datetime_tag('datetime');
 * </code>
 *
 * <code>
 *  echo select_datetime_tag('datetime', '1979-10-30');
 * </code>
 *
 * <code>
 *  $datetime = array('year' => '1979', 'month' => 10, 'day' => 30, 'hour' => '15', 'minute' => 46);
 *  echo select_datetime_tag('time', $datetime, array('use_short_month' => true, '12hour_time' => true));
 * </code>
 *
 * @param  string field name (automatically becomes an array of date and time parts)
 * @param  mixed  accepts a valid time string or properly formatted time array
 * @param  array  additional HTML compliant <select> tag parameters
 * @return string a variable number of <select> tags populated with date and time related select boxes
 * @see select date_tag, select_time_tag
 */
function select_datetime_tag($name, $value = null, $options = array(), $html_options = array())
{
  $options = _parse_attributes($options);
  $datetime_seperator = _get_option($options, 'datetime_seperator', '');

  $date = select_date_tag($name, $value, $options, $html_options);
  $time = select_time_tag($name, $value, $options, $html_options);

  return $date.$datetime_seperator.$time;
}

/**
 * Returns a <label> tag with <i>$label</i> for the specified <i>$id</i> parameter.
 *
 * @param  string id
 * @param  string label or title
 * @param  array  additional HTML compliant <label> tag parameters
 * @return string <label> tag with <i>$label</i> for the specified <i>$id</i> parameter.
 */
function label_for($id, $label, $options = array())
{
  $options = _parse_attributes($options);

  return content_tag('label', $label, array_merge(array('for' => get_id_from_name($id, null)), $options));
}

/**
 * Returns a formatted ID based on the <i>$name</i> parameter and optionally the <i>$value</i> parameter.
 *
 * This function determines the proper form field ID name based on the parameters. If a form field has an
 * array value as a name we need to convert them to proper and unique IDs like so:
 * <samp>
 *  name[] => name (if value == null)
 *  name[] => name_value (if value != null)
 *  name[bob] => name_bob
 *  name[item][total] => name_item_total
 * </samp>
 *
 * <b>Examples:</b>
 * <code>
 *  echo get_id_from_name('status[]', '1');
 * </code>
 *
 * @param  string field name 
 * @param  string field value
 * @return string <select> tag populated with all the languages in the world.
 */
function get_id_from_name($name, $value = null)
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

/**
 * Prepends zeros to the begging of <i>$string</i> until the string reaches <i>$strlen</i> length.
 *
 * @param  string string to check
 * @param  string required length of string
 * @return string formatted string with zeros at the beginning of the string until it reaches <i>$strlen</i> length
 */
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

/**
 * Retrieves the proper date format based on the specified <i>$culture</i> setting
 *
 * <b>Note:</b> If no <i>$culture</i> is defined, the user's culture setting will be used in its place.
 *
 * @param  string two or three character culture setting variable
 * @return string formatted date/time format based on the specified date/time setting
 * @see sfUser
 */
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

/**
 * Converts date values (<i>$value</i>) into its correct date format (<i>$format_char</i>)
 *
 * This function is primarily used in select_date_tag, select_time_tag and select_datetime_tag.
 *
 * <b>Note:</b> If <i>$value</i> is empty, it will be populated with the current date and time.
 *
 * @param  string date or date part
 * @param  string custom key for array values
 * @return string properly formatted date part value.
 * @see select_date_tag, select_time_tag, select_datetime_tag
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

/**
 * Converts specific <i>$options</i> to their correct HTML format
 *
 * @param  array options
 * @return array returns properly formatted options 
 */
function _convert_options($options)
{
  $options = _parse_attributes($options);

  foreach (array('disabled', 'readonly', 'multiple') as $attribute)
  {
    $options = _boolean_attribute($options, $attribute);
  }
  
  // Parse any javascript options
  $options = _convert_options_to_javascript($options);

  return $options;
}

/**
 * Removes an attribute if it is found in an array of <i>$options</i>.
 *
 * @param  array options
 * @return string filtered array of <i>$options</i>
 */
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
