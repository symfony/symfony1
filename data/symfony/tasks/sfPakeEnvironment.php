<?php

pake_desc('synchronise project with another machine');
pake_task('sync', 'project_exists');

function run_sync($task, $args)
{
  if (!count($args))
  {
    throw new Exception('You must provide an environment to synchronize.');
  }

  $env = $args[0];

  $dryrun = isset($args[1]) ? $args[1] : false;

  if (!file_exists('config/rsync_exclude.txt'))
  {
    throw new Exception('You must create a rsync_exclude file for your project.');
  }

  $host = $task->get_property('host', $env);
  if (!$host)
  {
    throw new Exception('You must set "host" variable in your properties.ini file.');
  }

  $user = $task->get_property('user', $env);
  if (!$user)
  {
    throw new Exception('You must set "user" variable in your properties.ini file.');
  }

  $dir = $task->get_property('dir', $env);
  if (!$dir)
  {
    throw new Exception('You must set "dir" variable in your properties.ini file.');
  }

  if (substr($dir, -1) != '/')
  {
    $dir .= '/';
  }

  $ssh = 'ssh';

  $port = $task->get_property('port', $env);
  if ($port)
  {
    $ssh = '"ssh -p'.$port.'"';
  }

  $dry_run = ($dryrun == 'go' || $dryrun == 'ok') ? '' : '--dry-run';
  $cmd = "rsync --progress $dry_run -azC --exclude-from=config/rsync_exclude.txt --force --delete -e $ssh ./ $user@$host:$dir";

  echo pake_sh($cmd);
}

?>