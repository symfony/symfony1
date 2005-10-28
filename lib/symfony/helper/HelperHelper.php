<?php

function use_helper($helperName)
{
  if (is_readable(SF_SYMFONY_LIB_DIR.'/symfony/helper/'.$helperName.'Helper.php'))
  {
    include_once('symfony/helper/'.$helperName.'Helper.php');
  }
  else
  {
    include_once('helper/'.$helperName.'Helper.php');
  }
}

?>