<?php

class i18nConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    $this->enablePlugin('sfI18NPlugin');
  }
}
