<?php

function use_helper($helperName)
{
  if (is_readable(SF_SYMFONY_LIB_DIR.'/symfony/helper/'.$helperName.'Helper.php'))
  {
    // global helper
    include_once('symfony/helper/'.$helperName.'Helper.php');
  }
  else if (is_readable(SF_APP_MODULE_DIR.'/'.sfContext::getInstance()->getModuleName().'/'.SF_APP_MODULE_LIB_DIR_NAME.'/helper/'.$helperName.'Helper.php'))
  {
    // current module helper
    include_once(SF_APP_MODULE_DIR.'/'.sfContext::getInstance()->getModuleName().'/'.SF_APP_MODULE_LIB_DIR_NAME.'/helper/'.$helperName.'Helper.php');
  }
  else
  {
    // helper in include_path
    include_once('helper/'.$helperName.'Helper.php');
  }
}

?>