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
    $context       = null,
    $cache         = null,
    $culture       = null,
    $messageSource = null,
    $messageFormat = null;

  /**
   * Initializes this class.
   *
   * @param sfContext A sfContext implementation instance
   */
  public function initialize($context, sfCache $cache = null)
  {
    $this->context = $context;
    $this->cache   = $cache;

    include(sfConfigCache::getInstance()->checkConfig(sfConfig::get('sf_app_config_dir_name').'/i18n.yml'));
  }

  /**
   * Sets the message source.
   *
   * @param mixed  An array of i18n directories if message source is XLIFF or gettext, null otherwise
   * @param string The culture
   */
  public function setMessageSource($dirs, $culture)
  {
    if (is_null($dirs))
    {
      $this->messageSource = $this->createMessageSource();
    }
    else
    {
      $this->messageSource = sfMessageSource::factory('Aggregate', array_map(array($this, 'createMessageSource'), $dirs));
    }
/*
    if (!is_null($this->cache))
    {
      $this->messageSource->setCache(new sfMessageCache($this->cache));
    }
*/
    $this->setCulture($culture);
    $this->messageFormat = null;
  }

  /**
   * Returns a new message source.
   *
   * @param  mixed           An array of i18n directories to create a XLIFF or gettext message source, null otherwise
   *
   * @return sfMessageSource A sfMessageSource object
   */
  public function createMessageSource($dir = null)
  {
    if (in_array(sfConfig::get('sf_i18n_source'), array('Creole', 'MySQL', 'SQLite')))
    {
      return sfMessageSource::factory(sfConfig::get('sf_i18n_source'), sfConfig::get('sf_i18n_database', 'default'));
    }
    else
    {
      return sfMessageSource::factory(sfConfig::get('sf_i18n_source'), $dir);
    }
  }

  /**
   * Sets the current culture for i18n format objects.
   *
   * @param string The culture
   */
  public function setCulture($culture)
  {
    if ($this->messageSource)
    {
      $this->culture = $culture;
      $this->messageSource->setCulture($culture);
    }
  }

  /**
   * Gets the message source.
   *
   * @return sfMessageSource A sfMessageSource object
   */
  public function getMessageSource()
  {
    if (!isset($this->messageSource))
    {
      $this->setMessageSource(sfLoader::getI18NGlobalDirs(), $this->context->getUser()->getCulture());
    }

    return $this->messageSource;
  }

  /**
   * Gets the message format.
   *
   * @return sfMessageFormat A sfMessageFormat object
   */
  public function getMessageFormat()
  {
    if (!isset($this->messageFormat))
    {
      $this->messageFormat = new sfMessageFormat($this->getMessageSource(), sfConfig::get('sf_charset'));

      if (sfConfig::get('sf_debug') && sfConfig::get('sf_i18n_debug'))
      {
        $this->messageFormat->setUntranslatedPS(array(sfConfig::get('sf_i18n_untranslated_prefix'), sfConfig::get('sf_i18n_untranslated_suffix')));
      }
    }

    return $this->messageFormat;
  }

  /**
   * Gets the translation for the given string
   *
   * @param  string The string to translate
   * @param  array  An array of arguments for the translation
   * @param  string The catalogue name
   *
   * @return string The translated string
   */
  public function __($string, $args = array(), $catalogue = 'messages')
  {
    return $this->getMessageFormat()->format($string, $args, $catalogue);
  }

  /**
   * Gets a country name.
   *
   * @param  string The ISO code
   * @param  string The culture
   *
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
   *
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
   *
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
   *
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
