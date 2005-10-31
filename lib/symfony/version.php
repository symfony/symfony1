<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004, 2005 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony.runtime
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

/**
 * Version initialization script.
 *
 * @package    symfony.runtime
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */

define('SF_APP_NAME',          'symfony');
define('SF_APP_MAJOR_VERSION', '1');
define('SF_APP_MINOR_VERSION', '0');
define('SF_APP_MICRO_VERSION', '0');
define('SF_APP_BRANCH',        'dev-1.0.0');
define('SF_APP_STATUS',        'DEV');
define('SF_APP_VERSION',       SF_APP_MAJOR_VERSION.'.'.
                               SF_APP_MINOR_VERSION.'.'.
                               SF_APP_MICRO_VERSION.'-'.SF_APP_STATUS);
define('SF_APP_URL',           'http://www.symfony-project.org');
define('SF_APP_INFO',          SF_APP_NAME.' '.SF_APP_VERSION.' ('.SF_APP_URL.')');

?>