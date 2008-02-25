<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCache is an abstract class for all cache classes in symfony.
 *
 * @package    symfony
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfCache
{
  const OLD = 1;
  const ALL = 2;
  const SEPARATOR = ':';

  protected
    $options = array(),
    $prefix  = '';

  /**
   * Class constructor.
   *
   * @see initialize()
   */
  public function __construct($options = array())
  {
    $this->initialize($options);
  }

  /**
   * Initializes this sfCache instance.
   *
   * @param  array An array of options.
   *
   * Available options:
   *
   * * automatic_cleaning_factor: The automatic cleaning process destroy too old (for the given life time) (default value: 1000)
   *   cache files when a new cache file is written.
   *     0               => no automatic cache cleaning
   *     1               => systematic cache cleaning
   *     x (integer) > 1 => automatic cleaning randomly 1 times on x cache write
   *
   * * lifetime (optional): The default life time (default value: 86400)
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this sfCache instance.
   */
  public function initialize($options = array())
  {
    $this->options = array_merge(array(
      'automatic_cleaning_factor' => 1000,
      'lifetime'                  => 86400,
      'prefix'                    => md5(dirname(__FILE__)),
    ), $options);

    $this->options['prefix'] .= self::SEPARATOR;
  }

  /**
   * Gets the cache content for a given key.
   *
   * @param  string The cache key
   * @param  mixed  The default value is the key does not exist or not valid anymore
   *
   * @return mixed  The data of the cache
   */
  abstract public function get($key, $default = null);

  /**
   * Returns true if there is a cache for the given key.
   *
   * @param  string  The cache key
   *
   * @return Boolean true if the cache exists, false otherwise
   */
  public function has($key)
  {
    return $this->getTimeout($key) > time();
  }

  /**
   * Saves some data in the cache.
   *
   * @param string The cache key
   * @param mixed  The data to put in cache
   * @param int    The lifetime
   *
   * @return Boolean true if no problem
   */
  abstract public function set($key, $data, $lifetime = null);

  /**
   * Removes a content from the cache.
   *
   * @param string The cache key
   *
   * @return Boolean true if no problem
   */
  abstract public function remove($key);

  /**
   * Removes content from the cache that matches the given pattern.
   *
   * @param  string  The cache key pattern
   *
   * @return Boolean true if no problem
   *
   * @see patternToRegexp
   */
  abstract public function removePattern($pattern);

  /**
   * Cleans the cache.
   *
   * @param  string  The clean mode
   *                 sfCache::ALL: remove all keys (default)
   *                 sfCache::OLD: remove all expired keys
   *
   * @return Boolean true if no problem
   */
  abstract public function clean($mode = self::ALL);

  /**
   * Returns the timeout for the given key.
   *
   * @param string The cache key
   *
   * @return int The timeout time
   */
  abstract public function getTimeout($key);

  /**
   * Returns the last modification date of the given key.
   *
   * @param string The cache key
   *
   * @return int The last modified time
   */
  abstract public function getLastModified($key);

  /**
   * Gets many keys at once.
   *
   * @param  array An array of keys
   *
   * @return array An associative array of data from cache
   */
  public function getMany($keys)
  {
    $data = array();
    foreach ($keys as $key)
    {
      $data[$key] = $this->get($key);
    }

    return $data;
  }

  /**
   * Computes lifetime.
   *
   * @param  integer Lifetime in seconds
   *
   * @return integer Lifetime in seconds
   */
  public function getLifetime($lifetime)
  {
    return is_null($lifetime) ? $this->getOption('lifetime') : $lifetime;
  }

  /**
   * Gets the backend object.
   *
   * @return object The backend object
   */
  public function getBackend()
  {
    throw new sfException('This cache class does not have a backend object.');
  }

  /**
   * Gets an option value.
   *
   * @param  string The option name
   *
   * @return mixed  The option value
   */
  public function getOption($name, $default = null)
  {
    return isset($this->options[$name]) ? $this->options[$name] : $default;
  }

  /**
   * Sets an option value.
   *
   * @param string The option name
   * @param mixed  The option value
   */
  public function setOption($name, $value)
  {
    return $this->options[$name] = $value;
  }

  /**
   * Converts a pattern to a regular expression.
   *
   * A pattern can use some special characters:
   *
   *  - * Matches a namespace (foo:*:bar)
   *  - ** Matches one or more namespaces (foo:**:bar)
   *
   * @param  string A pattern
   *
   * @return string A regular expression
   */
  protected function patternToRegexp($pattern)
  {
    $regexp = str_replace(
      array('\\*\\*', '\\*'),
      array('.+?',    '[^'.preg_quote(sfCache::SEPARATOR, '#').']+'),
      preg_quote($pattern, '#')
    );

    return '#^'.$regexp.'$#';
  }
}
