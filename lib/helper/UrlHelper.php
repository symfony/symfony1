<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * UrlHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

function url_for($url, $absolute = false)
{
  static $controller;

  if (!isset($controller))
  {
    $controller = sfContext::getInstance()->getController();
  }

  return $controller->genUrl(null, $url, $absolute);
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
function link_to($name = '', $options = '', $html_options = array())
{
  $html_options = _parse_attributes($html_options);

  $html_options = _convert_options_to_javascript($html_options);

  $absolute = false;
  if (isset($html_options['absolute_url']))
  {
    unset($html_options['absolute_url']);
    $absolute = true;
  }

  $html_options['href'] = url_for($options, $absolute);

  if (isset($html_options['query_string']))
  {
    $html_options['href'] .= '?'.$html_options['query_string'];
    unset($html_options['query_string']);
  }

  if (is_object($name))
  {
    $name = $name->__toString();
  }

  if (!strlen($name))
  {
    $name = $html_options['href'];
  }

  return content_tag('a', $name, $html_options);
}

function link_to_if($condition, $name = '', $options = '', $html_options = array(), $parameters_for_method_reference = array())
{
  if ($condition)
  {
    return link_to($name, $options, $html_options, $parameters_for_method_reference);
  }
  else
  {
    if (isset($html_options['tag']))
    {
      $tag = $html_options['tag'];
      unset($html_options['tag']);
    }
    else
    {
      $tag = 'span';
    }

    return content_tag($tag, $name, $html_options);
  }
}

function link_to_unless($condition, $name = '', $options = '', $html_options = array(), $parameters_for_method_reference = array())
{
  return link_to_if(!$condition, $name, $options, $html_options, $parameters_for_method_reference);
}

function button_to($name, $target, $options = array())
{
  $html_options = _convert_options($options);
  $html_options['value']   = $name;
 
  if (isset($html_options['post']) && $html_options['post'])
  {
    if (isset($html_options['popup']))
    {
      throw new sfConfigurationException('You can\'t use "popup" and "post" together');
    }
    $html_options['type'] = 'submit';
    unset($html_options['post']);
    $html_options = _convert_options_to_javascript($html_options);
 
    return form_tag($target, array('method' => 'post', 'class' => 'button_to')).tag('input', $html_options).'</form>';
  }
  else if (isset($html_options['popup']))
  {
    $html_options['type']    = 'button';
    $html_options = _convert_options_to_javascript($html_options, $target);
 
    return tag('input', $html_options);
  }
  else
  {
    $html_options['type']    = 'button';
    $html_options['onclick'] = "document.location.href='".url_for($target)."';";
    $html_options = _convert_options_to_javascript($html_options);
 
    return tag('input', $html_options);
  }
}

function mail_to($email, $name = '', $html_options = array())
{
  $html_options = _parse_attributes($html_options);

  $html_options = _convert_options_to_javascript($html_options);

  if (!$name)
  {
    $name = $email;
  }

  if (isset($html_options['encode']) && $html_options['encode'])
  {
    unset($html_options['encode']);
    $html_options['href'] = _encodeText('mailto:'.$email);
    $name = _encodeText($name);
  }
  else
  {
    $html_options['href'] = 'mailto:'.$email;
  }

  return content_tag('a', $name, $html_options);
}

function _convert_options_to_javascript($html_options, $target = '')
{
  // confirm
  $confirm = isset($html_options['confirm']) ? $html_options['confirm'] : '';
  unset($html_options['confirm']);

  // popup
  $popup = isset($html_options['popup']) ? $html_options['popup'] : '';
  unset($html_options['popup']);

  // post
  $post = isset($html_options['post']) ? $html_options['post'] : '';
  unset($html_options['post']);

  $onclick = isset($html_options['onclick']) ? $html_options['onclick'] : '';

  if ($popup && $post)
  {
    throw new sfConfigurationException('You can\'t use "popup" and "post" in the same link');
  }
  else if ($confirm && $popup)
  {
    $html_options['onclick'] = $onclick.'if ('._confirm_javascript_function($confirm).') { '._popup_javascript_function($popup, $target).' };return false;';
  }
  else if ($confirm && $post)
  {
    $html_options['onclick'] = $onclick.'if ('._confirm_javascript_function($confirm).') { '._post_javascript_function().' };return false;';
  }
  else if ($confirm)
  {
    if ($onclick)
    {
      $html_options['onclick'] = 'if ('._confirm_javascript_function($confirm).') {'.$onclick.'}';
    }
    else
    {
      $html_options['onclick'] = 'return '._confirm_javascript_function($confirm).';';
    }
  }
  else if ($post)
  {
    $html_options['onclick'] = $onclick._post_javascript_function().'return false;';
  }
  else if ($popup)
  {
    $html_options['onclick'] = $onclick._popup_javascript_function($popup, $target).'return false;';
  }

  return $html_options;
}

function _confirm_javascript_function($confirm)
{
  return "confirm('".escape_javascript($confirm)."')";
}

function _popup_javascript_function($popup, $target = '')
{
  $url = $target == '' ? 'this.href' : "'".url_for($target)."'";

  if (is_array($popup))
  {
    if (isset($popup[1]))
    {
      return "window.open(".$url.",'".$popup[0]."','".$popup[1]."');";
    }
    else
    {
      return "window.open(".$url.",'".$popup[0]."');";
    }
  }
  else
  {
    return "window.open(".$url.");";
  }
}

function _post_javascript_function()
{
  return "f = document.createElement('form'); document.body.appendChild(f); f.method = 'POST'; f.action = this.href; f.submit();";
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