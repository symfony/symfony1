<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// setup expected test environment (per check_configuration.php)
ini_set('magic_quotes_gpc', 'off');
ini_set('register_globals', 'off');
ini_set('session.auto_start', 'off');
ini_set('arg.output_separator', '&amp;');

ini_set('allow_url_fopen', 'on');

$_test_dir = realpath(dirname(__FILE__).'/..');
require_once($_test_dir.'/../lib/vendor/lime/lime.php');
require_once($_test_dir.'/../lib/config/sfConfig.class.php');
sfConfig::set('sf_symfony_lib_dir', realpath($_test_dir.'/../lib'));

require_once(dirname(__FILE__).'/../../lib/autoload/sfCoreAutoload.class.php');
sfCoreAutoload::register();

require_once(dirname(__FILE__).'/../../lib/util/sfToolkit.class.php');
sfConfig::set('sf_test_cache_dir', sfToolkit::getTmpDir());
