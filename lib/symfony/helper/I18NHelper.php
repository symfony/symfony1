<?php

function __($text, $culture = null)
{
  return sfContext::getInstance()->getRequest()->getAttribute('gm')->_($text);
}

function format_country($country_iso)
{
  require_once('i18n/CultureInfo.php');

  $c = new CultureInfo(sfContext::getInstance()->getUser()->getCulture());
  $countries = $c->getCountries();

  return $countries[$country_iso];
}

?>