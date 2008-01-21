<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../../../lib/vendor/lime/lime.php');
require_once(dirname(__FILE__).'/../../../../lib/view/escaper/sfOutputEscaper.class.php');
require_once(dirname(__FILE__).'/../../../../lib/view/escaper/sfOutputEscaperGetterDecorator.class.php');
require_once(dirname(__FILE__).'/../../../../lib/view/escaper/sfOutputEscaperArrayDecorator.class.php');
require_once(dirname(__FILE__).'/../../../../lib/view/escaper/sfOutputEscaperObjectDecorator.class.php');
require_once(dirname(__FILE__).'/../../../../lib/view/escaper/sfOutputEscaperIteratorDecorator.class.php');
require_once(dirname(__FILE__).'/../../../../lib/view/escaper/sfOutputEscaperSafe.class.php');

require_once(dirname(__FILE__).'/../../../../lib/plugins/sfCompat10Plugin/lib/helper/EscapingHelper.php');
require_once(dirname(__FILE__).'/../../../../lib/config/sfConfig.class.php');

sfConfig::set('sf_charset', 'UTF-8');

$t = new lime_test(21, new lime_output_color());

class OutputEscaperTestClass
{
  public $title = '<strong>escaped!</strong>';

  public function getTitle()
  {
    return $this->title;
  }

  public function getTitleTitle()
  {
    $o = new self;

    return $o->getTitle();
  }
}

class OutputEscaperTestClassChild extends OutputEscaperTestClass
{
}

// ::escape()
$t->diag('::escape()');
$t->diag('::escape() does not escape special values');
$t->ok(sfOutputEscaper::escape('esc_entities', null) === null, '::escape() returns null if the value to escape is null');
$t->ok(sfOutputEscaper::escape('esc_entities', false) === false, '::escape() returns false if the value to escape is false');
$t->ok(sfOutputEscaper::escape('esc_entities', true) === true, '::escape() returns true if the value to escape is true');

$t->diag('::escape() does not escape a value when escaping method is ESC_RAW');
$t->is(sfOutputEscaper::escape('esc_raw', '<strong>escaped!</strong>'), '<strong>escaped!</strong>', '::escape() takes an escaping strategy function name as its first argument');

$t->diag('::escape() escapes strings');
$t->is(sfOutputEscaper::escape('esc_entities', '<strong>escaped!</strong>'), '&lt;strong&gt;escaped!&lt;/strong&gt;', '::escape() returns an escaped string if the value to escape is a string');
$t->is(sfOutputEscaper::escape('esc_entities', '<strong>échappé</strong>'), '&lt;strong&gt;&eacute;chapp&eacute;&lt;/strong&gt;', '::escape() returns an escaped string if the value to escape is a string');

$t->diag('::escape() escapes arrays');
$input = array(
  'foo' => '<strong>escaped!</strong>',
  'bar' => array('foo' => '<strong>escaped!</strong>'),
);
$output = sfOutputEscaper::escape('esc_entities', $input);
$t->isa_ok($output, 'sfOutputEscaperArrayDecorator', '::escape() returns a sfOutputEscaperArrayDecorator object if the value to escape is an array');
$t->is($output['foo'], '&lt;strong&gt;escaped!&lt;/strong&gt;', '::escape() escapes all elements of the original array');
$t->is($output['bar']['foo'], '&lt;strong&gt;escaped!&lt;/strong&gt;', '::escape() is recursive');
$t->is($output->getRawValue(), $input, '->getRawValue() returns the unescaped value');

$t->diag('::escape() escapes objects');
$input = new OutputEscaperTestClass();
$output = sfOutputEscaper::escape('esc_entities', $input);
$t->isa_ok($output, 'sfOutputEscaperObjectDecorator', '::escape() returns a sfOutputEscaperObjectDecorator object if the value to escape is an object');
$t->is($output->getTitle(), '&lt;strong&gt;escaped!&lt;/strong&gt;', '::escape() escapes all methods of the original object');
$t->is($output->title, '&lt;strong&gt;escaped!&lt;/strong&gt;', '::escape() escapes all properties of the original object');
$t->is($output->getTitleTitle(), '&lt;strong&gt;escaped!&lt;/strong&gt;', '::escape() is recursive');
$t->is($output->getRawValue(), $input, '->getRawValue() returns the unescaped value');

$t->is(sfOutputEscaper::escape('esc_entities', $output)->getTitle(), '&lt;strong&gt;escaped!&lt;/strong&gt;', '::escape() does not double escape an object');
$t->isa_ok(sfOutputEscaper::escape('esc_entities', new DirectoryIterator('.')), 'sfOutputEscaperIteratorDecorator', '::escape() returns a sfOutputEscaperIteratorDecorator object if the value to escape is an object that implements the ArrayAccess interface');

$t->diag('::escape() does not escape object marked as being safe');
$t->isa_ok(sfOutputEscaper::escape('esc_entities', new sfOutputEscaperSafe(new OutputEscaperTestClass())), 'OutputEscaperTestClass', '::escape() returns the original value if it is marked as being safe');

sfOutputEscaper::markClassAsSafe('OutputEscaperTestClass');
$t->isa_ok(sfOutputEscaper::escape('esc_entities', new OutputEscaperTestClass()), 'OutputEscaperTestClass', '::escape() returns the original value if the object class is marked as being safe');
$t->isa_ok(sfOutputEscaper::escape('esc_entities', new OutputEscaperTestClassChild()), 'OutputEscaperTestClassChild', '::escape() returns the original value if one of the object parent class is marked as being safe');

$t->diag('::escape() cannot escape resources');
$fh = fopen(__FILE__, 'r');
try
{
  sfOutputEscaper::escape('esc_entities', $fh);
  $t->fail('::escape() throws an InvalidArgumentException if the value cannot be escaped');
}
catch (InvalidArgumentException $e)
{
  $t->pass('::escape() throws an InvalidArgumentException if the value cannot be escaped');
}
