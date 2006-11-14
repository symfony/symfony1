<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(65, new lime_output_color());

// ::stringToArray()
$t->diag('::stringToArray()');
$tests = array(
  'foo=bar' => array('foo' => 'bar'),
  'foo1=bar1 foo=bar   ' => array('foo1' => 'bar1', 'foo' => 'bar'),
  'foo1="bar1 foo1"' => array('foo1' => 'bar1 foo1'),
  'foo1="bar1 foo1" foo=bar' => array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
  'foo1 = "bar1=foo1" foo=bar' => array('foo1' => 'bar1=foo1', 'foo' => 'bar'),
  'foo1= \'bar1 foo1\'    foo  =     bar' => array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
  'foo1=\'bar1=foo1\' foo = bar' => array('foo1' => 'bar1=foo1', 'foo' => 'bar'),
  'foo1=  bar1 foo1 foo=bar' => array('foo1' => 'bar1 foo1', 'foo' => 'bar'),
  'foo1="l\'autre" foo=bar' => array('foo1' => 'l\'autre', 'foo' => 'bar'),
  'foo1="l"autre" foo=bar' => array('foo1' => 'l"autre', 'foo' => 'bar'),
  'foo_1=bar_1' => array('foo_1' => 'bar_1'),
);

foreach ($tests as $string => $attributes)
{
  $t->is(sfToolkit::stringToArray($string), $attributes, '->stringToArray()');
}

// ::isUTF8()
$t->diag('::isUTF8()');
$t->is('été', true, '::isUTF8() returns true if the parameter is an UTF-8 encoded string');
$t->is(sfToolkit::isUTF8('AZERTYazerty1234-_'), true, '::isUTF8() returns true if the parameter is an UTF-8 encoded string');
$t->is(sfToolkit::isUTF8('AZERTYazerty1234-_'.chr(1)), false, '::isUTF8() returns false if the parameter is not an UTF-8 encoded string');

// ::literalize()
$t->diag('::literalize()');
foreach (array('true', 'on', '+', 'yes') as $param)
{
  $t->is(sfToolkit::literalize($param), true, sprintf('::literalize() returns true with "%s"', $param));
  if (strtoupper($param) != $param)
  {
    $t->is(sfToolkit::literalize(strtoupper($param)), true, sprintf('::literalize() returns true with "%s"', strtoupper($param)));
  }
  $t->is(sfToolkit::literalize(' '.$param.' '), true, sprintf('::literalize() returns true with "%s"', ' '.$param.' '));
}

foreach (array('false', 'off', '-', 'no') as $param)
{
  $t->is(sfToolkit::literalize($param), false, sprintf('::literalize() returns false with "%s"', $param));
  if (strtoupper($param) != $param)
  {
    $t->is(sfToolkit::literalize(strtoupper($param)), false, sprintf('::literalize() returns false with "%s"', strtoupper($param)));
  }
  $t->is(sfToolkit::literalize(' '.$param.' '), false, sprintf('::literalize() returns false with "%s"', ' '.$param.' '));
}

foreach (array('null', '~', '') as $param)
{
  $t->is(sfToolkit::literalize($param), null, sprintf('::literalize() returns null with "%s"', $param));
  if (strtoupper($param) != $param)
  {
    $t->is(sfToolkit::literalize(strtoupper($param)), null, sprintf('::literalize() returns null with "%s"', strtoupper($param)));
  }
  $t->is(sfToolkit::literalize(' '.$param.' '), null, sprintf('::literalize() returns null with "%s"', ' '.$param.' '));
}

// ::replaceConstants()
$t->diag('::replaceConstants()');
sfConfig::set('foo', 'bar');
$t->is(sfToolkit::replaceConstants('my value with a %foo% constant'), 'my value with a bar constant', '::replaceConstantsCallback() replaces constants enclosed in %');
$t->is(sfToolkit::replaceConstants('%Y/%m/%d %H:%M'), '%Y/%m/%d %H:%M', '::replaceConstantsCallback() does not replace unknown constants');
sfConfig::set('bar', null);
$t->is(sfToolkit::replaceConstants('my value with a %bar% constant'), 'my value with a  constant', '::replaceConstantsCallback() replaces constants enclosed in % even if value is null');

