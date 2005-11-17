<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
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

function __($text, $args = array(), $culture = null)
{
  if (!SF_IS_I18N)
  {
    throw new sfConfigurationException('you must set is_i18n to "on" in your settings.yml to enable I18N support');
  }

  return sfContext::getInstance()->getRequest()->getAttribute('message_format', null, 'symfony/i18n')->_($text, $args);
}

function format_country($country_iso)
{
  require_once('i18n/CultureInfo.php');

  $c = new CultureInfo(sfContext::getInstance()->getUser()->getCulture());
  $countries = $c->getCountries();

  return $countries[$country_iso];
}

function format_language($language_iso)
{
  require_once('i18n/CultureInfo.php');

  $c = new CultureInfo(sfContext::getInstance()->getUser()->getCulture());
  $languages = $c->getLanguages();

  return $languages[$language_iso];
}

?>