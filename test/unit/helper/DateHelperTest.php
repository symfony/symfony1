<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

sfLoader::loadHelpers(array('Helper', 'Asset', 'Url', 'Tag', 'Date'));

$t = new lime_test(27, new lime_output_color());

class sfContext
{
  public $user = null;
  public static $instance = null;

  public function getInstance()
  {
    if (!isset(self::$instance))
    {
      self::$instance = new sfContext();
    }

    return self::$instance;
  }

  public function getUser()
  {
    return $this->user;
  }
}

class sfUser
{
  public $culture = 'en';

  public function getCulture()
  {
    return $this->culture;
  }
}

sfConfig::set('sf_charset', 'utf-8');

$context = sfContext::getInstance();
$user = new sfUser();
$context->user = $user;

// distance_of_time_in_words()
$t->diag('distance_of_time_in_words()');
$now = time();
$msg = 'distance_of_time_in_words() format a distance of time in words!';
$t->is(distance_of_time_in_words($now - 2, $now), 'less than a minute', $msg);
$t->is(distance_of_time_in_words($now - 8, $now), 'less than a minute', $msg);
$t->is(distance_of_time_in_words($now - 13, $now), 'less than a minute', $msg);
$t->is(distance_of_time_in_words($now - 25, $now), 'less than a minute', $msg);
$t->is(distance_of_time_in_words($now - 49, $now), 'less than a minute', $msg);
$t->is(distance_of_time_in_words($now - 60, $now, true), '1 minute', $msg);

$t->is(distance_of_time_in_words($now - 2, $now, true), 'less than 5 seconds', $msg);
$t->is(distance_of_time_in_words($now - 8, $now, true), 'less than 10 seconds', $msg);
$t->is(distance_of_time_in_words($now - 13, $now, true), 'less than 20 seconds', $msg);
$t->is(distance_of_time_in_words($now - 25, $now, true), 'half a minute', $msg);
$t->is(distance_of_time_in_words($now - 49, $now, true), 'less than a minute', $msg);
$t->is(distance_of_time_in_words($now - 60, $now, true), '1 minute', $msg);

$t->is(distance_of_time_in_words($now - 10 * 60, $now), '10 minutes', $msg);
$t->is(distance_of_time_in_words($now - 50 * 60, $now), 'about 1 hour', $msg);

$t->is(distance_of_time_in_words($now - 3 * 3600, $now), 'about 3 hours', $msg);
$t->is(distance_of_time_in_words($now - 25 * 3600, $now), '1 day', $msg);

$t->is(distance_of_time_in_words($now - 4 * 86400, $now), '4 days', $msg);
$t->is(distance_of_time_in_words($now - 35 * 86400, $now), 'about 1 month', $msg);
$t->is(distance_of_time_in_words($now - 75 * 86400, $now), '3 months', $msg);

$t->is(distance_of_time_in_words($now - 370 * 86400, $now), 'about 1 year', $msg);
$t->is(distance_of_time_in_words($now - 4 * 365 * 86400, $now), 'over 4 years', $msg);

// format_date()
$t->diag('format_date()');
$user->culture = 'fr';
$t->is(format_date(time()), date('d/m/Y'), 'format_date() format a date according to the user culture');
$t->is(format_date(date('Y-m-d')), date('d/m/Y'), 'format_date() format a date according to the user culture');

$user->culture = 'en';
$time = time();
$t->is(format_date($time, 'F'), date('d F Y H:i:s', $time).' CET', 'format_date() takes a format string as its second argument');

$user->culture = 'fr';
$t->is(format_date($time, 'F', 'en'), date('d F Y H:i:s', $time).' CET', 'format_date() takes a culture as its third argument');

// format_datetime()
$t->diag('format_datetime()');
$user->culture = 'en';
$time = time();
$t->is(format_datetime($time), date('d F Y H:i:s', $time).' CET', 'format_datetime() format a date time according to the user culture');
$t->is(format_datetime(date('Y-m-d')), date('d F Y').' 00:00:00 CET', 'format_datetime() format a date time according to the user culture');
