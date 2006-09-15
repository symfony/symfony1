<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('SF_ROOT_DIR',    realpath(dirname(__FILE__).'/fixtures/project'));
define('SF_APP',         $app);
define('SF_ENVIRONMENT', 'test');
define('SF_DEBUG',       true);

$sf_symfony_lib_dir = realpath(dirname(__FILE__).'/../../lib');
$sf_symfony_data_dir = realpath(dirname(__FILE__).'/../../data');

require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');
