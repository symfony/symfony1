<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * DateHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

function format_daterange($start_date, $end_date, $format = 'd', $full_text, $start_text, $end_text, $culture = null)
{
  if (!$culture) $culture = sfContext::getInstance()->getUser()->getCulture();

  require_once 'i18n/DateFormat.php';
  $dateFormat = new DateFormat($culture);

  if ($start_date != '' && $end_date != '')
  {
    return sprintf($full_text, $dateFormat->format($start_date, $format), $dateFormat->format($end_date, $format));
  }
  else if ($start_date != '')
  {
    return sprintf($start_text, $dateFormat->format($start_date, $format));
  }
  else if ($end_date != '')
  {
    return sprintf($end_text, $dateFormat->format($end_date, $format));
  }
}

function format_date($date, $format = 'd', $culture = null)
{
  if (!$culture) $culture = sfContext::getInstance()->getUser()->getCulture();
  
  require_once 'i18n/DateFormat.php';
  $dateFormat = new DateFormat($culture);
  return $dateFormat->format($date, $format);
}

function format_datetime($date, $format = 'F', $culture = null)
{
  if (!$culture) $culture = sfContext::getInstance()->getUser()->getCulture();

  require_once 'i18n/DateFormat.php';
  $dateFormat = new DateFormat($culture);
  return $dateFormat->format($date, $format);
}

function distance_of_time_in_words($from_time, $to_time, $include_seconds = false)
{
  $distance_in_minutes = abs(round(($to_time - $from_time) / 60));
  $distance_in_seconds = abs(round(($to_time - $from_time)));

  if ($distance_in_minutes <= 1)
  {
    if (!$include_seconds) return ($distance_in_minutes == 0) ? "less than a minute" : "1 minute";
    if ($distance_in_seconds <= 5)
      return "less than 5 seconds";
    else if ($distance_in_seconds >= 6 && $distance_in_seconds <= 10)
      return "less than 10 seconds";
    else if ($distance_in_seconds >= 11 && $distance_in_seconds <= 20)
      return "less than 20 seconds";
    else if ($distance_in_seconds >= 21 && $distance_in_seconds <= 40)
      return "half a minute";
    else if ($distance_in_seconds >= 41 && $distance_in_seconds <= 59)
      return "less than a minute";
    else
      return "1 minute";
  }
  else if ($distance_in_minutes >= 2 && $distance_in_minutes <= 45)
    return $distance_in_minutes." minutes";
  else if ($distance_in_minutes >= 46 && $distance_in_minutes <= 90)
    return "about 1 hour";
  else if ($distance_in_minutes >= 90 && $distance_in_minutes <= 1440)
    return "about ".round($distance_in_minutes / 60)." hours";
  else if ($distance_in_minutes >= 1441 && $distance_in_minutes <= 2880)
    return "1 day";
  else
    return round($distance_in_minutes / 1440)." days";
}

# Like distance_of_time_in_words, but where <tt>to_time</tt> is fixed to <tt>Time.now</tt>.
function time_ago_in_words($from_time, $include_seconds = false)
{
  return distance_of_time_in_words($from_time, time(), $include_seconds);
}

?>