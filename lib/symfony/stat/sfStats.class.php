<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage stat
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfStats
{
  public static function getClicksForUriAsXml($uri)
  {
    $xml = '<?xml version="1.0" standalone="yes" ?>
    <uris>';

    $con = Propel::getConnection();
    $rs = $con->executeQuery('SELECT COUNT(uri) AS nb, uri FROM sf_stats WHERE referer = "'.$uri.'" GROUP BY uri');
//AND uri != "'.$uri.'" 
    while ($rs->next())
      $xml .= '<uri count="'.$rs->getInt('nb').'" uri="'.$rs->getString('uri').'" />';

    $xml .= '</uris>';

    return $xml;
  }

  public static function record($context)
  {

    if (!sfConfig::get('sf_stats')) return;

    switch (sfConfig::get('sf_path_info_array'))
    {
      case 'SERVER':
        $pathArray =& $_SERVER;
        break;
      case 'ENV':
      default:
        $pathArray =& $_ENV;
    }

    // first hit for this session?
    $request = $context->getRequest();
    $user = $context->getUser();
    if (!$user->getAttribute('session', null, 'symfony/stat/sfStats'))
    {
      $stats_session = new StatsSession();
      $stats_session->setAgent($pathArray['HTTP_USER_AGENT']);
      $stats_session->setIp($pathArray['REMOTE_ADDR']);
      $stats_session->save();

      $user->setAttribute('session', $stats_session->getId(), 'symfony/stat/sfStats');
      $user->setAttribute('position', 0, 'symfony/stat/sfStats');
    }

    $pos = $user->getAttribute('position', null, 'symfony/stat/sfStats') + 1;
    $user->setAttribute('position', $pos, 'symfony/stat/sfStats');

    $stats = new Stats();
    $stats->setStatsSessionId($user->getAttribute('session', null, 'symfony/stat/sfStats'));
    $stats->setPosition($pos);
    $stats->setDate(time());
    $stats->setCode(200);
    $stats->setSize(0);
    $stats->setUri($pathArray['REQUEST_URI']);

    // we strip our domain name from referer
    if (array_key_exists('HTTP_REFERER', $pathArray))
    {
      $stats->setReferer(preg_replace('/^https?\:\/\/'.preg_quote($pathArray['HTTP_HOST']).'/i', '', $pathArray['HTTP_REFERER']));
    }

    // we set 9 user defined columns
    for ($i = 1; $i < 10; $i++)
    {
      $stats->setByName('Value'.$i, ($request->getAttribute('stats_value_'.$i)));
    }

    $stats->save();
  }
}

?>
