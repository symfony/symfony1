<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebResponse class.
 *
 * This class manages web reponses. It supports cookies and headers management.
 * 
 * @package    symfony
 * @subpackage response
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWebResponse extends sfResponse
{
  private
    $cookies = array(),
    $headers = array(),
    $status  = 'HTTP/1.0 200 OK';

  /**
   * Set a cookie.
   *
   * @param string HTTP header name
   * @param string value
   *
   * @return void
   */
  public function setCookie ($name, $value, $expire = '', $path = '', $domain = '', $secure = 0)
  {
    $this->cookies[] = array(
      'name'   => $name,
      'value'  => $value,
      'expire' => $expire,
      'path'   => $path,
      'domain' => $domain,
      'secure' => $secure,
    );
  }

  /**
   * Set response status code.
   *
   * @param string HTTP status code
   * @param string
   *
   * @return void
   */
  public function setStatus ($code, $name = 'OK')
  {
    $this->status = 'HTTP/1.0 '.$code.' '.$name;
  }

  /**
   * Set a HTTP header.
   *
   * @param string HTTP header name
   * @param string value
   *
   * @return void
   */
  public function setHeader ($name, $value, $replace = true)
  {
    $name = $this->normalizeHeaderName($name);

    if (!isset($this->headers[$name]) || $replace)
    {
      $this->headers[$name] = array();
    }

    $this->headers[$name][] = $value;
  }

  /**
   * Get HTTP header current value.
   *
   * @return array
   */
  public function getHeader ($name, $defaultValue = null)
  {
    $retval = $defaultValue;

    if (isset($this->headers[$this->normalizeHeaderName($name)]))
    {
      $retval = $this->headers[$this->normalizeHeaderName($name)];
    }

    return $retval;
  }

  /**
   * Has a HTTP header.
   *
   * @return boolean
   */
  public function hasHeader ($name)
  {
    return isset($this->headers[$this->normalizeHeaderName($name)]);
  }

  /**
   * Send HTTP headers and cookies.
   *
   * @return void
   */
  public function sendHeaders ()
  {
    // status
    header($this->status);

    if (sfConfig::get('sf_logging_active'))
    {
      $this->getContext()->getLogger()->info('{sfResponse} send status "'.$this->status.'"');
    }

    // set headers from HTTP meta
    foreach ($this->getContext()->getRequest()->getAttributeHolder()->getAll('helper/asset/auto/httpmeta') as $name => $value)
    {
      $this->setHeader($name, $value);
    }

    // headers
    foreach ($this->headers as $name => $values)
    {
      foreach ($values as $value)
      {
        header($name.': '.$value);

        if (sfConfig::get('sf_logging_active'))
        {
          $this->getContext()->getLogger()->info('{sfResponse} send header "'.$name.'": "'.$value.'"');
        }
      }
    }

    // cookies
    foreach ($this->cookies as $cookie)
    {
      setrawcookie($cookie['name'], $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure']);

      if (sfConfig::get('sf_logging_active'))
      {
        $this->getContext()->getLogger()->info('{sfResponse} send cookie "'.$cookie['name'].'": "'.$cookie['value'].'"');
      }
    }
  }

  private function normalizeHeaderName($name)
  {
    return preg_replace('/\-(.)/e', "'-'.strtoupper('\\1')", strtr(ucfirst(strtolower($name)), '_', '-'));
  }

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   */
  public function shutdown ()
  {
  }
}

?>