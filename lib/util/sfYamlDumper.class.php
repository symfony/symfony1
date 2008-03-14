<?php

require_once(dirname(__FILE__).'/sfYamlInline.class.php');

class sfYamlDumper
{
  /**
   * Dumps a PHP value to YAML.
   *
   * @param  mixed   The PHP value
   * @param  integer The level where you switch to inline YAML
   *
   * @return string  The YAML representation of the PHP value
   */
  public function dump($input, $inline = 0, $indent = 0)
  {
    $output = '';
    $prefix = $indent ? str_repeat(' ', $indent) : '';

    if ($inline <= 0 || !is_array($input) || empty($input))
    {
      $output .= $prefix.sfYamlInline::dump($input);
    }
    else
    {
      $isAHash = count(array_diff_key($input, array_fill(0, count($input), true)));

      foreach ($input as $key => $value)
      {
        $willBeInlined = $inline - 1 <= 0 || !is_array($value) || empty($value);

        $output .= sprintf('%s%s%s%s',
          $prefix,
          $isAHash ? sfYamlInline::dump($key).':' : '-',
          $willBeInlined ? ' ' : "\n",
          $this->dump($value, $inline - 1, $willBeInlined ? 0 : $indent + 2)
        ).($willBeInlined ? "\n" : '');
      }
    }

    return $output;
  }
}
