<?php

require_once 'i18n/CultureInfo.php';
require_once 'i18n/DateFormat.php';

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage i18n
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @version    SVN: $Id: sfI18N.class.php 432 2005-09-07 12:30:24Z fabien $
 */
class sfI18N
{
  public static function getCountry($iso, $culture)
  {
    $c = new CultureInfo($culture);
    $countries = $c->getCountries();

    return (array_key_exists($iso, $countries)) ? $countries[$iso] : '';
  }

  public static function getNativeName($culture)
  {
    $cult = new CultureInfo($culture);
    return $cult->getNativeName();
  }

  // Return timestamp from a date formatted with a given culture
  public static function getTimestampForCulture($date, $culture)
  {
    list($d, $m, $y) = sfI18N::getDateForCulture($date, $culture);
    return mktime(0, 0, 0, $m, $d, $y);
  }

  // Return a d, m and y from a date formatted with a given culture
  public static function getDateForCulture($date, $culture)
  {
    if (!$date) return 0;

    $dateFormatInfo = @DateTimeFormatInfo::getInstance($culture);
    $dateFormat = $dateFormatInfo->getShortDatePattern();

    // We construct the regexp based on date format
    $dateRegexp = preg_replace('~[dmy]+~i', '(\d+)', $dateFormat);
  
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
    preg_match("~$dateRegexp~", $date, $matches);

    // We get matching timestamp
    return array($matches[$datePositions['d']], $matches[$datePositions['m']], $matches[$datePositions['y']]);
  }
}

?>