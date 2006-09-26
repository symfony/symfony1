<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebRequest class.
 *
 * This class manages web requests. It parses input from the request and store them as parameters.
 * sfWebRequest is able to parse request with routing support enabled.
 *
 * @package    symfony
 * @subpackage request
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfWebRequest extends sfRequest
{
  /**
   * A list of languages accepted by the browser.
   * @var array
   */
  protected $languages = null;

  /**
   * A list of charsets accepted by the browser
   * @var array
   */
  protected $charsets = null;

  protected $pathInfoArray = null;

  protected $relativeUrlRoot = null;

  /**
   * Retrieve an array of file information.
   *
   * @param string A file name
   *
   * @return array An associative array of file information, if the file exists, otherwise null.
   */
  public function getFile ($name)
  {
    return ($this->hasFile($name) ? $this->getFileValues($name) : null);
  }

  /**
   * Retrieve a file error.
   *
   * @param string A file name.
   *
   * @return int One of the following error codes:
   *
   *             - <b>UPLOAD_ERR_OK</b>        (no error)
   *             - <b>UPLOAD_ERR_INI_SIZE</b>  (the uploaded file exceeds the
   *                                           upload_max_filesize directive
   *                                           in php.ini)
   *             - <b>UPLOAD_ERR_FORM_SIZE</b> (the uploaded file exceeds the
   *                                           MAX_FILE_SIZE directive that
   *                                           was specified in the HTML form)
   *             - <b>UPLOAD_ERR_PARTIAL</b>   (the uploaded file was only
   *                                           partially uploaded)
   *             - <b>UPLOAD_ERR_NO_FILE</b>   (no file was uploaded)
   */
  public function getFileError ($name)
  {
    return ($this->hasFile($name) ? $this->getFileValue($name, 'error') : UPLOAD_ERR_NO_FILE);
  }

  /**
   * Retrieve a file name.
   *
   * @param string A file name.
   *
   * @return string A file name, if the file exists, otherwise null.
   */
  public function getFileName ($name)
  {
    return ($this->hasFile($name) ? $this->getFileValue($name, 'name') : null);
  }

  /**
   * Retrieve an array of file names.
   *
   * @return array An indexed array of file names.
   */
  public function getFileNames ()
  {
    return array_keys($_FILES);
  }

  /**
   * Retrieve an array of files.
   *
   * @return array An associative array of files.
   */
  public function getFiles ()
  {
    return $_FILES;
  }

  /**
   * Retrieve a file path.
   *
   * @param string A file name.
   *
   * @return string A file path, if the file exists, otherwise null.
   */
  public function getFilePath ($name)
  {
    return ($this->hasFile($name) ? $this->getFileValue($name, 'tmp_name') : null);
  }

  /**
   * Retrieve a file size.
   *
   * @param string A file name.
   *
   * @return int A file size, if the file exists, otherwise null.
   */
  public function getFileSize ($name)
  {
    return ($this->hasFile($name) ? $this->getFileValue($name, 'size') : null);
  }

  /**
   * Retrieve a file type.
   *
   * This may not be accurate. This is the mime-type sent by the browser
   * during the upload.
   *
   * @param string A file name.
   *
   * @return string A file type, if the file exists, otherwise null.
   */
  public function getFileType ($name)
  {
    return ($this->hasFile($name) ? $this->getFileValue($name, 'type') : null);
  }

  /**
   * Indicates whether or not a file exists.
   *
   * @param string A file name.
   *
   * @return bool true, if the file exists, otherwise false.
   */
  public function hasFile ($name)
  {
    if (preg_match('/^(.+?)\[(.+?)\]$/', $name, $match))
    {
      return isset($_FILES[$match[1]]['name'][$match[2]]);
    }
    else
    {
      return isset($_FILES[$name]);
    }
  }

  /**
   * Indicates whether or not a file error exists.
   *
   * @param string A file name.
   *
   * @return bool true, if the file error exists, otherwise false.
   */
  public function hasFileError ($name)
  {
    return ($this->hasFile($name) ? ($this->getFileValue($name, 'error') != UPLOAD_ERR_OK) : false);
  }

  /**
   * Indicates whether or not any file errors occured.
   *
   * @return bool true, if any file errors occured, otherwise false.
   */
  public function hasFileErrors ()
  {
    foreach ($this->getFileNames() as $name)
    {
      if ($this->hasFileError($name) === true)
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Indicates whether or not any files exist.
   *
   * @return bool true, if any files exist, otherwise false.
   */
  public function hasFiles ()
  {
    return (count($_FILES) > 0);
  }

  public function getFileValue ($name, $key)
  {
    if (preg_match('/^(.+?)\[(.+?)\]$/', $name, $match))
    {
      return $_FILES[$match[1]][$key][$match[2]];
    }
    else
    {
      return $_FILES[$name][$key];
    }
  }

  public function getFileValues ($name)
  {
    if (preg_match('/^(.+?)\[(.+?)\]$/', $name, $match))
    {
      return array(
        'name'     => $_FILES[$match[1]]['name'][$match[2]],
        'type'     => $_FILES[$match[1]]['type'][$match[2]],
        'tmp_name' => $_FILES[$match[1]]['tmp_name'][$match[2]],
        'error'    => $_FILES[$match[1]]['error'][$match[2]],
        'size'     => $_FILES[$match[1]]['size'][$match[2]],
      );
    }
    else
    {
      return $_FILES[$name];
    }
  }

  public function getFileExtension ($name)
  {
    $fileType = $this->getFileType($name);

    if (!$fileType)
    {
      return '.bin';
    }

    $mimeTypes = unserialize(file_get_contents(sfConfig::get('sf_symfony_data_dir').'/data/mime_types.dat'));

    return isset($mimeTypes[$fileType]) ? '.'.$mimeTypes[$fileType] : '.bin';
  }

  /**
   * Initialize this Request.
   *
   * @param Context A Context instance.
   * @param array   An associative array of initialization parameters.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this Request.
   */
  public function initialize ($context, $parameters = null)
  {
    parent::initialize ($context, $parameters);

    if (isset($_SERVER['REQUEST_METHOD']))
    {
      switch ($_SERVER['REQUEST_METHOD'])
      {
        case 'GET':
          $this->setMethod(self::GET);
          break;

        case 'POST':
          $this->setMethod(self::POST);
          break;

        default:
          $this->setMethod(self::GET);
      }
    }
    else
    {
      // set the default method
      $this->setMethod(self::GET);
    }

    // load parameters from GET/PATH_INFO/POST
    $this->loadParameters();
  }

  /**
   * Returns the array that contains all request information ($_SERVER or $_ENV).
   *
   * This information is stored in the [sf_path_info_array] constant.
   *
   * @return  array
   */
  protected function getPathInfoArray()
  {
    if (!$this->pathInfoArray)
    {
      // parse PATH_INFO
      switch (sfConfig::get('sf_path_info_array'))
      {
        case 'SERVER':
          $this->pathInfoArray =& $_SERVER;
          break;

        case 'ENV':
        default:
          $this->pathInfoArray =& $_ENV;
      }
    }

    return $this->pathInfoArray;
  }

  public function getUri()
  {
    $pathArray = $this->getPathInfoArray();

    if ($this->isAbsUri())
    {
      return $pathArray['REQUEST_URI'];
    }

    return $this->getUriPrefix().$pathArray['REQUEST_URI'];
  }

  /**
   * See if the client is using absolute uri
   *
   * @return boolean
   */
  public function isAbsUri()
  {
    $pathArray = $this->getPathInfoArray();

    return preg_match('/^http/', $pathArray['REQUEST_URI']);
  }

  /**
   * Uri prefix,including protocol,hostname and server port
   *
   * @return string
   */
  public function getUriPrefix()
  {
    $pathArray = $this->getPathInfoArray();
    if ($this->isSecure())
    {
      $standardPort = '443';
      $proto = 'https';
    }
    else
    {
      $standardPort = '80';
      $proto = 'http';
    }

    $port = $pathArray['SERVER_PORT'] == $standardPort || !$pathArray['SERVER_PORT'] ? '' : ':'.$pathArray['SERVER_PORT'];

    return $proto.'://'.$pathArray['HTTP_HOST'].$port;
  }

  public function getPathInfo ()
  {
    $pathInfo = '';

    $pathArray = $this->getPathInfoArray();

    // simulate PATH_INFO if needed
    $sf_path_info_key = sfConfig::get('sf_path_info_key');
    if (!isset($pathArray[$sf_path_info_key]) || !$pathArray[$sf_path_info_key])
    {
      if (isset($pathArray['REQUEST_URI']))
      {
        $script_name = $this->getScriptName();
        $uri_prefix = $this->isAbsUri() ? $this->getUriPrefix() : '';
        $pathInfo = preg_replace('/^'.preg_quote($uri_prefix, '/').'/','',$pathArray['REQUEST_URI']);
        $pathInfo = preg_replace('/^'.preg_quote($script_name, '/').'/', '', $pathInfo);
        $prefix_name = preg_replace('#/[^/]+$#', '', $script_name);
        $pathInfo = preg_replace('/^'.preg_quote($prefix_name, '/').'/', '', $pathInfo);
        $pathInfo = preg_replace('/'.preg_quote($pathArray['QUERY_STRING'], '/').'$/', '', $pathInfo);
      }
    }
    else
    {
      $pathInfo = $pathArray[$sf_path_info_key];
      if ($sf_relative_url_root = $this->getRelativeUrlRoot())
      {
        $pathInfo = preg_replace('/^'.str_replace('/', '\\/', $sf_relative_url_root).'\//', '', $pathInfo);
      }
    }

    // for IIS
    if (isset($_SERVER['SERVER_SOFTWARE']) && false !== stripos($_SERVER['SERVER_SOFTWARE'], 'iis') && $pos = stripos($pathInfo, '.php'))
    {
      $pathInfo = substr($pathInfo, $pos + 4);
    }

    if (!$pathInfo)
    {
      $pathInfo = '/';
    }

    return $pathInfo;
  }

  /**
   * Loads GET, PATH_INFO and POST data into the parameter list.
   *
   * @return void
   */
   protected function loadParameters ()
  {
    // merge GET parameters
    $this->getParameterHolder()->addByRef($_GET);

    $pathInfo = $this->getPathInfo();
    if ($pathInfo)
    {
      // routing map defined?
      $r = sfRouting::getInstance();
      if ($r->hasRoutes())
      {
        $results = $r->parse($pathInfo);
        if ($results !== null)
        {
          $this->getParameterHolder()->addByRef($results);
        }
        else
        {
          $this->setParameter('module', sfConfig::get('sf_error_404_module'));
          $this->setParameter('action', sfConfig::get('sf_error_404_action'));
        }
      }
      else
      {
        $array = explode('/', trim($pathInfo, '/'));
        $count = count($array);

        for ($i = 0; $i < $count; $i++)
        {
          // see if there's a value associated with this parameter,
          // if not we're done with path data
          if ($count > ($i + 1))
          {
            $this->getParameterHolder()->setByRef($array[$i], $array[++$i]);
          }
        }
      }
    }

    // merge POST parameters
    $this->getParameterHolder()->addByRef($_POST);

    // move symfony parameters in a protected namespace (parameters prefixed with _sf_)
    foreach ($this->getParameterHolder()->getAll() as $key => $value)
    {
      if (stripos($key, '_sf_') !== false)
      {
        $this->getParameterHolder()->remove($key);
        $this->setParameter($key, $value, 'symfony/request/sfWebRequest');
        unset($_GET[$key]);
      }
    }

    if (sfConfig::get('sf_logging_active'))
    {
      $this->getContext()->getLogger()->info(sprintf('{sfWebRequest} request parameters %s', str_replace("\n", '', var_export($this->getParameterHolder()->getAll(), true))));
    }
  }

  /**
   * Move an uploaded file.
   *
   * @param string A file name.
   * @param string An absolute filesystem path to where you would like the
   *               file moved. This includes the new filename as well, since
   *               uploaded files are stored with random names.
   * @param int    The octal mode to use for the new file.
   * @param bool   Indicates that we should make the directory before moving the file.
   * @param int    The octal mode to use when creating the directory.
   *
   * @return bool true, if the file was moved, otherwise false.
   *
   * @throws sfFileException If a major error occurs while attempting to move the file.
   */
  public function moveFile ($name, $file, $fileMode = 0666, $create = true, $dirMode = 0777)
  {
    if ($this->hasFile($name) && $this->getFileValue($name, 'error') == UPLOAD_ERR_OK && $this->getFileValue($name, 'size') > 0)
    {
      // get our directory path from the destination filename
      $directory = dirname($file);

      if (!is_readable($directory))
      {
        $fmode = 0777;

        if ($create && !@mkdir($directory, $dirMode, true))
        {
          // failed to create the directory
          $error = 'Failed to create file upload directory "%s"';
          $error = sprintf($error, $directory);

          throw new sfFileException($error);
        }

        // chmod the directory since it doesn't seem to work on
        // recursive paths
        @chmod($directory, $dirMode);
      }
      else if (!is_dir($directory))
      {
        // the directory path exists but it's not a directory
        $error = 'File upload path "%s" exists, but is not a directory';
        $error = sprintf($error, $directory);

        throw new sfFileException($error);
      }
      else if (!is_writable($directory))
      {
        // the directory isn't writable
        $error = 'File upload path "%s" is not writable';
        $error = sprintf($error, $directory);

        throw new sfFileException($error);
      }

      if (@move_uploaded_file($this->getFileValue($name, 'tmp_name'), $file))
      {
        // chmod our file
        @chmod($file, $fileMode);

        return true;
      }
    }

    return false;
  }

  /**
   * Returns referer.
   *
   * @return  string
   */
  public function getReferer()
  {
    $pathArray = $this->getPathInfoArray();

    return isset($pathArray['HTTP_REFERER']) ? $pathArray['HTTP_REFERER'] : '';
  }

  /**
   * Returns current host name.
   *
   * @return  string
   */
  public function getHost()
  {
    $pathArray = $this->getPathInfoArray();

    return isset($pathArray['HTTP_X_FORWARDED_HOST']) ? $pathArray['HTTP_X_FORWARDED_HOST'] : (isset($pathArray['HTTP_HOST']) ? $pathArray['HTTP_HOST'] : '');
  }

  /**
   * Returns current script name.
   *
   * @return  string
   */
  public function getScriptName()
  {
    $pathArray = $this->getPathInfoArray();

    return isset($pathArray['SCRIPT_NAME']) ? $pathArray['SCRIPT_NAME'] : (isset($pathArray['ORIG_SCRIPT_NAME']) ? $pathArray['ORIG_SCRIPT_NAME'] : '');
  }

  /**
   * Returns request method.
   *
   * @return  string
   */
  public function getRequestMethod()
  {
    $pathArray = $this->getPathInfoArray();

    return isset($pathArray['REQUEST_METHOD']) ? $pathArray['REQUEST_METHOD'] : 'GET';
  }

  /**
   * Get a list of languages acceptable by the client browser
   *
   * @return array languages ordered in the user browser preferences.
   */
  public function getLanguages()
  {
    if ($this->languages)
    {
      return $this->languages;
    }

    $this->languages = array();

    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
    {
      return $this->languages;
    }

    foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $lang)
    {
      // Cut off any q-value that might come after a semi-colon
      if ($pos = strpos($lang, ';'))
      {
        $lang = trim(substr($lang, 0, $pos));
      }

      if (strstr($lang, '-'))
      {
        $codes = explode('-', $lang);
        if ($codes[0] == 'i')
        {
          // Language not listed in ISO 639 that are not variants
          // of any listed language, which can be registerd with the
          // i-prefix, such as i-cherokee
          if (count($codes) > 1)
          {
            $lang = $codes[1];
          }
        }
        else
        {
          for ($i = 0, $max = count($codes); $i < $max; $i++)
          {
            if ($i == 0)
            {
              $lang = strtolower($codes[0]);
            }
            else
            {
              $lang .= '_'.strtoupper($codes[$i]);
            }
          }
        }
      }

//      if(CultureInfo::validCulture($lang))
      $this->languages[] = $lang;
    }

    return $this->languages;
  }

  /**
   * Get a list of charsets acceptable by the client browser.
   *
   * @return array list of charsets in preferable order.
   */
  public function getCharsets()
  {
    if ($this->charsets)
      return $this->charsets;

    $this->charsets = array();

    if (!isset($_SERVER['HTTP_ACCEPT_CHARSET']))
    {
      return $this->charsets;
    }

    $this->charsets = preg_replace('/;.*/', '', explode(',', $_SERVER['HTTP_ACCEPT_CHARSET']));

    return $this->charsets;
  }

  /**
   * Return true id the request is a XMLHttpRequest (via prototype 'HTTP_X_REQUESTED_WITH' header).
   *
   * @return boolean
   */
  public function isXmlHttpRequest ()
  {
    return ($this->getHttpHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
  }

  public function getHttpHeader ($name, $prefix = 'http')
  {
    $name = strtoupper($prefix).'_'.strtoupper(strtr($name, '-', '_'));

    $pathArray = $this->getPathInfoArray();

    return isset($pathArray[$name]) ? stripslashes($pathArray[$name]) : null;
  }

  /**
   * Get cookie value.
   *
   * @return mixed
   */
  public function getCookie ($name, $defaultValue = null)
  {
    $retval = $defaultValue;

    if (isset($_COOKIE[$name]))
    {
      $retval = $_COOKIE[$name];
    }

    return $retval;
  }

  /**
   * Returns true if the current request is secure (HTTPS protocol).
   *
   * @return boolean
   */
  public function isSecure()
  {
    $pathArray = $this->getPathInfoArray();

    return (
      (isset($pathArray['HTTPS']) && strtolower($pathArray['HTTPS']) == 'on')
      ||
      (isset($pathArray['HTTP_X_FORWARDED_PROTO']) && strtolower($pathArray['HTTP_X_FORWARDED_PROTO']) == 'https')
    );
  }

  public function getRelativeUrlRoot()
  {
    if ($this->relativeUrlRoot === null)
    {
      $this->relativeUrlRoot = sfConfig::get('sf_relative_url_root', preg_replace('#/[^/]+\.php5?$#', '', $this->getScriptName()));
    }

    return $this->relativeUrlRoot;
  }

  public function setRelativeUrlRoot($value)
  {
    $this->relativeUrlRoot = $value;
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
