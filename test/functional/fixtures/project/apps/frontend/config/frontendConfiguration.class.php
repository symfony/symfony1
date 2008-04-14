<?php

class frontendConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    $this->dispatcher->connect('view.configure_format', 'configure_format_foo');
    $this->dispatcher->connect('request.filter_parameters', 'filter_parameters');
    $this->dispatcher->connect('view.configure_format', 'configure_iphone_format');
  }
}
