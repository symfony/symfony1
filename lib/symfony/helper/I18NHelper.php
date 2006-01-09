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

if (!sfConfig::get('sf_i18n'))
{
  throw new sfConfigurationException('you must set sf_i18n to on in settings.yml to be able to use these helpers.');
}

sfConfig::set('sf_i18n_instance', sfContext::getInstance()->getI18N());

function __($text, $args = array(), $culture = null)
{
  return sfConfig::get('sf_i18n_instance')->__($text, $args);
}

function format_number_choice($text, $args = array(), $number, $culture = null)
{
  $translated = sfConfig::get('sf_i18n_instance')->__($text, $args);

  $choice = new sfChoiceFormat();

  $retval = $choice->format($translated, $number);

  if ($retval === false)
  {
    $error = 'Unable to parse your choice "%s"';
    $error = sprintf($error, $translated);
    throw new sfException($error);
  }

  return $retval;
}

function format_country($country_iso)
{
  $c = new sfCultureInfo(sfContext::getInstance()->getUser()->getCulture());
  $countries = $c->getCountries();

  return $countries[$country_iso];
}

function format_language($language_iso)
{
  $c = new sfCultureInfo(sfContext::getInstance()->getUser()->getCulture());
  $languages = $c->getLanguages();

  return $languages[$language_iso];
}

?>