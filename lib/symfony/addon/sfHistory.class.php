<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony.runtime.addon
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @version    SVN: $Id: sfHistory.class.php 370 2005-08-18 09:00:01Z fabien $
 */

/**
 *
 * sfHistory class.
 *
 * @package    symfony.runtime.addon
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @version    SVN: $Id: sfHistory.class.php 370 2005-08-18 09:00:01Z fabien $
 */
class sfHistory
{
  private $history = array();
  private $maxItems = 5;
  private $maxTitleLength = 40;

  public static function retrieveForUser($user)
  {
    if ($user->getAttribute('sfHistory'))
      $history = $user->getAttribute('sfHistory');
    else
    {
      $history = new sfHistory();
      $history->setMaxItems(5);
      $history->setMaxTitleLength(20);
      $user->setAttribute('sfHistory', $history);
    }

    return $history;
  }

  public function __construct()
  {
  }

  public function getMaxTitleLength()
  {
    return $this->maxTitleLength;
  }

  public function setMaxTitleLength($nb)
  {
    $this->maxTitleLength = $nb;
  }

  public function getMaxItems()
  {
    return $this->maxItems;
  }

  public function setMaxItems($nb)
  {
    $this->maxItems = $nb;
  }

  public function delHistory($url)
  {
    $i = 0;
    foreach ($this->history as $item)
    {
      if ($item[1] == $url) unset($this->history[$i]);
      $i++;
    }
  }

  public function pushHistory($name, $url, $icon)
  {
    if (preg_match('/javascript/i', $url)) return;

    if (strlen($name) > $this->maxTitleLength)
      $short_name = substr($name, 0, $this->maxTitleLength - 3).'...';
    else
      $short_name = $name;

    array_unshift($this->history, array($name, $short_name, $url, $icon));

    $already_seen = array();
    $i = 0;
    foreach ($this->history as $item)
    {
      if (array_key_exists($item[2], $already_seen)) unset($this->history[$i]);
      $already_seen[$item[2]] = 1;
      $i++;
    }

    // If maxItems is reached, we get rid of the first item added
    while (count($this->history) > $this->maxItems) array_pop($this->history);
  }

  public function hasHistory()
  {
    if (count($this->history) > 0)
      return true;
    else
      return false;
  }

  public function getHistory()
  {
    return $this->history;
  }

  public function clearHistory()
  {
    $this->history = array();
  }
}

?>