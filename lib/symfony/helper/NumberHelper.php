<?php

function format_number($number, $culture = null)
{
  require_once 'i18n/NumberFormat.php';
  $numberFormat = new NumberFormat(_current_language($culture));

  return $numberFormat->format($number);
}

function format_currency($amount, $currency = null, $culture = null)
{
  require_once 'i18n/NumberFormat.php';
  $numberFormat = new NumberFormat(_current_language($culture));

  return $numberFormat->format($amount, 'c', $currency);
}

function _current_language($culture)
{
  $context = sfContext::getInstance();
  if (!$culture) $culture = $context->getUser()->getCulture();

  return $culture;
}

?>