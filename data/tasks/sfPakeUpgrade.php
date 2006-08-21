<?php

pake_desc('upgrade to a new symfony release');
pake_task('upgrade', 'project_exists');

pake_desc('downgrade to a previous symfony release');
pake_task('downgrade', 'project_exists');

function run_upgrade($task, $args)
{
  if (!isset($args[0]))
  {
    throw new Exception('You must provide the upgrade script to use (0.6 to upgrade to 0.6 for example).');
  }

  $version = $args[0];

  throw new Exception('I have no upgrade script for this release.');
}
