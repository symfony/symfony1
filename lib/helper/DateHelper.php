<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
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
  if (!$culture)
  {
    $culture = sfContext::getInstance()->getUser()->getCulture();
  }

  $dateFormat = new sfDateFormat($culture);

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

function format_date($date, $format = 'd', $culture = null, $charset = null)
{
  if (!$culture)
  {
    $culture = sfContext::getInstance()->getUser()->getCulture();
  }
  if (!$charset)
  {
    $charset = sfConfig::get('sf_charset');
  }

  $dateFormat = new sfDateFormat($culture);

  return $dateFormat->format($date, $format, null, $charset);
}

function format_datetime($date, $format = 'F', $culture = null, $charset = null)
{
  if (!$culture)
  {
    $culture = sfContext::getInstance()->getUser()->getCulture();
  }
  if (!$charset)
  {
    $charset = sfConfig::get('sf_charset');
  }

  $dateFormat = new sfDateFormat($culture);

  return $dateFormat->format($date, $format, null, $charset);
}

function distance_of_time_in_words($from_time, $to_time = null, $include_seconds = false)
{
  $to_time = $to_time? $to_time: time();

  $distance_in_minutes = floor(abs($to_time - $from_time) / 60);
  $distance_in_seconds = floor(abs($to_time - $from_time));

  $string = '';
  $parameters = array();

  if ($distance_in_minutes <= 1)
  {
    if (!$include_seconds)
    {
      $string = $distance_in_minutes == 0 ? 'less than a minute' : '1 minute';
    }
    else
    {
      if ($distance_in_seconds <= 5)
      {
        $string = 'less than 5 seconds';
      }
      else if ($distance_in_seconds >= 6 && $distance_in_seconds <= 10)
      {
        $string = 'less than 10 seconds';
      }
      else if ($distance_in_seconds >= 11 && $distance_in_seconds <= 20)
      {
        $string = 'less than 20 seconds';
      }
      else if ($distance_in_seconds >= 21 && $distance_in_seconds <= 40)
      {
        $string = 'half a minute';
      }
      else if ($distance_in_seconds >= 41 && $distance_in_seconds <= 59)
      {
        $string = 'less than a minute';
      }
      else
      {
        $string = '1 minute';
      }
    }
  }
  else if ($distance_in_minutes >= 2 && $distance_in_minutes <= 45)
  {
    $string = '%minutes% minutes';
    $parameters['%minutes%'] = $distance_in_minutes;
  }
  else if ($distance_in_minutes >= 46 && $distance_in_minutes <= 90)
  {
    $string = 'about 1 hour';
  }
  else if ($distance_in_minutes >= 90 && $distance_in_minutes <= 1440)
  {
    $string = 'about %hours% hours';
    $parameters['%hours%'] = round($distance_in_minutes / 60);
  }
  else if ($distance_in_minutes >= 1441 && $distance_in_minutes <= 2880)
  {
    $string = '1 day';
  }
  else if ($distance_in_minutes >= 2880 && $distance_in_minutes <= 43199)
  {
    $string = '%days% days';
    $parameters['%days%'] = round($distance_in_minutes / 1440);
  }
  else if ($distance_in_minutes >= 43200 && $distance_in_minutes <= 86399)
  {
    $string = 'about 1 month';
  }
  else if ($distance_in_minutes >= 86400 && $distance_in_minutes <= 525959)
  {
    $string = '%months% months';
    $parameters['%months%'] = round($distance_in_minutes / 43200);
  }
  else if ($distance_in_minutes >= 525960 && $distance_in_minutes <= 1051919)
  {
    $string = 'about 1 year';
  }
  else
  {
    $string = 'over %years% years';
    $parameters['%years%'] = round($distance_in_minutes / 525960);
  }

  if (sfConfig::get('sf_i18n'))
  {
    use_helper('I18N');

    return __($string, $parameters);
  }
  else
  {
    return strtr($string, $parameters);
  }
}

// Like distance_of_time_in_words, but where to_time is fixed to time()
function time_ago_in_words($from_time, $include_seconds = false)
{
  return distance_of_time_in_words($from_time, time(), $include_seconds);
}
