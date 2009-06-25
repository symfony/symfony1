<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(9);

$source = <<<EOF
<?php

class Foo
{
  function foo()
  {
    // some code
  }
}
EOF;

$sourceWithCodeBefore = <<<EOF
<?php

class Foo
{
  function foo()
  {
    // code before
    // some code
  }
}
EOF;

$sourceWithCodeAfter = <<<EOF
<?php

class Foo
{
  function foo()
  {
    // some code
    // code after
  }
}
EOF;

$sourceWithCodeBeforeAndAfter = <<<EOF
<?php

class Foo
{
  function foo()
  {
    // code before
    // some code
    // code after
  }
}
EOF;

// ->wrapMethod()
$t->diag('->wrapMethod()');
$m = new sfClassManipulator($source);
$t->is($m->wrapMethod('bar', '// code before', '// code after'), $source, '->wrapMethod() does nothing if the method does not exist.');
$m = new sfClassManipulator($source);
$t->is($m->wrapMethod('foo', '// code before'), $sourceWithCodeBefore, '->wrapMethod() adds code before the beginning of a method.');
$m = new sfClassManipulator($source);
$t->is($m->wrapMethod('foo', '', '// code after'), $sourceWithCodeAfter, '->wrapMethod() adds code after the end of a method.');
$t->is($m->wrapMethod('foo', '// code before'), $sourceWithCodeBeforeAndAfter, '->wrapMethod() adds code to the previously manipulated code.');

// ->getCode()
$t->diag('->getCode()');
$m = new sfClassManipulator($source);
$t->is($m->getCode(), $source, '->getCode() returns the source code when no manipulations has been done');
$m->wrapMethod('foo', '', '// code after');
$t->is($m->getCode(), $sourceWithCodeAfter, '->getCode() returns the modified code');

// ->setFile() ->getFile()
$t->diag('->setFile() ->getFile()');
$m = new sfClassManipulator($source);
$m->setFile('foo');
$t->is($m->getFile(), 'foo', '->setFile() sets the name of the file associated with the source code');

// ::fromFile()
$t->diag('::fromFile()');
$file = sys_get_temp_dir().'/sf_tmp.php';
file_put_contents($file, $source);
$m = sfClassManipulator::fromFile($file);
$t->is($m->getFile(), $file, '::fromFile() sets the file internally');

// ->save()
$t->diag('->save()');
$m = sfClassManipulator::fromFile($file);
$m->wrapMethod('foo', '', '// code after');
$m->save();
$t->is(file_get_contents($file), $sourceWithCodeAfter, '->save() saves the modified code if a file is associated with the instance');

unlink($file);
