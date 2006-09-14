<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDomCssSelector allows to navigate a DOM with CSS selector.
 *
 * based on getElementsBySelector version 0.4 - Simon Willison, March 25th 2003
 * http://simon.incutio.com/archive/2003/03/25/getElementsBySelector
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfDomCssSelector
{
  protected $dom = null;

  public function __construct($dom)
  {
    $this->dom = $dom;
  }

  public function getTexts($selector)
  {
    $texts = array();
    foreach ($this->getElements($selector) as $element)
    {
      $texts[] = $element->nodeValue;
    }

    return $texts;
  }

  public function getElements($selector)
  {
    $tokens = explode(' ', $selector);
    $nodes = array($this->dom);
    foreach ($tokens as $token)
    {
      $token = trim($token);
      if (false !== strpos($token, '#'))
      {
        // Token is an ID selector
        $bits = explode('#', $token);
        $tagName = $bits[0];
        $id = $bits[1];
        $xpath = new DomXPath($this->dom);
        $element = $xpath->query(sprintf("//*[@id = '%s']", $id))->item(0);
        if (!$element || ($tagName && strtolower($element->nodeName) != $tagName))
        {
          // tag with that ID not found
          return array();
        }

        // Set nodes to contain just this element
        $nodes = array($element);

        continue; // Skip to next token
      }

      if (false !== strpos($token, '.'))
      {
        // Token contains a class selector
        $bits = explode('.', $token);
        $tagName = $bits[0] ? $bits[0] : '*';
        $className = $bits[1];

        // Get elements matching tag, filter them for class selector
        $founds = $this->getElementsByTagName($nodes, $tagName);
        $nodes = array();
        foreach ($founds as $found)
        {
          if (preg_match('/\b'.$className.'\b/', $found->getAttribute('class')))
          {
            $nodes[] = $found;
          }
        }

        continue; // Skip to next token
      }

      // Code to deal with attribute selectors
      if (preg_match('/^(\w*)\[(\w+)([=~\|\^\$\*]?)=?"?([^\]"]*)"?\]$/', $token, $match))
      {
        $tagName = $match[1] ? $match[1] : '*';
        $attrName = $match[2];
        $attrOperator = $match[3];
        $attrValue = $match[4];

        // Grab all of the tagName elements within current node
        $founds = $this->getElementsByTagName($nodes, $tagName);
        $nodes = array();
        foreach ($founds as $found)
        {
          $ok = false;
          switch ($attrOperator)
          {
            case '=': // Equality
              $ok = $found->getAttribute($attrName) == $attrValue;
              break;
            case '~': // Match one of space seperated words
              $ok = preg_match('/\b'.$attrValue.'\b/', $found->getAttribute($attrName));
              break;
            case '|': // Match start with value followed by optional hyphen
              $ok = preg_match('/^'.$attrValue.'-?/', $found->getAttribute($attrName));
              break;
            case '^': // Match starts with value
              $ok = 0 === strpos($found->getAttribute($attrName), $attrValue);
              break;
            case '$': // Match ends with value
              $ok = $attrValue == substr($found->getAttribute($attrName), -strlen($attrValue));
              break;
            case '*': // Match ends with value
              $ok = false !== strpos($found->getAttribute($attrName), $attrValue);
              break;
            default :
              // Just test for existence of attribute
              $ok = $found->hasAttribute($attrName);
          }

          if ($ok)
          {
            $nodes[] = $found;
          }
        }

        continue; // Skip to next token
      }

      // If we get here, token is JUST an element (not a class or ID selector)
      $nodes = $this->getElementsByTagName($nodes, $token);
    }

    return $nodes;
  }

  protected function getElementsByTagName($nodes, $tagName)
  {
    $founds = array();
    foreach ($nodes as $node)
    {
      foreach ($node->getElementsByTagName($tagName) as $element)
      {
        $founds[] = $element;
      }
    }

    return $founds;
  }
}
