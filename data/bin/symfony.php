<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!isset($sf_symfony_lib_dir))
{
  die("You must launch symfony command line with the symfony script\n");
}

require_once($sf_symfony_lib_dir.'/command/sfCommandApplication.class.php');
require_once($sf_symfony_lib_dir.'/command/sfSymfonyCommandApplication.class.php');

try
{
  $application = new sfSymfonyCommandApplication();
  $application->initialize($sf_symfony_lib_dir, $sf_symfony_data_dir);
  $application->run();
}
catch (Exception $e)
{
  $application->renderException($e);
  exit(1);
}

exit(0);
