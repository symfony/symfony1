<?php

class sfOptimizer
{
  protected
    $source = '',
    $optimizers = array();

  public function initialize($source)
  {
    $this->source = $source;
    $this->optimizers = array();
  }

  public function registerOptimizer($callable)
  {
    $this->optimizers[] = $callable;
  }

  public function registerStandardOptimizers()
  {
    $optimizers = array('constant', 'condition', 'comment', 'whitespace');
    foreach ($optimizers as $optimizer)
    {
      $class = 'sf'.ucfirst($optimizer).'Optimizer';
      require_once(dirname(__FILE__).'/'.$class.'.class.php');
      $o = new $class();
      if (!method_exists($o, 'optimize'))
      {
        throw new Exception(sprintf('Optimizer "%s" does not have an optimize method', $class));
      }
      $this->optimizers[] = array($o, 'optimize');
    }
  }

  public function optimize()
  {
    $tokens = token_get_all($this->source);

    foreach ($this->optimizers as $optimizer)
    {
      $tokens = call_user_func($optimizer, $tokens);
    }

    return self::tokensToPhp($tokens);
  }

  static public function tokensToPhp($tokens)
  {
    $output = '';
    foreach ($tokens as $token)
    {
      $output .= is_string($token) ? $token : $token[1];
    }

    return $output;
  }
}
