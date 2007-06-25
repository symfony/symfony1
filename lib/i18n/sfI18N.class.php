<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfI18N wraps the core i18n classes for a symfony context.
 *
 * @package    symfony
 * @subpackage i18n
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfI18N
{
  protected
    $context        = null,
    $culture        = null,
    $messageSources = array(),
    $messageFormats = array();

  /**
   * Initializes this class.
   *
   * @param sfContext A sfContext implementation instance
   */
  public function initialize($context)
  {
    $this->context = $context;

    $this->setMessageSource(sfLoader::getI18NGlobalDirs(), $context->getUser()->getCulture());
  }

  /**
   * Sets the message sources.
   *
   * @param mixed  An array of i18n directories if message source configuration is XLIFF or gettext, null otherwise
   * @param string The culture
   */
  public function setMessageSource($dirs, $culture)
  {
    $this->messageSources = array();
    $this->messageFormats = array();

    if (is_null($dirs))
    {
      $this->messageSources = $this->createMessageSource();
    }
    else
    {
      foreach ($dirs as $dir)
      {
        $this->messageSources[] = $this->createMessageSource($dir);
      }
    }

    $this->setCulture($culture);
  }

  /**
   * Returns a new message source.
   *
   * @param  mixed           An array of i18n directories to create a XLIFF or gettext message source, null otherwise
   * @return sfMessageSource A sfMessageSource object
   */
  public function createMessageSource($dir = null)
  {
    if (in_array(sfConfig::get('sf_i18n_source'), array('Creole', 'MySQL', 'SQLite')))
    {
      $messageSource = sfMessageSource::factory(sfConfig::get('sf_i18n_source'), sfConfig::get('sf_i18n_database', 'default'));
    }
    else
    {
      $messageSource = sfMessageSource::factory(sfConfig::get('sf_i18n_source'), $dir);
    }

    if (sfConfig::get('sf_i18n_cache'))
    {
      $subdir   = str_replace(str_replace('/', DIRECTORY_SEPARATOR, sfConfig::get('sf_root_dir')), '', $dir);
      $cacheDir = str_replace('/', DIRECTORY_SEPARATOR, sfConfig::get('sf_i18n_cache_dir').$subdir);

      $cache = new sfMessageCache();
      $cache->initialize(array(
        'cacheDir' => $cacheDir,
        'lifeTime' => 86400,
      ));

      $messageSource->setCache($cache);
    }

    return $messageSource;
  }

  /**
   * Returns a new message format for the given message source.
   *
   * @param  sfIMessageSource A sfMessageSource object
   * @return sfMessageFormat  A sfMessageFormat object
   */
  public function createMessageFormat(sfIMessageSource $source)
  {
    $messageFormat = new sfMessageFormat($source, sfConfig::get('sf_charset'));

    if (sfConfig::get('sf_debug') && sfConfig::get('sf_i18n_debug'))
    {
      $messageFormat->setUntranslatedPS(array(sfConfig::get('sf_i18n_untranslated_prefix'), sfConfig::get('sf_i18n_untranslated_suffix')));
    }

    return $messageFormat;
  }

  /**
   * Sets the current culture for i18n format objects.
   *
   * @param string The culture
   */
  public function setCulture($culture)
  {
    if ($culture != $this->culture)
    {
      $this->culture = $culture;

      $this->messageFormats = array();
    }

    foreach ($this->messageSources as $messageSource)
    {
      $messageSource->setCulture($culture);
    }
  }

  /**
   * Gets all current message sources.
   *
   * @return array An array of sfMessageSource objects
   */
  public function getMessageSources()
  {
    return $this->messageSources;
  }

  /**
   * Gets the message source for the given index.
   *
   * @param  integer         The indice (1 based)
   * @return sfMessageSource A sfMessageSource object
   */
  public function getMessageSource($i = 1)
  {
    if (!isset($this->messageSources[$i - 1]))
    {
      throw new sfException(sprintf('The "$i" message source does not exist.', $i));
    }

    return $this->messageSources[$i - 1];
  }

  /**
   * Gets the last message source.
   */
  public function getLastMessageSource()
  {
    return $this->getMessageSource(count($this->messageSources));
  }

  /**
   * Gets all current message formats.
   *
   * @return array An array of sfMessageFormat objects
   */
  public function getMessageFormats()
  {
    for ($i = 0, $count = count($this->messageSources); $i < $count; $i++)
    {
      $this->getMessageFormat($i);
    }

    return $this->messageFormats;
  }

  /**
   * Gets the message format for the given index.
   *
   * @param  integer         The indice (1 based)
   * @return sfMessageFormat A sfMessageFormat object
   */
  public function getMessageFormat($i = 1)
  {
    if (!isset($this->messageFormats[$i - 1]))
    {
      $this->messageFormats[$i - 1] = $this->createMessageFormat($this->getMessageSource($i));
    }

    return $this->messageFormats[$i - 1];
  }

  /**
   * Gets the last message format.
   */
  public function getLastMessageFormat()
  {
    return $this->getMessageFormat(count($this->messageSources));
  }

  /**
   * Gets the translation for the given string
   *
   * @param  string The string to translate
   * @param  array  An array of arguments for the translation
   * @param  string The catalogue name
   * @return string The translated string
   */
  public function __($string, $args = array(), $catalogue = 'messages')
  {
    for ($i = 0, $count = count($this->messageSources); $i < $count; $i++)
    {
      if ($retval = $this->getMessageFormat($i + 1)->formatExists($string, $args, $catalogue))
      {
        return $retval;
      }
    }

    return $this->getLastMessageFormat()->format($string, $args, $catalogue);
  }

  /**
   * Gets a country name.
   *
   * @param  string The ISO code
   * @param  string The culture
   * @return string The country name
   */
  public function getCountry($iso, $culture)
  {
    $c = new sfCultureInfo($culture);
    $countries = $c->getCountries();

    return (array_key_exists($iso, $countries)) ? $countries[$iso] : '';
  }

  /**
   * Gets a native culture name.
   *
   * @param  string The culture
   * @return string The culture name
   */
  public function getNativeName($culture)
  {
    $cult = new sfCultureInfo($culture);

    return $cult->getNativeName();
  }

  /**
   * Returns a timestamp from a date formatted with a given culture.
   *
   * @param  string  The formatted date as string
   * @param  string  The culture
   * @return integer The timestamp
   */
  public function getTimestampForCulture($date, $culture)
  {
    list($d, $m, $y) = $this->getDateForCulture($date, $culture);

    return mktime(0, 0, 0, $m, $d, $y);
  }

  /**
   * Returns the day, month and year from a date formatted with a given culture.
   *
   * @param  string  The formatted date as string
   * @param  string  The culture
   * @return array   An array with the day, month and year
   */
  public function getDateForCulture($date, $culture)
  {
    if (!$date) return 0;

    $dateFormatInfo = @sfDateTimeFormatInfo::getInstance($culture);
    $dateFormat = $dateFormatInfo->getShortDatePattern();

    // We construct the regexp based on date format
    $dateRegexp = preg_replace('/[dmy]+/i', '(\d+)', $dateFormat);

    // We parse date format to see where things are (m, d, y)
    $a = array(
      'd' => strpos($dateFormat, 'd'),
      'm' => strpos($dateFormat, 'M'),
      'y' => strpos($dateFormat, 'y'),
    );
    $tmp = array_flip($a);
    ksort($tmp);
    $i = 0;
    $c = array();
    foreach ($tmp as $value) $c[++$i] = $value;
    $datePositions = array_flip($c);

    // We find all elements
    if (preg_match("~$dateRegexp~", $date, $matches))
    {
      // We get matching timestamp
      return array($matches[$datePositions['d']], $matches[$datePositions['m']], $matches[$datePositions['y']]);
    }
    else
    {
      return null;
    }
  }
}
