<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(52, new lime_output_color());

// __construct()
$t->diag('__construct()');
$c = new sfCultureInfo();
$t->is($c->getName(), 'en', '->__construct() returns an object with "en" as the default culture');
$c = new sfCultureInfo('fr');
$t->is($c->getName(), 'fr', '->__construct() takes a culture as its first argument');
$c = new sfCultureInfo('');
$t->is($c->getName(), 'en', '->__construct() returns an object with "en" as the default culture');

// __toString()
$t->diag('__toString()');
$c = new sfCultureInfo();
$t->is($c->__toString(), 'en', '->__toString() returns the name of the culture');

try
{
  $c = new sfCultureInfo('xxx');
  $t->fail('->__construct() throws an exception if the culture is not valid');
}
catch (sfException $e)
{
  $t->pass('->__construct() throws an exception if the culture is not valid');
}

$c_en = new sfCultureInfo();
$c_fr = new sfCultureInfo('fr');

// ->getLanguages()
$t->diag('->getLanguages()');
$languages_en = $c_en->getLanguages();
$languages_fr = $c_fr->getLanguages();
$t->is($languages_en['fr'], 'French', '->getLanguages() returns a list of languages in the language of the localized version');
$t->is($languages_fr['fr'], 'français', '->getLanguages() returns a list of languages in the language of the localized version');
$t->is($languages_en, $c_en->Languages, '->getLanguages() is equivalent to ->Languages');

// ->getCurrencies()
$t->diag('->getCurrencies()');
$currencies_en = $c_en->getCurrencies();
$currencies_fr = $c_fr->getCurrencies();
$t->is($currencies_en['EUR'][1], 'Euro', '->getCurrencies() returns a list of currencies in the language of the localized version');
$t->is($currencies_fr['EUR'][1], 'euro', '->getCurrencies() returns a list of currencies in the language of the localized version');
$t->is($currencies_en, $c_en->Currencies, '->getCurrencies() is equivalent to ->Currencies');

// ->getCountries()
$t->diag('->getCountries()');
$countries_en = $c_en->getCountries();
$countries_fr = $c_fr->getCountries();
$t->is($countries_en['ES'], 'Spain', '->getCountries() returns a list of countries in the language of the localized version');
$t->is($countries_fr['ES'], 'Espagne', '->getCountries() returns a list of countries in the language of the localized version');
$t->is($countries_en, $c_en->Countries, '->getCountries() is equivalent to ->Countries');

// ->getScripts()
$t->diag('->getScripts()');
$scripts_en = $c_en->getScripts();
$scripts_fr = $c_fr->getScripts();
$t->is($scripts_en['Arab'], 'Arabic', '->getScripts() returns a list of scripts in the language of the localized version');
$t->is($scripts_fr['Arab'], 'arabe', '->getScripts() returns a list of scripts in the language of the localized version');
$t->is($scripts_en, $c_en->Scripts, '->getScripts() is equivalent to ->Scripts');

// ->getTimeZones()
$t->diag('->getTimeZones()');
$time_zones_en = $c_en->getTimeZones();
$time_zones_fr = $c_fr->getTimeZones();
$t->is($time_zones_en[1][0], 'America/Los_Angeles', '->getTimeZones() returns a list of time zones in the language of the localized version');
$t->is($time_zones_fr[1][0], 'America/Vancouver', '->getTimeZones() returns a list of time zones in the language of the localized version');
$t->is($time_zones_en, $c_en->TimeZones, '->getTimeZones() is equivalent to ->TimeZones');

// ->validCulture()
$t->diag('->validCulture()');
$t->is($c->validCulture('fr'), true, '->validCulture() returns true if the culture is valid');
$t->is($c->validCulture('fr_FR'), true, '->validCulture() returns true if the culture is valid');
foreach (array('xxx', 'pp', 'frFR') as $culture)
{
  $t->is($c->validCulture($culture), false, '->validCulture() returns false if the culture does not exist');
}

// ::getCultures()
$t->diag('::getCultures()');
$cultures = sfCultureInfo::getCultures();
$t->is(in_array('fr', $cultures), true, '::getCultures() returns an array of all available cultures');
$t->is(in_array('fr_FR', $cultures), true, '::getCultures() returns an array of all available cultures');

$cultures = sfCultureInfo::getCultures(sfCultureInfo::NEUTRAL);
$t->is(in_array('fr', $cultures), true, '::getCultures() returns an array of all available cultures');
$t->is(in_array('fr_FR', $cultures), false, '::getCultures() returns an array of all available cultures');

