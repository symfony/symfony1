<?php

/**
 * sfHTTPNegotiator class file.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the BSD License.
 *
 * Copyright(c) 2004 by Qiang Xue. All rights reserved.
 *
 * To contact the author write to {@link mailto:qiang.xue@gmail.com Qiang Xue}
 * The latest version of PRADO can be obtained from:
 * {@link http://prado.sourceforge.net/}
 *
 * @author     Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @version    $Id$
 * @package    symfony
 * @subpackage i18n
 */

/**
 * sfHTTPNegotiator class.
 * 
 * Get the language and charset information from the client browser.
 *
 * @author Xiang Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @version v1.0, last update on Fri Dec 24 16:01:35 EST 2004
 * @package System.I18N.core
 */
class sfHTTPNegotiator
{
  /**
   * A list of languages accepted by the browser.
   * @var array 
   */
  protected $languages;

  /**
   * A list of charsets accepted by the browser
   * @var array 
   */
  protected $charsets;

  /**
   * Get a list of languages acceptable by the client browser
   * @return array languages ordered in the user browser preferences. 
   */
  function getLanguages()
  {
    if(!is_null($this->languages))
      return $this->languages;

    $this->languages = array();

    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
            return $this->languages;

    //$basedir = sfCultureInfo::dataDir();
    //$ext = sfCultureInfo::fileExt();

    foreach(explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $lang) 
    {
            // Cut off any q-value that might come after a semi-colon
            if ($pos = strpos($lang, ';'))
                $lang = trim(substr($lang, 0, $pos));

      if (strstr($lang, '-')) 
      {
        $codes = explode('-',$lang);
        if($codes[0] == 'i')
        {
                    // Language not listed in ISO 639 that are not variants
                    // of any listed language, which can be registerd with the
                    // i-prefix, such as i-cherokee
          if(count($codes)>1)
            $lang = $codes[1];
        }
        else
        {
          for($i = 0; $i<count($codes); $i++)
          {
            if($i == 0)
              $lang = strtolower($codes[0]);
            else
              $lang .= '_'.strtoupper($codes[$i]);
          }
        }
            }

      if(sfCultureInfo::validCulture($lang))
        $this->languages[] = $lang;
        }
    
    return $this->languages;
  }

  /**
   * Get a list of charsets acceptable by the client browser.
   * @return array list of charsets in preferable order. 
   */
  function getCharsets()
  {
        if(!is_null($this->charsets))
      return $this->charsets;

    $this->charsets = array();

    if (!isset($_SERVER['HTTP_ACCEPT_CHARSET']))
            return $this->charsets;

    foreach (explode(',', $_SERVER['HTTP_ACCEPT_CHARSET']) as $charset) 
    {
            if (!empty($charset)) 
                $this->charsets[] = preg_replace('/;.*/', '', $charset);
        }

    return $this->charsets;
  }
}
