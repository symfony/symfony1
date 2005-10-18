<?php

/* PHP port of tag helpers from Rails

url_helper.rb
  url_for           OK
  link_to           
  link_image_to       DEPRECATED
  link_to_unless_current    
  link_to_unless        
  link_to_if          
  mail_to           
  current_page        

*/

// +---------------------------------------------------------------------------+
// | This file is part of the SymFony Framework project.                        |
// | Copyright (c) 2004, 2005 Fabien POTENCIER.                                          |
// +---------------------------------------------------------------------------+

/**
 *
 * @package   sf_runtime
 * @subpackage helper
 *
 * @author    Fabien POTENCIER (fabien.potencier@gmail.com)
 *  (c) Fabien POTENCIER
 * @since     1.0.0
 * @version   $Id: UrlHelper.php 531 2005-10-18 09:43:40Z fabien $
 */

function url_for($url)
{
  return sfContext::getInstance()->getController()->genUrl(null, $url);
}
 
/*
    # Creates a link tag of the given +name+ using an URL created by the set of +options+. See the valid options in
    # link:classes/ActionController/Base.html#M000021. It's also possible to pass a string instead of an options hash to
    # get a link tag that just points without consideration. If nil is passed as a name, the link itself will become the name.
    # The html_options have a special feature for creating javascript confirm alerts where if you pass :confirm => 'Are you sure?',
    # the link will be guarded with a JS popup asking that question. If the user accepts, the link is processed, otherwise not.
    #
    # Example:
    #   link_to "Delete this page", { :action => "destroy", :id => @page.id }, :confirm => "Are you sure?"
*/
function link_to($name = '', $options = '', $html_options = array(), $parameters_for_method_reference = array())
{
  $html_options = _parse_attributes($html_options);

  $html_options = _convert_confirm_option_to_javascript($html_options);
  if (!is_array($options) && preg_match('/^http/', $options))
  {
    $html_options['href'] = $options;
    if (!strlen($name)) $name = $html_options['href'];
    return content_tag('a', $name, $html_options);
  }
  else
  {
    $html_options['href'] = url_for($options, $parameters_for_method_reference);
    if (!strlen($name)) $name = $html_options['href'];
    return content_tag('a', $name, $html_options);
  }
}

function link_to_if($condition, $name = '', $options = '', $html_options = array(), $parameters_for_method_reference = array())
{
  if ($condition)
  {
    return link_to($name, $options, $html_options, $parameters_for_method_reference);
  }
}

function link_to_unless($condition, $name = '', $options = '', $html_options = array(), $parameters_for_method_reference = array())
{
  return link_to_if(!$condition, $name, $options, $html_options, $parameters_for_method_reference);
}

function _convert_confirm_option_to_javascript($html_options)
{
  if (isset($html_options['confirm']))
  {
    $confirm = preg_replace("/'/", "\\'", $html_options['confirm']);
    unset($html_options['confirm']);
    $html_options['onclick'] = "return confirm('$confirm');";
  }

  return $html_options;
}

function mail_to($email, $encode = true)
{
  if ($encode)
  {
    $email = _encodeText($email);
    return content_tag('a', $email, array('href' => _encodeText('mailto:').$email));
  }
  else
  {
    return content_tag('a', $email, array('href' => 'mailto:'.$email));
  }
}

function _encodeText($text)
{
  $encoded_text = '';

  for ($i = 0; $i < strlen($text); $i++)
  {
    $char = $text{$i};
    $r = rand(0, 100);

    # roughly 10% raw, 45% hex, 45% dec
    # '@' *must* be encoded. I insist.
    if ($r > 90 && $char != '@')
    {
      $encoded_text .= $char;
    }
    else if ($r < 45)
    {
      $encoded_text .= '&#x'.dechex(ord($char)).';';
    }
    else
    {
      $encoded_text .= '&#'.ord($char).';';
    }
  }

  return $encoded_text;
}

?>