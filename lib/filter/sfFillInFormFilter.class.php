<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfFillInFormFilter extends sfFilter
{
  private $escapers = array();

  public function executeBeforeRendering($filterChain)
  {
    $context  = $this->getContext();
    $response = $context->getResponse();
    $request  = $context->getRequest();

    $doc = new DomDocument('1.0', 'UTF-8');
    @$doc->loadHTML($response->getContent());
    $xpath = new DomXPath($doc);

    if (!$this->getParameter('name'))
    {
      throw new sfInitializationException('You must give the name of the form to populate.');
    }

    // load converters
    foreach ($this->getParameter('converters') as $functionName => $parameters)
    {
      if (!is_array($parameters))
      {
        $parameters = array($parameters);
      }

      foreach ($parameters as $parameter)
      {
        $this->escapers[$parameter][] = $functionName;
      }
    }

    // find our form
    if ($form = $xpath->query('//form[@name="'.$this->getParameter('name').'"]')->item(0))
    {
      // input
      foreach ($xpath->query('//descendant::input[@name and @type="text"]', $form) as $element)
      {
        $name = $element->getAttribute('name');
        if (null === $request->getParameter($name))
        {
          continue;
        }
        $element->setAttribute('value', $this->espaceRequestParameter($request, $name));
      }

      // checkbox
      foreach ($xpath->query('//descendant::input[@name and @type="checkbox"]', $form) as $element)
      {
        $name = $element->getAttribute('name');
        $selected = $element->hasAttribute('value') ? ($request->getParameter($name) == $element->getAttribute('value')) : $request->getParameter($name);
        if ($selected)
        {
          $element->setAttribute('checked', 'checked');
        }
        else
        {
          $element->removeAttribute('checked');
        }
      }

      // radio
      foreach ($xpath->query('//descendant::input[@name and @type="radio"]', $form) as $element)
      {
        $name = $element->getAttribute('name');
        if ($request->getParameter($name) == $element->getAttribute('value'))
        {
          $element->setAttribute('checked', 'checked');
        }
        else
        {
          $element->removeAttribute('checked');
        }
      }

      // textarea
      foreach ($xpath->query('//descendant::textarea[@name]', $form) as $element)
      {
        $name = $element->getAttribute('name');
        if ($request->getParameter($name) === null)
        {
          continue;
        }

        foreach ($element->childNodes as $child_node)
        {
          $element->removeChild($child_node);
        }

        $element->appendChild($doc->createTextNode($this->espaceRequestParameter($request, $name)));
      }

      // select
      foreach ($xpath->query('//descendant::select[@name]', $form) as $element)
      {
        $name = $element->getAttribute('name');
        $mutiple = false;
        if (substr($name, -2) == '[]')
        {
          // multiple select
          $element->setAttribute('multiple', 'multiple');
          $mutiple = true;
          $name = substr($name, 0, -2);
        }
        foreach ($xpath->query('descendant::option', $element) as $option)
        {
          $option->removeAttribute('selected');
          if ($mutiple && is_array($request->getParameter($name)))
          {
            if (in_array($option->getAttribute('value'), $request->getParameter($name)))
            {
              $option->setAttribute('selected', 'selected');
            }
          }
          else
          {
            if ($request->getParameter($name) == $option->getAttribute('value'))
            {
              $option->setAttribute('selected', 'selected');
            }
          }
        }
      }
    }

    $response->setContent($doc->saveHTML());

    unset($doc);

    // execute next filter
    $filterChain->execute();
  }

  private function espaceRequestParameter($request, $name)
  {
    $value = $request->getParameter($name);
    if (isset($this->escapers[$name]))
    {
      foreach ($this->escapers[$name] as $function)
      {
        $value = $function($value);
      }
    }

    return $value;
  }
}

?>