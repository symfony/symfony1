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
class sfRssUserland091Feed extends sfRssFeed
{
  protected function getFeedElements()
  {
    $xml = array();
    foreach ($this->getItems() as $item)
    {
      $xml[] = '<item>';
      $xml[] = '  <title>'.htmlspecialchars($this->getItemFeedTitle($item)).'</title>';
      if ($this->getItemFeedDescription($item))
      {
        $xml[] = '  <description>'.htmlspecialchars($this->getItemFeedDescription($item)).'</description>';
      }
      $xml[] = '  <link>'.$this->getItemFeedLink($item).'</link>';
      $xml[] = '</item>';
    }

    return $xml;
  }

  protected function getVersion()
  {
    return '0.91';
  }
}

?>