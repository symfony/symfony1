<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');
require_once(dirname(__FILE__).'/../../../lib/util/sfYamlParser.class.php');
require_once(dirname(__FILE__).'/../../../lib/util/sfYamlDumper.class.php');

$t = new lime_test(68, new lime_output_color());

$parser = new sfYamlParser();
$dumper = new sfYamlDumper();

$path = dirname(__FILE__).'/fixtures/yaml';
$files = $parser->parse(file_get_contents($path.'/index.yml'));
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

    $test = $parser->parse($yaml);
    if (isset($test['todo']) && $test['todo'])
    {
      $t->todo($test['test']);
    }
    else
    {
      $expected = eval('return '.trim($test['php']).';');

      $t->is($parser->parse($dumper->dump($expected, 10)), $expected, $test['test'].' (dumper)');
    }
  }
}

// inline level
$array = array(
  '' => 'bar',
  'foo\'bar' => array(),
  'bar' => array(1, 'foo'),
  'foobar' => array(
    'foo' => 'bar',
    'bar' => array(1, 'foo'),
    'foobar' => array(
      'foo' => 'bar',
      'bar' => array(1, 'foo'),
    ),
  ),
);

$expected = <<<EOF
{ '': bar, 'foo''bar': {  }, bar: [1, foo], foobar: { foo: bar, bar: [1, foo], foobar: { foo: bar, bar: [1, foo] } } }
EOF;
$t->is(sfYaml::dump($array, -10), $expected, '::dump() takes an inline level argument');
$t->is(sfYaml::dump($array, 0), $expected, '::dump() takes an inline level argument');

$expected = <<<EOF
'': bar
'foo''bar': {  }
bar: [1, foo]
foobar: { foo: bar, bar: [1, foo], foobar: { foo: bar, bar: [1, foo] } }

EOF;
$t->is(sfYaml::dump($array, 1), $expected, '::dump() takes an inline level argument');

$expected = <<<EOF
'': bar
'foo''bar': {  }
bar:
  - 1
  - foo
foobar:
  foo: bar
  bar: [1, foo]
  foobar: { foo: bar, bar: [1, foo] }

EOF;
$t->is(sfYaml::dump($array, 2), $expected, '::dump() takes an inline level argument');

$expected = <<<EOF
'': bar
'foo''bar': {  }
bar:
  - 1
  - foo
foobar:
  foo: bar
  bar:
    - 1
    - foo
  foobar:
    foo: bar
    bar: [1, foo]

EOF;
$t->is(sfYaml::dump($array, 3), $expected, '::dump() takes an inline level argument');

$expected = <<<EOF
'': bar
'foo''bar': {  }
bar:
  - 1
  - foo
foobar:
  foo: bar
  bar:
    - 1
    - foo
  foobar:
    foo: bar
    bar:
      - 1
      - foo

EOF;
$t->is(sfYaml::dump($array, 4), $expected, '::dump() takes an inline level argument');
$t->is(sfYaml::dump($array, 10), $expected, '::dump() takes an inline level argument');
