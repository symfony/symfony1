<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormI18nSelectLanguage represents a language HTML select tag.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWidgetFormI18nSelectLanguage extends sfWidgetFormSelect
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * culture:   The culture to use for internationalized strings (required)
   *  * languages: An array of language codes to use (ISO 639-1)
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetFormSelect
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);

    $this->addRequiredOption('culture');
    $this->addOption('languages');

    // populate choices with all languages
    $culture = isset($options['culture']) ? $options['culture'] : 'en';

    $cultureInfo = new sfCultureInfo($culture);
    $languages = $cultureInfo->getLanguages();

    // restrict languages to a sub-set
    if (isset($options['languages']))
    {
      if ($problems = array_diff($options['languages'], array_keys($languages)))
      {
        throw new InvalidArgumentException(sprintf('The following languages do not exist: %s.', implode(', ', $problems)));
      }

      $languages = array_intersect_key($languages, array_flip($options['languages']));
    }

    asort($languages);

    $this->setOption('choices', $languages);
  }
}