// ::isPathAbsolute()
$t->diag('::isPathAbsolute()');
$t->is(sfToolkit::isPathAbsolute('/test'), true, '::isPathAbsolute() returns true if path is absolute');
$t->is(sfToolkit::isPathAbsolute('\\test'), true, '::isPathAbsolute() returns true if path is absolute');
$t->is(sfToolkit::isPathAbsolute('C:\\test'), true, '::isPathAbsolute() returns true if path is absolute');
$t->is(sfToolkit::isPathAbsolute('d:/test'), true, '::isPathAbsolute() returns true if path is absolute');
$t->is(sfToolkit::isPathAbsolute('test'), false, '::isPathAbsolute() returns false if path is relative');
$t->is(sfToolkit::isPathAbsolute('../test'), false, '::isPathAbsolute() returns false if path is relative');
$t->is(sfToolkit::isPathAbsolute('..\\test'), false, '::isPathAbsolute() returns false if path is relative');

// ::stripComments()
$t->diag('::stripComments()');

$php = <<<EOF
<?php

# A perl like comment
// Another comment
/* A very long
comment
on several lines
*/

\$i = 1; // A comment on a PHP line
EOF;

$stripped_php = '<?php $i = 1; ';

$t->is(preg_replace('/\s*(\r?\n)+/', ' ', sfToolkit::stripComments($php)), $stripped_php, '::stripComments() strip all comments from a php string');
sfConfig::set('sf_strip_comments', false);
$t->is(sfToolkit::stripComments($php), $php, '::stripComments() do nothing if "sf_strip_comments" is false');

// ::stripslashesDeep()
$t->diag('::stripslashesDeep()');
$t->is(sfToolkit::stripslashesDeep('foo'), 'foo', '::stripslashesDeep() strip slashes on string');
$t->is(sfToolkit::stripslashesDeep(addslashes("foo's bar")), "foo's bar", '::stripslashesDeep() strip slashes on array');
$t->is(sfToolkit::stripslashesDeep(array(addslashes("foo's bar"), addslashes("foo's bar"))), array("foo's bar", "foo's bar"), '::stripslashesDeep() strip slashes on deep arrays');
$t->is(sfToolkit::stripslashesDeep(array(array('foo' => addslashes("foo's bar")), addslashes("foo's bar"))), array(array('foo' => "foo's bar"), "foo's bar"), '::stripslashesDeep() strip slashes on deep arrays');

// ::clearDirectory()
$t->diag('::clearDirectory()');
$tmp_dir = System::tmpdir().DIRECTORY_SEPARATOR.'symfony_tests_'.rand(1, 999);
mkdir($tmp_dir);
file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'test', 'ok');
mkdir($tmp_dir.DIRECTORY_SEPARATOR.'foo');
file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar', 'ok');
sfToolkit::clearDirectory($tmp_dir);
$t->ok(!is_dir($tmp_dir.DIRECTORY_SEPARATOR.'foo'), '::clearDirectory() removes all directories from the directory parameter');
$t->ok(!is_file($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar'), '::clearDirectory() removes all directories from the directory parameter');
$t->ok(!is_file($tmp_dir.DIRECTORY_SEPARATOR.'test'), '::clearDirectory() removes all directories from the directory parameter');
rmdir($tmp_dir);

// ::clearGlob()
$t->diag('::clearGlob()');
$tmp_dir = System::tmpdir().DIRECTORY_SEPARATOR.'symfony_tests_'.rand(1, 999);
mkdir($tmp_dir);
mkdir($tmp_dir.DIRECTORY_SEPARATOR.'foo');
mkdir($tmp_dir.DIRECTORY_SEPARATOR.'bar');
file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar', 'ok');
file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'foo', 'ok');
file_put_contents($tmp_dir.DIRECTORY_SEPARATOR.'bar'.DIRECTORY_SEPARATOR.'bar', 'ok');
sfToolkit::clearGlob($tmp_dir.'/*/bar');
$t->ok(!is_file($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar'), '::clearGlob() removes all files and directories matching the pattern parameter');
$t->ok(!is_file($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'bar'), '::clearGlob() removes all files and directories matching the pattern parameter');
$t->ok(is_file($tmp_dir.DIRECTORY_SEPARATOR.'foo'.DIRECTORY_SEPARATOR.'foo'), '::clearGlob() removes all files and directories matching the pattern parameter');
sfToolkit::clearDirectory($tmp_dir);
rmdir($tmp_dir);
