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