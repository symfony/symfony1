<?php

class sfConstantOptimizer
{
  public function optimize($tokens)
  {
    $source = sfOptimizer::tokenstoPhp($tokens);

    // booleans
    foreach (array('sf_debug', 'sf_test', 'sf_logging_enabled', 'sf_web_debug', 'sf_cache', 'sf_i18n') as $key)
    {
      $source = str_replace(sprintf('sfConfig::get(\'%s\')', $key), sfConfig::get($key) ? 1 : 0, $source);
    }

    // constant paths
    $source = preg_replace('/sfConfig\:\:get\(\'(.+?)_(dir|dir_name)\'\)/e', '"\'".sfConfig::get("\\1_\\2")."\'";', $source);

    return token_get_all($source);
  }
}
