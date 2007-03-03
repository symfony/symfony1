<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

pake_desc('upgrade to a new symfony release');
pake_task('upgrade');

function run_upgrade($task, $args)
{
   throw new Exception('I have no upgrade script for this release.');
}
