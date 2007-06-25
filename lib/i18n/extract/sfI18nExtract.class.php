<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage i18n
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfI18nExtract
{
  protected
    $currentMessages = array(),
    $newMessages = array(),
    $allSeenMessages = array(),
    $culture = null,
    $parameters = array(),
    $i18n = null;

  /**
   * Initializes the current extract object.
   *
   * @param string The culture
   * @param array  An array of parameters
   */
  function initialize($culture, $parameters = array())
  {
    if (!sfConfig::get('sf_i18n'))
    {
      throw new sfConfigurationException('You must enable "i18n" in your settings.yml configuration file.');
    }

    $this->allSeenMessages = array();
    $this->newMessages = array();
    $this->currentMessages = array();

    $this->culture = $culture;
    $this->parameters = $parameters;

    $this->i18n = sfContext::getInstance()->getI18N();

    $this->configure();

    $this->loadMessageSources();
    $this->loadCurrentMessages();
  }

  /**
   * Configures the current extract object.
   */
  public function configure()
  {
  }

  /**
   * Extracts i18n strings.
   *
   * This class must be implemented by subclasses.
   */
  abstract public function extract();

  /**
   * Saves the new messages.
   *
   * Current limitations:
   *  - For file backends (XLIFF and gettext), it only saves in the "most global" file
   */
  public function saveNewMessages()
  {
    $messageSource = $this->i18n->getLastMessageSource();
    foreach ($this->getNewMessages() as $message)
    {
      $messageSource->append($message);
    }

    $messageSource->save();
  }

  /**
   * Deletes old messages.
   *
   * Current limitations:
   *  - For file backends (XLIFF and gettext), it only deletes in the "most global" file
   */
  public function deleteOldMessages()
  {
    $messageSource = $this->i18n->getLastMessageSource();
    foreach ($this->getOldMessages() as $message)
    {
      $messageSource->delete($message);
    }
  }

  /**
   * Gets the new i18n strings.
   *
   * @param array An array of i18n strings
   */
  public function getNewMessages()
  {
    return $this->newMessages;
  }

  /**
   * Gets the current i18n strings.
   *
   * @param array An array of i18n strings
   */
  public function getCurrentMessages()
  {
    return $this->currentMessages;
  }

  /**
   * Gets all i18n strings seen during the extraction process.
   *
   * @param array An array of i18n strings
   */
  public function getAllSeenMessages()
  {
    return $this->allSeenMessages;
  }

  /**
   * Gets old i18n strings.
   *
   * This returns all strings that weren't seen during the extraction process
   * and are in the current messages.
   *
   * @param array An array of i18n strings
   */
  public function getOldMessages()
  {
    return array_diff($this->getCurrentMessages(), $this->getAllSeenMessages());
  }

  /**
   * Loads message sources objects and sets the culture.
   */
  protected function loadMessageSources()
  {
    foreach ($this->i18n->getMessageSources() as $messageSource)
    {
      $messageSource->setCulture($this->culture);
      $messageSource->load();
    }
  }

  /**
   * Loads messages already saved in the message sources.
   */
  protected function loadCurrentMessages()
  {
    $this->currentMessages = array();
    foreach ($this->i18n->getMessageSources() as $messageSource)
    {
      foreach ($messageSource->read() as $catalogue => $translations)
      {
        foreach ($translations as $key => $values)
        {
          $this->currentMessages[] = $key;
        }
      }
    }
  }

  /**
   * Extracts i18n strings from PHP files.
   *
   * @param string The PHP full path name
   */
  protected function extractFromPhpFiles($dir)
  {
    $phpExtractor = new sfI18nPhpExtractor();

    $files = sfFinder::type('file')->name('*.php');
    $messages = array();
    foreach ($files->in($dir) as $file)
    {
      $messages = array_merge($messages, $phpExtractor->extract(file_get_contents($file)));
    }

    $this->updateMessages($messages);
  }

  /**
   * Updates the internal arrays with new messages.
   *
   * @param array An array of new i18n strings
   */
  protected function updateMessages($messages)
  {
    $messages = array_unique($messages);

    $this->allSeenMessages = array_unique(array_merge($this->allSeenMessages, $messages));

    $this->newMessages = array_merge($this->newMessages, array_diff($messages, $this->currentMessages));
  }
}
