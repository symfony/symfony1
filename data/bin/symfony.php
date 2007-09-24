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
require_once($sf_symfony_lib_dir.'/log/sfLogger.class.php');
require_once($sf_symfony_lib_dir.'/log/sfConsoleLogger.class.php');
require_once($sf_symfony_lib_dir.'/command/sfCommandLogger.class.php');
require_once($sf_symfony_lib_dir.'/command/sfFormatter.class.php');
require_once($sf_symfony_lib_dir.'/command/sfAnsiColorFormatter.class.php');
require_once($sf_symfony_lib_dir.'/event/sfEvent.class.php');
require_once($sf_symfony_lib_dir.'/event/sfEventDispatcher.class.php');

try
{
  $dispatcher = new sfEventDispatcher();

  $logger = new sfCommandLogger($dispatcher);

  $options = array(
    'symfony_lib_dir' => $sf_symfony_lib_dir,
    'symfony_data_dir' => $sf_symfony_data_dir,
  );

  $application = new sfSymfonyCommandApplication($dispatcher, new sfAnsiColorFormatter(), $options);
  $application->run();
}
catch (Exception $e)
{
  if (!isset($application))
  {
    throw $e;
  }

  $application->renderException($e);

  exit(1);
}

exit(0);
