<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * TagHelper defines some base helpers to construct html tags.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

/**
 * Constructs an html tag.
 *
 * @param  $name    string  tag name
 * @param  $options array   tag options
 * @param  $open    boolean true to leave tag open
 * @return string
 */
function tag($name, $options = array(), $open = false)
{
  if (!$name)
  {
    return '';
  }

  return '<'.$name._tag_options($options).(($open) ? '>' : ' />');
}

function content_tag($name, $content = '', $options = array())
{
  if (!$name)
  {
    return '';
  }

  return '<'.$name._tag_options($options).'>'.$content.'</'.$name.'>';
}

function cdata_section($content)
{
  return "<![CDATA[$content]]>";
}

/**
  # Escape carrier returns and single and double quotes for Javascript segments.
*/
function escape_javascript($javascript = '')
{
  $javascript = preg_replace('/\r\n|\n|\r/', "\\n", $javascript);
  $javascript = preg_replace('/(["\'])/', '\\\\1', $javascript);

  return $javascript;
}

function _tag_options($options = array())
{
  $options = _parse_attributes($options);

  $html = '';
//FIXME : addslashes / htmlspecialchars pour $value
  foreach ($options as $key => $value)
  {
    $html .= ' '.$key.'="'.$value.'"';
  }

  return $html;
}

function _parse_attributes($string)
{
  if (is_array($string))
  {
    return $string;
  }

  preg_match_all('/
    \s*(\w+)              # key                               \\1
    \s*=\s*               # =
    (\'|")?               # values may be included in \' or " \\2
    (.*?)                 # value                             \\3
    (?(2) \\2)            # matching \' or " if needed        \\4
    \s*(?:
      (?=\w+\s*=) | \s*$  # followed by another key= or the end of the string
    )
  /x', $string, $matches, PREG_SET_ORDER);

  $attributes = array();
  foreach ($matches as $val)
  {
    $attributes[$val[1]] = $val[3];
  }

  return $attributes;
}

?>
