<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(41, new lime_output_color());

$path = dirname(__FILE__).'/fixtures/Spyc';
$files = Spyc::YAMLLoad($path.'/index.yml');
foreach ($files as $file)
{
  $t->diag($file);

  $yamls = file_get_contents($path.'/'.$file.'.yml');

  // split YAMLs documents
  foreach (preg_split('/^---( %YAML\:1\.0)?/m', $yamls) as $yaml)
  {
    if (!$yaml)
    {
      continue;
    }

    $test = Spyc::YAMLLoad($yaml);

    if (isset($test['todo']) && $test['todo'])
    {
      $t->todo($test['test']);
    }
    else
    {
      $t->is(var_export(Spyc::YAMLLoad($test['yaml']), true), var_export(eval('return '.trim($test['php']).';'), true), $test['test']);
    }
  }
}
