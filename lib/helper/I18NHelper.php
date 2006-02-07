<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * I18NHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

function __($text, $args = array(), $culture = null, $catalogue = 'messages')
{
  if (!sfConfig::get('sf_i18n'))
  {
    throw new sfConfigurationException('you must set sf_i18n to on in settings.yml to be able to use these helpers.');
  }

  return sfConfig::get('sf_i18n_instance')->__($text, $args, $catalogue);
}

function format_number_choice($text, $args = array(), $number, $culture = null, $catalogue = 'messages')
{
  $translated = sfConfig::get('sf_i18n_instance')->__($text, $args, $catalogue);

  $choice = new sfChoiceFormat();

  $retval = $choice->format($translated, $number);

  if ($retval === false)
  {
    $error = sprintf('Unable to parse your choice "%s"', $translated);
    throw new sfException($error);
  }

  return $retval;
}

function format_country($country_iso)
{
  $c = new sfCultureInfo(sfContext::getInstance()->getUser()->getCulture());
  $countries = $c->getCountries();

  return isset($countries[$country_iso]) ? $countries[$country_iso] : '';
}

function format_language($language_iso)
{
  $c = new sfCultureInfo(sfContext::getInstance()->getUser()->getCulture());
  $languages = $c->getLanguages();

  return isset($languages[$language_iso]) ? $languages[$language_iso] : '';
}

?>