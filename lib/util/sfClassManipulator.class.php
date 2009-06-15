<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfClassManipulator manipulates class code.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfCommandApplication.class.php 19217 2009-06-13 09:15:28Z fabien $
 */
class sfClassManipulator
{
  protected $code = '', $file = false;

  /**
   * Constructor.
   *
   * @param string $code The code to manipulate
   */
  public function __construct($code)
  {
    $this->code = $code;
  }

  /**
   * Creates a manipulator object from a file.
   *
   * @param string $file A file name
   *
   * @return sfClassManipulator A sfClassManipulator instance
   */
  static public function fromFile($file)
  {
    $class = __CLASS__;
    $manipulator = new $class(file_get_contents($file));
    $manipulator->setFile($file);

    return $manipulator;
  }

  /**
   * Saves the code back to the associated file.
   *
   * This only works if you have bound the instance with a file with the setFile() method.
   *
   * @throw LogicException if no file is associated with the instance
   */
  public function save()
  {
    if (!$this->file)
    {
      throw new LogicException('Unable to save the code as no file has been provided.');
    }

    file_put_contents($this->file, $this->code);
  }

  /**
   * Gets the modified code.
   *
   * @return string The modified code
   */
  public function getCode()
  {
    return $this->code;
  }

  /**
   * Gets the associated file.
   *
   * @return string The associated file
   */
  public function getFile()
  {
    return $this->file;
  }

  /**
   * Sets the file associated with this instance.
   *
   * @param string A file name
   */
  public function setFile($file)
  {
    $this->file = $file;
  }

  /**
   * Wraps an existing method with some code.
   *
   * @param string $method     The method name to change
   * @param string $beforeCode The code to add at the beginning of the method
   * @param string $endCode    The code to add at the end of the method
   */
  public function wrapMethod($method, $beforeCode = '', $afterCode = '')
  {
    $code = '';
    $insideSetup = -1;
    $parens = 0;
    foreach (token_get_all($this->code) as $token)
    {
      if (isset($token[1]))
      {
        if (-1 == $insideSetup && T_FUNCTION == $token[0])
        {
          $insideSetup = 0;
        }
        elseif (0 == $insideSetup && T_STRING == $token[0])
        {
          $insideSetup = $method == $token[1] ? 1 : -1;
        }

        $code .= $token[1];
      }
      else
      {
        if (1 == $insideSetup && '{' == $token)
        {
          if (!$parens)
          {
            $code .= $beforeCode ? "$token\n    $beforeCode" : $token;
          }

          ++$parens;
        }
        elseif (1 == $insideSetup && '}' == $token)
        {
          --$parens;

          if (!$parens)
          {
            $insideSetup = -1;

            $code .= $afterCode ? "  $afterCode\n  $token" : $token;
          }
        }
        else
        {
          $code .= $token;
        }
      }
    }

    return $this->code = $code;
  }
}
