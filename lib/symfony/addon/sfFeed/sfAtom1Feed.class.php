<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * Specification: http://atompub.org/2005/07/11/draft-ietf-atompub-format-10.html
 *
 * @package    symfony
 * @subpackage addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfAtom1Feed extends sfFeed
{
  public function getFeed()
  {
    header('Content-Type: application/atom+xml');

    $xml = array();
    $xml[] = '<?xml version="1.0" encoding="UTF-8" ?>';

    if ($this->getLanguage())
    {
      $xml[] = sprintf('<feed xmlns="%s" xml:lang="%s">', 'http://www.w3.org/2005/Atom', $this->getLanguage());
    }
    else
    {
      $xml[] = sprintf('<feed xmlns="%s">', 'http://www.w3.org/2005/Atom');
    }

    $xml[] = '  <title>'.$this->getTitle().'</title>';
    $xml[] = '  <link rel="alternate" href="'.sfContext::getInstance()->getController()->genUrl(null, $this->getLink(), true).'"></link>';
    if ($this->getFeedUrl())
    {
      $xml[] = '  <link rel="self" href="'.sfContext::getInstance()->getController()->genUrl(null, $this->getFeedUrl(), true).'"></link>';
    }
    $xml[] = '  <id>'.sfContext::getInstance()->getController()->genUrl(null, $this->getLink(), true).'</id>';
    $xml[] = '  <updated>'.strftime('%Y-%m-%dT%H:%M:%SZ', $this->getLatestPostDate()).'</updated>';

    if ($this->getAuthorName())
    {
      $xml[] = '  <author>';
      $xml[] = '    <name>'.$this->getAuthorName().'</name>';
      if ($this->getAuthorEmail())
      {
        $xml[] = '    <author_email>'.$this->getAuthorEmail().'</author_email>';
      }
      if ($this->getAuthorLink())
      {
        $xml[] = '    <author_link>'.$this->getAuthorLink().'</author_link>';
      }
      $xml[] = '  </author>';
    }

    if ($this->getSubtitle())
    {
      $xml[] = '  <subtitle>'.$this->getSubtitle().'</subtitle>';
    }

    foreach ($this->getCategories() as $category)
    {
      $xml[] = '  <category term="'.$category.'"></category>';
    }

    $xml[] = $this->getFeedElements();

    $xml[] = '</feed>';

    return implode("\n", $xml);
  }

  private function getFeedElements()
  {
    $xml = '';

    foreach ($this->getItems() as $item)
    {
      $xml[] = '<entry>';
      $xml[] = '  <title>'.htmlspecialchars($this->getItemFeedTitle($item)).'</title>';
      $xml[] = '  <link href="'.sfContext::getInstance()->getController()->genUrl(null, $this->getItemFeedLink($item), true).'"></link>';
      if ($this->getItemFeedPubdate($item))
      {
        $xml[] = '  <updated>'.strftime('%Y-%m-%dT%H:%M:%SZ', $this->getItemFeedPubdate($item)).'</updated>';
      }

      // author information
      if ($this->getItemFeedAuthorName($item))
      {
        $xml[] = '  <author>';
        $xml[] = '    <name>'.$this->getItemFeedAuthorName($item).'</name>';
        if ($this->getItemFeedAuthorEmail($item))
        {
          $xml[] = '    <author_email>'.$this->getItemFeedAuthorEmail($item).'</author_email>';
        }
        if ($this->getItemFeedAuthorLink($item))
        {
          $xml[] = '    <author_link>'.sfContext::getInstance()->getController()->genUrl(null, $this->getItemFeedAuthorLink($item), true).'</author_link>';
        }
        $xml[] = '  </author>';
      }

      // unique id
      if ($this->getItemFeedUniqueId($item))
      {
        $uniqueId = $this->getItemFeedUniqueId($item);
      }
      else
      {
        $uniqueId = $this->getTagUri($this->getItemFeedLink($item), $this->getItemFeedPubdate($item));
      }
      $xml[] = '  <id>'.$uniqueId.'</id>';

      // summary
      if ($this->getItemFeedDescription($item))
      {
        $xml[] = sprintf('  <summary type="html">%s</summary>', htmlspecialchars($this->getItemFeedDescription($item)));
      }

      // enclosure
      if ((method_exists($item, 'getFeedEnclosure')) && ($enclosure = $item->getFeedEnclosure()))
      {
        $xml[] = sprintf('  <link rel="enclosure" href="%s" length="%s" type="%s"></link>', $enclosure->getUrl(), $enclosure->getLength(), $enclosure->getMimeType());
      }

      // categories
      foreach ($this->getItemFeedCategories($item) as $category)
      {
        $xml[] = '  <category term="'.$category.'"></category>';
      }

      $xml[] = '</entry>';
    }

    return implode("\n", $xml);
  }

  // Creates a TagURI. See http://diveintomark.org/archives/2004/05/28/howto-atom-id
  private function getTagUri($url, $date)
  {
    $tag = preg_replace('#^http\://#', '', $url);
    if ($date)
    {
      $tag = preg_replace('#/#', ','.strftime('%Y-%m-%d', $date).':/', $tag, 1);
    }
    $tag = preg_replace('/#/', '/', $tag);

    return 'tag:'.$tag;
  }

  private function getLatestPostDate()
  {
    $updates = array();
    foreach ($this->getItems() as $item)
    {
      if ($this->getItemFeedPubdate($item))
      {
        $updates[] = $this->getItemFeedPubdate($item);
      }
    }

    if ($updates)
    {
      sort($updates);
      return $updates[count($updates) - 1];
    }
    else
    {
      return time();
    }
  }

}

?>