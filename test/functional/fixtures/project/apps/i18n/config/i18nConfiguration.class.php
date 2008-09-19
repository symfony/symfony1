<?php

class i18nConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    $this->enablePlugins('sfI18NPlugin');
  }
}
