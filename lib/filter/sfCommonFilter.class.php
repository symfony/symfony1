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
class sfCommonFilter extends sfFilter
{
  /**
   * Execute this filter.
   *
   * @param FilterChain A FilterChain instance.
   *
   * @return void
   */
  public function execute ($filterChain)
  {
    // execute next filter
    $filterChain->execute();

    // execute this filter only once
    $response = $this->getContext()->getResponse();

    // include javascripts and stylesheets
    sfLoader::loadHelpers(array('Tag', 'Asset'));
    $html  = $this->includeJavascripts($response);
    $html .= $this->includeStylesheets($response);
    $content = $response->getContent();
    if (false !== ($pos = strpos($content, '</head>')))
    {
      $content = substr($content, 0, $pos).$html.substr($content, $pos);
    }

    $response->setContent($content);
  }

  protected function includeJavascripts($response)
  {
    $already_seen = array();
    $html = '';

    foreach (array('first', '', 'last') as $position)
    {
      foreach ($response->getJavascripts($position) as $files)
      {
        if (!is_array($files))
        {
          $files = array($files);
        }

        foreach ($files as $file)
        {
          $file = javascript_path($file);

          if (isset($already_seen[$file])) continue;

          $already_seen[$file] = 1;
          $html .= javascript_include_tag($file);
        }
      }
    }

    return $html;
  }

  protected function includeStylesheets($response)
  {
    $already_seen = array();
    $html = '';

    foreach (array('first', '', 'last') as $position)
    {
      foreach ($response->getStylesheets($position) as $files => $options)
      {
        if (!is_array($files))
        {
          $files = array($files);
        }

        foreach ($files as $file)
        {
          $file = stylesheet_path($file);

          if (isset($already_seen[$file])) continue;

          $already_seen[$file] = 1;
          $html .= stylesheet_tag($file, $options);
        }
      }
    }

    return $html;
  }
}
