<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @author     Fabien Potencier <fabien.potencier@symfony-project>
 * @version    SVN: $Id$
 */

/**
 * Pakefile.
 *
 * @package    symfony
 * @author     Fabien Potencier <fabien.potencier@symfony-project>
 * @version    SVN: $Id$
 */

pake_import('pear');

pake_desc('launch symfony test suite');
pake_task('alltests');

pake_desc('release a new symfony version');
pake_task('release', 'alltests');

function run_alltests($task, $args)
{
  set_include_path(
    dirname(__FILE__).PATH_SEPARATOR.
    dirname(__FILE__).'/lib'.PATH_SEPARATOR.
    get_include_path()
  );

  define('SF_LOGGING_ACTIVE', false);
  require_once 'symfony/log/Log.class.php';
  require_once 'symfony/log/sfLogger.class.php';

  pake_import('simpletest', false);
  pakeSimpletestTask::call_simpletest($task, 'text');
}

function run_create_pear_package($task, $args)
{
  if (!isset($args[0]))
  {
    throw new Exception('you must provide pake version to release');
  }

  $version    = $args[0];
  $stability  = $args[1];

  // create a pear package
  print 'create pear package for version "'.$version."\"\n";

  try
  {
    @pake_remove('package.xml', getcwd());
  }
  catch (Exception $e)
  {
  }
  pake_copy(getcwd().'/package.xml.tmpl', getcwd().'/package.xml');

  // add class files
  $finder = pakeFinder::type('file')->prune('.svn')->discard('.svn')->relative();
  $xml_classes = '';
  $dirs = array('lib' => 'php', 'data/symfony' => 'data');
  foreach ($dirs as $dir => $role)
  {
    $class_files = $finder->in($dir);
    foreach ($class_files as $file)
    {
      $xml_classes .= '<file role="'.$role.'" baseinstalldir="symfony" install-as="'.$file.'" name="'.$dir.'/'.$file.'" />'."\n";
    }
  }

  // replace tokens
  pake_replace_tokens('package.xml', getcwd(), '##', '##', array(
    'SYMFONY_VERSION' => $version,
    'CURRENT_DATE'    => date('Y-m-d'),
    'CLASS_FILES'     => $xml_classes,
    'STABILITY'       => $stability,
  ));
  pakePearTask::run_pear($task, $args);
  pake_remove('package.xml', getcwd());
}

function run_release($task, $args)
{
  if (!isset($args[0]))
  {
    throw new Exception('you must provide version prefix (0.5 for beta release or 0.6.0 for stable release)');
  }

  if (!isset($args[1]))
  {
    throw new Exception('you must provide stability status (alpha/beta/stable)');
  }

  $stability = $args[1];

  if ($stability == 'beta')
  {
    $version_prefix = $args[0];

    $result = pake_sh('svn status -u '.getcwd());

    if (preg_match('/(\d+)\s*/is', $result, $match))
    {
      $version = $match[1];
    }

    if (!isset($version))
    {
      throw new Exception('unable to find last svn revision');
    }

    // make a PEAR compatible version
    $version = $version_prefix.'.'.$version;
  }
  else
  {
    $version = $args[0];
  }

  if ($task->is_verbose()) print 'releasing symfony version "'.$version."\"\n";

  $args[0] = $version;

  run_create_pear_package($task, $args);

  // copy .tgz as symfony-latest.tgz
  pake_copy(getcwd().'/symfony-'.$version.'.tgz', getcwd().'/symfony-latest.tgz');
}

?>