$cultures = sfCultureInfo::getCultures(sfCultureInfo::SPECIFIC);
$t->is(in_array('fr', $cultures), false, '::getCultures() returns an array of all available cultures');
$t->is(in_array('fr_FR', $cultures), true, '::getCultures() returns an array of all available cultures');

// ->getParent()
$t->diag('->getParent()');
$c = new sfCultureInfo('fr_FR');
$t->isa_ok($c->getParent(), 'sfCultureInfo', '->getParent() returns a sfCultureInfo instance');
$t->is($c->getParent()->getName(), 'fr', '->getParent() returns the parent culture');
$c = new sfCultureInfo('fr');
$t->is($c->getParent()->getName(), 'en', '->getParent() returns the invariant culture if the culture is neutral');

// ->getIsNeutralCulture()
$t->diag('->getIsNeutralCulture()');
$c = new sfCultureInfo('fr_FR');
$t->is($c->getIsNeutralCulture(), false, '->getIsNeutralCulture() returns false if the culture is specific');
$c = new sfCultureInfo('fr');
$t->is($c->getIsNeutralCulture(), true, '->getIsNeutralCulture() returns true if the culture is neutral');

// ->getEnglishName()
$t->diag('->getEnglishName()');
$c = new sfCultureInfo('fr_FR');
$t->is($c->getEnglishName(), 'French (France)', '->getEnglishName() returns the english name of the current culture');
$c = new sfCultureInfo('fr');
$t->is($c->getEnglishName(), 'French', '->getEnglishName() returns the english name of the current culture');
$t->is($c->getEnglishName(), $c->EnglishName, '->getEnglishName() is equivalent to ->EnglishName');

// ->getNativeName()
$t->diag('->getNativeName()');
$c = new sfCultureInfo('fr_FR');
$t->is($c->getNativeName(), 'français (France)', '->getNativeName() returns the native name of the current culture');
$c = new sfCultureInfo('fr');
$t->is($c->getNativeName(), 'français', '->getNativeName() returns the native name of the current culture');
$t->is($c->getNativeName(), $c->NativeName, '->getNativeName() is equivalent to ->NativeName');

// ->getCalendar()
$t->diag('->getCalendar()');
$c = new sfCultureInfo('fr');
$t->is($c->getCalendar(), 'gregorian', '->getCalendar() returns the default calendar');
$t->is($c->getCalendar(), $c->Calendar, '->getCalendar() is equivalent to ->Calendar');

// __get()
$t->diag('__get()');
try
{
  $c->NonExistant;
  $t->fail('__get() throws an exception if the property does not exist');
}
catch (sfException $e)
{
  $t->pass('__get() throws an exception if the property does not exist');
}

// __set()
$t->diag('__set()');
try
{
  $c->NonExistant = 12;
  $t->fail('__set() throws an exception if the property does not exist');
}
catch (sfException $e)
{
  $t->pass('__set() throws an exception if the property does not exist');
}

// ->getDateTimeFormat()
$t->diag('->getDateTimeFormat()');
$c = new sfCultureInfo();
$t->isa_ok($c->getDateTimeFormat(), 'sfDateTimeFormatInfo', '->getDateTimeFormat() returns a sfDateTimeFormatInfo instance');

// ->setDateTimeFormat()
$t->diag('->setDateTimeFormat()');
$d = $c->getDateTimeFormat();
$c->setDateTimeFormat('yyyy');
$t->is($c->getDateTimeFormat(), 'yyyy', '->setDateTimeFormat() sets the sfDateTimeFormatInfo instance');
$c->DateTimeFormat = 'mm';
$t->is($c->getDateTimeFormat(), 'mm', '->setDateTimeFormat() is equivalent to ->DateTimeFormat = ');

// ->getNumberFormat()
$t->diag('->getNumberFormat()');
$c = new sfCultureInfo();
$t->isa_ok($c->getNumberFormat(), 'sfNumberFormatInfo', '->getNumberFormat() returns a sfNumberFormatInfo instance');

// ->setNumberFormat()
$t->diag('->setNumberFormat()');
$d = $c->getNumberFormat();
$c->setNumberFormat('.');
$t->is($c->getNumberFormat(), '.', '->setNumberFormat() sets the sfNumberFormatInfo instance');
$c->NumberFormat = '#';
$t->is($c->getNumberFormat(), '#', '->setNumberFormat() is equivalent to ->NumberFormat = ');
