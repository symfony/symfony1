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
 * @subpackage addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfRssFeed extends sfFeed
{
  public function getFeed()
  {
    header('Content-Type: application/rss+xml');

    $xml = array();
    $xml[] = '<?xml version="1.0" encoding="UTF-8" ?>';
    $xml[] = '<rss version="'.$this->getVersion().'">';
    $xml[] = '  <channel>';
    $xml[] = '  <title>'.$this->getTitle().'</title>';
    $xml[] = '  <link>'.sfContext::getInstance()->getController()->genUrl(null, $this->getLink(), true).'</link>';
    $xml[] = '  <description>'.$this->getDescription().'</description>';
    if ($this->getLanguage())
    {
      $xml[] = '  <language>'.$this->getLanguage().'</language>';
    }
    $xml[] = implode("\n", $this->getFeedElements());
    $xml[] = '  </channel>';
    $xml[] = '</rss>';

    return implode("\n", $xml);
  }

  abstract protected function getFeedElements();

  abstract protected function getVersion();
}

?>