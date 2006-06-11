<?php

function debug_message($message)
{
  if (sfConfig::get('sf_web_debug'))
  {
    sfWebDebug::getInstance()->logShortMessage($message);
  }
}

function log_message($message, $priority = 'info')
{
  sfContext::getInstance()->getLogger()->log($message, constant('SF_PEAR_LOG_'.strtoupper($priority)));
}
