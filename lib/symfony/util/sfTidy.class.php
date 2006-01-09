<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTidy is a wrapper for the tidy library.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id: sfAction.class.php 368 2005-08-13 16:22:38Z fabien $
 */
class sfTidy
{
  public static function tidy($html, $name)
  {

    if (!function_exists('tidy_parse_string')) return $html;

    if (sfConfig::get('sf_logging_active')) $log = sfLogger::getInstance();

    if (sfConfig::get('sf_logging_active')) $log->info('{sfView} tidy output for "'.$name.'"');

    $tidy = new tidy();
    $tidy->parseString($html, sfConfig::get('sf_app_dir').DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'tidy.conf');
    $tidy->cleanRepair();

    // warnings and errors
    if (sfConfig::get('sf_logging_active'))
    {
      $tidy->diagnose();

      $error_msgs = array(
        'access' => array(),
        'warning' => array(),
        'error' => array(),
      );
      if ($tidy->errorBuffer)
      {
        foreach (split("\n", htmlspecialchars($tidy->errorBuffer)) as $line)
        {
          if (trim($line) == '') continue;
          if (preg_match('/were found\!/', $line)) continue;

          $line = '{sfView} '.$line;
          if (preg_match('/Error\:/i', $line))
            $error_msgs['error'][] = $line;
          else if (preg_match('/Access\:/i', $line))
            $error_msgs['access'][] = $line;
          else if (preg_match('/Warning\:/i', $line))
            $error_msgs['warning'][] = $line;
          else if (preg_match('/Info/i', $line))
            $log->info($line);
          else
            $log->info($line);
        }
      }

      if (tidy_error_count($tidy))
      {
        $msg = '{sfView} '.tidy_error_count($tidy).' error(s) for "'.$name.'"';
        if (count($error_msgs['error'])) $msg .= '[BEGIN_COMMENT] [n] '.implode('[n]', $error_msgs['error']).' [END_COMMENT]';
        $log->err($msg);
      }
      if (tidy_warning_count($tidy))
      {
        $msg = '{sfView} '.tidy_warning_count($tidy).' warning(s) for "'.$name.'"';
        if (count($error_msgs['warning'])) $msg .= '[BEGIN_COMMENT] [n] '.implode('[n]', $error_msgs['warning']).' [END_COMMENT]';
        $log->warning($msg);
      }
      if (tidy_access_count($tidy))
      {
        $msg = '{sfView} '.tidy_access_count($tidy).' accessibility problem(s) for "'.$name.'"';
        if (count($error_msgs['access'])) $msg .= '[BEGIN_COMMENT] [n] '.implode('[n]', $error_msgs['access']).' [END_COMMENT]';
        $log->warning($msg);
      }
    }

    return (string) $tidy;
  }
}

?>