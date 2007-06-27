<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This class can be used to cache the result and output of functions/methods.
 *
 * @package    symfony
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfFunctionCache
{
  protected $cache = null;

  /**
   * Constructor.
   *
   * @param sfCache An sfCache object instance
   */
  public function __construct($cache)
  {
    $this->cache = $cache;
  }

  /**
   * Calls a cacheable function or method (or not if there is already a cache for it).
   *
   * Arguments of this method are read with func_get_args. So it doesn't appear in the function definition. Synopsis : 
   * call('functionName', $arg1, $arg2, ...)
   * (arg1, arg2... are arguments of 'functionName')
   *
   * @return mixed The result of the function/method
   */
  public function call()
  {
    $arguments = func_get_args();

    // Generate a cache id
    $id = md5(serialize($arguments));

    $data = $this->cache->get($id, '');
    if ($data !== null)
    {
      $array = unserialize($data);
      $output = $array['output'];
      $result = $array['result'];
    }
    else
    {
      $callable = array_shift($arguments);

      ob_start();
      ob_implicit_flush(false);

      if (!is_callable($callable))
      {
        throw new sfException('The first argument to call() must be a valid callable.');
      }

      try
      {
        $result = call_user_func_array($callable, $arguments);
      }
      catch (Exception $e)
      {
        ob_end_clean();
        throw $e;
      }

      $output = ob_get_contents();
      ob_end_clean();

      $array['output'] = $output;
      $array['result'] = $result;

      $this->cache->set($id, '', serialize($array));
    }

    echo($output);

    return $result;
  }
}
