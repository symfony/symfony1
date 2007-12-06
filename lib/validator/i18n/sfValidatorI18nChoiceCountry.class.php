<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfValidatorI18nChoiceCountry validates than the value is a valid country.
 *
 * @package    symfony
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfValidatorI18nChoiceCountry extends sfValidatorChoice
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * culture:   The culture to use for internationalized strings (required)
   *  * countries: An array of country codes to use (ISO 3166)
   *
   * @see sfValidatorChoice
   */
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->addRequiredOption('culture');
    $this->addOption('countries');

    // populate choices with all countries
    $culture = isset($options['culture']) ? $options['culture'] : 'en';

    $cultureInfo = new sfCultureInfo($culture);
    $countries = array_keys($cultureInfo->getCountries());

    // restrict countries to a sub-set
    if (isset($options['countries']))
    {
      if ($problems = array_diff($options['countries'], $countries))
      {
        throw new InvalidArgumentException(sprintf('The following countries do not exist: %s.', implode(', ', $problems)));
      }

      $countries = $options['countries'];
    }

    sort($countries);

    $this->setOption('choices', $countries);
  }
}
