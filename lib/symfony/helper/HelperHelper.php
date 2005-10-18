<?php

function use_helper($helperName)
{
  if (!@include_once('symfony/helper/'.$helperName.'Helper.php'))
  {
    include_once('helper/'.$helperName.'Helper.php');
  }
}

?>