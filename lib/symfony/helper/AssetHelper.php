<?php

// +---------------------------------------------------------------------------+
// | This file is part of the SymFony Framework project.                        |
// | Copyright (c) 2004, 2005 Fabien POTENCIER.                                          |
// +---------------------------------------------------------------------------+

/**
 *
 * @package   sf_runtime
 * @subpackage helper
 *
 * @author    Fabien POTENCIER (fabien.potencier@symfony-project)
 *  (c) Fabien POTENCIER
 * @since     1.0.0
 * @version   $Id: AssetHelper.php 495 2005-10-05 15:42:11Z fabien $
 */

  /**
      # Returns a link tag that browsers and news readers can use to auto-detect a RSS or ATOM feed for this page. The +type+ can
      # either be <tt>:rss</tt> (default) or <tt>:atom</tt> and the +options+ follow the url_for style of declaring a link target.
      #
      # Examples:
      #   auto_discovery_link_tag # =>
      #     <link rel="alternate" type="application/rss+xml" title="RSS" href="http://www.curenthost.com/controller/action" />
      #   auto_discovery_link_tag(:atom) # =>
      #     <link rel="alternate" type="application/atom+xml" title="ATOM" href="http://www.curenthost.com/controller/action" />
      #   auto_discovery_link_tag(:rss, :action => "feed") # =>
      #     <link rel="alternate" type="application/atom+xml" title="ATOM" href="http://www.curenthost.com/controller/feed" />
  */
  function auto_discovery_link_tag($type = 'rss', $options = array())
  {
    $options = _parse_attributes($options);
    $options['only_path'] = 'false';
    return tag('link', array('rel' => 'alternate', 'type' => 'application/'.$type.'+xml', 'title' => ucfirst($type), 'href' => url_for($options)));
  }

  /*
      # Returns path to a javascript asset. Example:
      #
      #   javascript_path "xmlhr" # => /js/xmlhr.js
  */
  function javascript_path($source)
  {
    return _compute_public_path($source, 'js', 'js');
  }

  /**
      # Returns a script include tag per source given as argument. Examples:
      #
      #   javascript_include_tag "xmlhr" # =>
      #     <script language="JavaScript" type="text/javascript" src="/js/xmlhr.js"></script>
      #
      #   javascript_include_tag "common.javascript", "/elsewhere/cools" # =>
      #     <script language="JavaScript" type="text/javascript" src="/js/common.javascript"></script>
      #     <script language="JavaScript" type="text/javascript" src="/elsewhere/cools.js"></script>
  */
  function javascript_include_tag()
  {
    $html = '';
    foreach (func_get_args() as $source)
    {
      $source = javascript_path($source);
      $html .= content_tag('script', '', array('language' => 'javascript', 'type' => 'text/javascript', 'src' => $source))."\n";
    }

    return $html;
  }

  /*
      # Returns path to a stylesheet asset. Example:
      #
      #   stylesheet_path "style" # => /css/style.css
  */
  function stylesheet_path($source)
  {
    return _compute_public_path($source, 'css', 'css');
  }

  /**
      # Returns a css link tag per source given as argument. Examples:
      #
      #   stylesheet_link_tag "style" # =>
      #     <link href="/css/style.css" media="screen" rel="Stylesheet" type="text/css" />
      #
      #   stylesheet_link_tag "random.styles", "/css/stylish" # =>
      #     <link href="/css/random.styles" media="screen" rel="Stylesheet" type="text/css" />
      #     <link href="/css/stylish.css" media="screen" rel="Stylesheet" type="text/css" />
  */
  function stylesheet_tag()
  {
    $html = '';
    foreach (func_get_args() as $source)
    {
      $source = stylesheet_path($source);
      $html .= tag('link', array('rel' => 'stylesheet', 'type' => 'text/css', 'media' => 'screen', 'href' => $source))."\n";
    }

    return $html;
  }

  /*
      # Returns path to an image asset. Example:
      #
      # The +src+ can be supplied as a...
      # * full path, like "/my_images/image.gif"
      # * file name, like "rss.gif", that gets expanded to "/images/rss.gif"
      # * file name without extension, like "logo", that gets expanded to "/images/logo.png"
  */
  function image_path($source)
  {
    return _compute_public_path($source, 'images', 'png');
  }

  /**
      # Returns an image tag converting the +options+ instead html options on the tag, but with these special cases:
      #
      # * <tt>:alt</tt>  - If no alt text is given, the file name part of the +src+ is used (capitalized and without the extension)
      # * <tt>:size</tt> - Supplied as "XxY", so "30x45" becomes width="30" and height="45"
      #
      # The +src+ can be supplied as a...
      # * full path, like "/my_images/image.gif"
      # * file name, like "rss.gif", that gets expanded to "/images/rss.gif"
      # * file name without extension, like "logo", that gets expanded to "/images/logo.png"
  */
  function image_tag($source, $options = array())
  {
    if (!$source)
    {
      return '';
    }

    $options = _parse_attributes($options);

    $options['src'] = image_path($source);

    if (!isset($options['alt']))
    {
      $path_pos = strrpos($source, '/');
      $dot_pos = strrpos($source, '.');
      $begin = $path_pos ? $path_pos + 1 : 0;
      $nb_str = ($dot_pos ? $dot_pos : strlen($source)) - $begin;
      $options['alt'] = ucfirst(substr($source, $begin, $nb_str));
    }

    if (isset($options['size']))
    {
      list($options['width'], $options['height']) = split('x', $options['size'], 2);
      unset($options['size']);
    }

    return tag('img', $options);
  }

  function _compute_public_path($source, $dir, $ext)
  {
    if (strpos($source, '/') === false) $source = '/'.$dir.'/'.$source;
    if (strpos($source, '.') === false) $source = $source.'.'.$ext;

    return SF_RELATIVE_URL_ROOT.$source;
  }

  function include_stylesheets()
  {
    $already_seen = array();
    foreach (sfContext::getInstance()->getRequest()->getAttributeHolder()->getAll('helper/asset/auto/stylesheet') as $files)
    {
      if (!is_array($files))
      {
        $files = array($files);
      }

      foreach ($files as $file)
      {
        if (isset($already_seen[$file])) continue;

        $already_seen[$file] = 1;
        echo stylesheet_tag($file);
      }
    }
  }

  function include_javascripts()
  {
    $already_seen = array();
    foreach (sfContext::getInstance()->getRequest()->getAttributeHolder()->getAll('helper/asset/auto/javascript') as $files)
    {
      if (!is_array($files))
      {
        $files = array($files);
      }

      foreach ($files as $file)
      {
        if (isset($already_seen[$file])) continue;

        $already_seen[$file] = 1;
        echo javascript_include_tag($file);
      }
    }
  }

  function include_metas()
  {
    foreach (sfContext::getInstance()->getRequest()->getAttributeHolder()->getAll('helper/asset/auto/meta') as $name => $content)
    {
      echo tag('meta', array('name' => $name, 'content' => $content))."\n";
    }
  }

  function include_http_metas()
  {
    foreach (sfContext::getInstance()->getRequest()->getAttributeHolder()->getAll('helper/asset/auto/httpmeta') as $httpequiv => $value)
    {
      echo tag('meta', array('http-equiv' => $httpequiv, 'content' => $value))."\n";
      header($httpequiv.': '.$value);
    }
  }

  function include_title()
  {
    $title = sfContext::getInstance()->getRequest()->getAttributeHolder()->get('title', '', 'helper/asset/auto/meta');
    echo content_tag('title', $title)."\n";
  }

?>