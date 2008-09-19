<?php

require_once dirname(__FILE__).'/../../../../../../autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->enablePlugins('sfCompat10Plugin');
  }
}
