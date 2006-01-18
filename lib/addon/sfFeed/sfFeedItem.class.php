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
class sfFeedItem
{
  private
   $title,
   $link,
   $description,
   $authorEmail,
   $authorName,
   $authorLink,
   $pubdate,
   $comments,
   $uniqueId,
   $enclosure,
   $categories = array();

  public function setFeedTitle ($title)
  {
    $this->title = $title;
  }

  public function getFeedTitle ()
  {
    return $this->title;
  }

  public function setFeedLink ($link)
  {
    $this->link = $link;
  }

  public function getFeedLink ()
  {
    return $this->link;
  }

  public function setFeedDescription ($description)
  {
    $this->description = $description;
  }

  public function getFeedDescription ()
  {
    return $this->description;
  }

  public function setFeedAuthorEmail ($authorEmail)
  {
    $this->authorEmail = $authorEmail;
  }

  public function getFeedAuthorEmail ()
  {
    return $this->authorEmail;
  }

  public function setFeedAuthorName ($authorName)
  {
    $this->authorName = $authorName;
  }

  public function getFeedAuthorName ()
  {
    return $this->authorName;
  }

  public function setFeedAuthorLink ($authorLink)
  {
    $this->authorLink = $authorLink;
  }

  public function getFeedAuthorLink ()
  {
    return $this->authorLink;
  }

  public function setFeedPubdate ($pubdate)
  {
    $this->pubdate = $pubdate;
  }

  public function getFeedPubdate ()
  {
    return $this->pubdate;
  }

  public function setFeedComments ($comments)
  {
    $this->comments = $comments;
  }

  public function getFeedComments ()
  {
    return $this->comments;
  }

  public function setFeedUniqueId ($uniqueId)
  {
    $this->uniqueId = $uniqueId;
  }

  public function getFeedUniqueId ()
  {
    return $this->uniqueId;
  }

  public function setFeedEnclosure ($enclosure)
  {
    $this->enclosure = $enclosure;
  }

  public function getFeedEnclosure ()
  {
    return $this->enclosure;
  }

  public function setFeedCategories ($categories)
  {
    $this->categories = $categories;
  }

  public function getFeedCategories ()
  {
    return $this->categories;
  }
}

?>