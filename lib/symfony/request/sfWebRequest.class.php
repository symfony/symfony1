<?php

/**
 * @package    symfony
 * @subpackage request
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @copyright  2004-2005 Fabien Potencier <fabien.potencier@gmail.com>
 * @license    SymFony License 1.0
 * @version    SVN: $Id: sfWebRequest.class.php 480 2005-09-21 13:33:58Z fabien $
 */
 
/**
 *
 * Request class.
 *
 * This class manages web requests. It parses input from the request and store them as parameters.
 * sfRequest is able to parse request with routing support enabled.
 *
 * @package    symfony
 * @subpackage request
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @author     Sean Kerr (skerr@mojavi.org)
 * @copyright  2004-2005 Fabien Potencier <fabien.potencier@gmail.com>
 * @license    SymFony License 1.0
 * @version    SVN: $Id: sfWebRequest.class.php 480 2005-09-21 13:33:58Z fabien $
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

  /**
   * Retrieve an array of file information.
   *
   * @param string A file name
   *
   * @return array An associative array of file information, if the file exists, otherwise null.
   */
  public function getFile ($name)
  {
    return (isset($_FILES[$name]) ? $_FILES[$name] : null);
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
    return (isset($_FILES[$name]) ? $_FILES[$name]['error'] : UPLOAD_ERR_NO_FILE);
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
    return (isset($_FILES[$name]) ? $_FILES[$name]['name'] : null);
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
    return (isset($_FILES[$name]) ? $_FILES[$name]['tmp_name'] : null);
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
    return (isset($_FILES[$name]) ? $_FILES[$name]['size'] : null);
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
    return (isset($_FILES[$name]) ? $_FILES[$name]['type'] : null);
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
    return isset($_FILES[$name]);
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
    return (isset($_FILES[$name]) ? ($_FILES[$name]['error'] != UPLOAD_ERR_OK) : false);
  }

  /**
   * Indicates whether or not any file errors occured.
   *
   * @return bool true, if any file errors occured, otherwise false.
   */
  public function hasFileErrors ()
  {
    foreach ($_FILES as &$file)
    {
      if ($file['error'] != UPLOAD_ERR_OK)
        return true;
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

    // sfStats call
    if (SF_STATS)
    {
      sfStats::record($this->context);
    }
  }

  /**
   * Returns the array that contains all request information ($_SERVER or $_ENV).
   *
   * This information is stored in the SF_PATH_INFO_ARRAY constant.
   *
   * @return  array
   */
  private function getPathInfoArray()
  {
    // parse PATH_INFO
    switch (SF_PATH_INFO_ARRAY)
    {
      case 'SERVER':
        $pathArray =& $_SERVER;
        break;

      case 'ENV':
      default:
        $pathArray =& $_ENV;
    }

    return $pathArray;
  }

  public function getUri()
  {
    return 'http://'.$this->getHost().$_SERVER['REQUEST_URI'];
  }

  public function getPathInfo ()
  {
    $pathInfo = '';

    $pathArray = $this->getPathInfoArray();

    // simulate PATH_INFO if needed
    if (!isset($pathArray[SF_PATH_INFO_KEY]))
    {
      $script_name = $pathArray['SCRIPT_NAME'];
      $pathInfo = preg_replace('/^'.preg_quote($script_name, '/').'/', '', $pathArray['REQUEST_URI']);
      $prefix_name = preg_replace('#\/[^/]+$#', '', $script_name);
      $pathInfo = preg_replace('/^'.preg_quote($prefix_name, '/').'/', '', $pathArray['REQUEST_URI']);
      $pathInfo = preg_replace('/'.preg_quote($pathArray['QUERY_STRING'], '/').'$/', '', $pathInfo);
    }
    else
    {
      $pathInfo = $pathArray[SF_PATH_INFO_KEY];
    }

    return $pathInfo;
  }

  /**
   * Loads GET, PATH_INFO and POST data into the parameter list.
   *
   * @return void
   */
  private function loadParameters ()
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
          $this->setParameter('module', SF_ERROR_404_MODULE);
          $this->setParameter('action', SF_ERROR_404_ACTION);
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
            $this->getParameterHolder()->setByRef($array[$i], $array[++$i]);
        }
      }
    }

    // merge POST parameters
    $this->getParameterHolder()->addByRef($_POST);

    if (SF_LOGGING_ACTIVE)
    {
      $parameters = '';
      foreach ($this->getParameterHolder()->getAll() as $key => $value)
      {
        $parameters .= ''.$key.' => "'.$value.'", ';
      }

      $this->getContext()->getLogger()->info('{sfRequest} request parameters {'.$parameters.'}');
    }

    // move some parameters in other namespaces
    $special_parameters = array(
      'ignore_cache' => 'symfony/request/sfWebRequest',
    );
    foreach ($special_parameters as $param => $namespace)
    {
      if ($this->hasParameter($param))
      {
        $value = $this->getParameterHolder()->remove($param);
        $this->setParameter($param, $value, $namespace);
      }
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
    if (isset($_FILES[$name]) && $_FILES[$name]['error'] == UPLOAD_ERR_OK && $_FILES[$name]['size'] > 0)
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

      if (@move_uploaded_file($_FILES[$name]['tmp_name'], $file))
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

    return isset($pathArray['HTTP_HOST']) ? $pathArray['HTTP_HOST'] : '';
  }

  /**
   * Returns current script name.
   *
   * @return  string
   */
  public function getScriptName()
  {
    $pathArray = $this->getPathInfoArray();

    return isset($pathArray['SCRIPT_NAME']) ? $pathArray['SCRIPT_NAME'] : '';
  }

  /**
   * Get a list of languages acceptable by the client browser
   *
   * @return array languages ordered in the user browser preferences. 
   */
  public function getLanguages()
  {
    if ($this->languages)
      return $this->languages;

    $this->languages = array();

    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
      return $this->languages;

    foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $lang) 
    {
      // Cut off any q-value that might come after a semi-colon
      if ($pos = strpos($lang, ';'))
        $lang = trim(substr($lang, 0, $pos));

      if (strstr($lang, '-')) 
      {
        $codes = explode('-', $lang);
        if ($codes[0] == 'i')
        {
          // Language not listed in ISO 639 that are not variants
          // of any listed language, which can be registerd with the
          // i-prefix, such as i-cherokee
          if (count($codes) > 1)
            $lang = $codes[1];
        }
        else
        {
          for ($i = 0, $max = count($codes); $i < $max; $i++)
          {
            if ($i == 0)
              $lang = strtolower($codes[0]);
            else
              $lang .= '_'.strtoupper($codes[$i]);
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
      return $this->charsets;

    foreach (explode(',', $_SERVER['HTTP_ACCEPT_CHARSET']) as $charset) 
    {
      if (!empty($charset)) 
        $this->charsets[] = preg_replace('/;.*/', '', $charset);
    }

    return $this->charsets;
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