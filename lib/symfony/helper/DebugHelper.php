<?php

function debug_message($message)
{
  if (SF_WEB_DEBUG)
  {
    sfWebDebug::getInstance()->logShortMessage($message);
  }
}

function log_message($message, $priority = 'info')
{
  sfContext::getInstance()->getLogger()->log($message, constant('PEAR_LOG_'.strtoupper($priority)));
}

?>