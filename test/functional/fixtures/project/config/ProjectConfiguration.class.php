<?php

require_once dirname(__FILE__).'/../../../../../lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->enablePlugins('sfAutoloadPlugin');
  }
}
