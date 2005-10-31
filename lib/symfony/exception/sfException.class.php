<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project>
 * (c) 2004, 2005 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfException is the base class for all symfony related exceptions and
 * provides an additional method for printing up a detailed view of an
 * exception.
 *
 * @package    symfony
 * @subpackage exception
 * @author     Fabien Potencier <fabien.potencier@symfony-project>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfException extends Exception
{
  private
    $name = null;

  private static
    $format = 'plain';

  /**
   * Class constructor.
   *
   * @param string The error message.
   * @param int    The error code.
   */
  public function __construct ($message = null, $code = 0)
  {
    if ($this->getName() === null)
    {
      $this->setName('sfException');
    }

    parent::__construct($message, $code);

    if (defined('SF_LOGGING_ACTIVE') && SF_LOGGING_ACTIVE)
    {
      sfLogger::getInstance()->err('{'.$this->getName().'} '.$message);
    }
  }

  /**
   * Gets the stack trace format.
   *
   * @return string The format to use for printing.
   */
  public static function getFormat()
  {
    return self::$format;
  }

  /**
   * Sets the stack trace format.
   *
   * @param string The format you wish to use for printing. Options include:
   *               - html
   *               - plain
   */
  public static function setFormat($format)
  {
    self::$format = $format;
  }

  /**
   * Retrieve the name of this exception.
   *
   * @return string This exception's name.
   */
  public function getName ()
  {
    return $this->name;
  }

  /**
   * Print the stack trace for this exception.
   */
  public function printStackTrace ()
  {
    // exception related properties
    $class     = ($this->getFile() != null) ? sfToolkit::extractClassName($this->getFile()) : 'N/A';
    $class     = ($class != '') ? $class : 'N/A';
    $code      = ($this->getCode() > 0) ? $this->getCode() : 'N/A';
    $file      = ($this->getFile() != null) ? $this->getFile() : 'N/A';
    $line      = ($this->getLine() != null) ? $this->getLine() : 'N/A';
    $message   = ($this->getMessage() != null) ? $this->getMessage() : 'N/A';
    $name      = $this->getName();
    $traceData = $this->getTrace();
    $trace     = array();

    // lower-case the format to avoid sensitivity issues
    $format = strtolower(self::$format);

    if ($trace !== null && count($traceData) > 0)
    {
      // format the stack trace
      for ($i = 0, $z = count($traceData); $i < $z; $i++)
      {
        // no file key exists, skip this index
        if (!isset($traceData[$i]['file'])) continue;

        // grab the class name from the file
        // (this only works with properly named classes)
        $tClass = sfToolkit::extractClassName($traceData[$i]['file']);

        $tFile      = $traceData[$i]['file'];
        $tFunction  = $traceData[$i]['function'];
        $tLine      = $traceData[$i]['line'];

        if ($tClass != null)
        {
          $tFunction = $tClass.'::'.$tFunction.'()';
        }
        else
        {
          $tFunction = $tFunction.'()';
        }

        if ($format == 'html')
        {
          $tFunction = '<strong>'.$tFunction.'</strong>';
        }

        $data = 'at %s in [%s:%s]';
        $data = sprintf($data, $tFunction, $tFile, $tLine);

        $trace[] = $data;
      }
    }

    $error_file = 'error';
    $error_ext = 'txt';
    switch ($format)
    {
      case 'html':
        $error_ext = 'php';
        break;

      case 'plain':
      default:
        break;
    }

    if (file_exists(SF_APP_TEMPLATE_DIR.DIRECTORY_SEPARATOR.$error_file.'_'.SF_ENVIRONMENT.'.'.$error_ext))
    {
      $error_file = 'error_'.SF_ENVIRONMENT;
    }

    include(SF_APP_TEMPLATE_DIR.DIRECTORY_SEPARATOR.$error_file.'.'.$error_ext);

    // if test, do not exit
    if (!SF_TEST)
    {
      exit(1);
    }
  }

  /**
   * Set the name of this exception.
   *
   * @param string An exception name.
   */
  protected function setName ($name)
  {
    $this->name = $name;
  }
}

?>