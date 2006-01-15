<?php

pake_desc('freeze symfony libraries to a PEAR release');
pake_task('freeze');

pake_desc('unfreeze symfony libraries');
pake_task('unfreeze');

function run_freeze($task, $args)
{
  if (!count($args))
  {
    throw new Exception('You must provide the release version you want to freeze (stable or beta or rXXX).');
  }

  $version = strtolower($args[0]);

  // remove current version if present

  $url = 'http://www.symfony-project.com/get/symfony-stable.tgz';
  $svn = 'http://svn.symfony-project.com/';

  // PEAR release or subversion revision?
  if ($version == 'stable')
  {
    
  }
  else if ($version == 'beta')
  {
    
  }
  else if ($version)
  {
    
  }
  else
  {
    throw new Exception('You must provide a release version that I understand! (stable or beta or rXXX).');
  }
}

function run_unfreeze($task, $args)
{
  // remove lib/symfony and data/symfony directories
}

?>