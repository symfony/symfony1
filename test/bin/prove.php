<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../lib/vendor/lime/lime.php');

class lime_symfony extends lime_harness
{
  protected function get_relative_file($file)
  {
    $file = str_replace(DIRECTORY_SEPARATOR, '/', str_replace(array(
      realpath($this->base_dir).DIRECTORY_SEPARATOR,
      realpath($this->base_dir.'/../lib/plugins').DIRECTORY_SEPARATOR,
      $this->extension,
    ), '', $file));

    return preg_replace('#^(.*?)Plugin/test/(unit|functional)/#', '[$1] $2/', $file);
  }
}

$h = new lime_symfony(new lime_output_color());

$h->base_dir = realpath(dirname(__FILE__).'/..');
require_once(dirname(__FILE__).'/../../lib/util/sfSimpleAutoload.class.php');
require_once(dirname(__FILE__).'/../../lib/util/sfToolkit.class.php');
$autoload = sfSimpleAutoload::getInstance(sfToolkit::getTmpDir().DIRECTORY_SEPARATOR.sprintf('sf_autoload_unit_%s.data', md5(__FILE__)));
$autoload->removeCache();

// cache autoload files
require_once($h->base_dir.'/bootstrap/unit.php');

$h->register(sfFinder::type('file')->prune('fixtures')->name('*Test.php')->in(array_merge(
  // unit tests
  array($h->base_dir.'/unit'),
  glob($h->base_dir.'/../lib/plugins/*/test/unit'),

  // functional tests
  array($h->base_dir.'/functional'),
  glob($h->base_dir.'/../lib/plugins/*/test/functional'),

  // other tests
  array($h->base_dir.'/other')
)));

$h->run();
