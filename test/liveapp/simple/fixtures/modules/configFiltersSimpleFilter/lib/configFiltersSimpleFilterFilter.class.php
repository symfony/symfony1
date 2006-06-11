<?php

class configFiltersSimpleFilterFilter extends sfFilter
{
  public function execute ($filterChain)
  {
    echo 'in a filter';

    // execute next filter
    $filterChain->execute();
  }
}
