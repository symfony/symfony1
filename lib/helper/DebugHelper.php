<?php

function debug_message($message)
{
  if (sfConfig::get('sf_web_debug'))
  {
    sfContext::getInstance()->get('sf_web_debug')->logShortMessage($message);
  }
}

function log_message($message, $priority = 'info')
{
  if (sfConfig::get('sf_logging_enabled'))
  {
    sfContext::getInstance()->getLogger()->log($message, constant('sfLogger::'.strtoupper($priority)));
  }
}
