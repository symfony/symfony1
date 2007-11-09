<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(10, new lime_output_color());

class MyFormatter extends sfWidgetFormSchemaFormatter
{
  protected
    $rowFormat       = "<li>\n  %error%%label%\n  %field%%help%\n%hidden_fields%</li>\n",
    $errorRowFormat  = "<li>\n%errors%</li>\n",
    $decoratorFormat = "<ul>\n  %content%</ul>";

  public function unnestErrors($errors, $prefix = '')
  {
    return parent::unnestErrors($errors, $prefix);
  }
}

$f = new MyFormatter();

// ->formatRow()
$t->diag('->formatRow()');
$output = <<<EOF
<li>
  label
  <input />help
</li>

EOF;
$t->is($f->formatRow('label', '<input />', array(), 'help', ''), $output, '->formatRow() formats a field in a row');

// ->formatErrorRow()
$t->diag('->formatErrorRow()');
$output = <<<EOF
<li>
  <ul class="error_list">
    <li>Global error</li>
    <li>id: required</li>
    <li>1 > sub_id: required</li>
  </ul>
</li>

EOF;
$t->is($f->formatErrorRow(array('Global error', 'id' => 'required', array('sub_id' => 'required'))), $output, '->formatErrorRow() formats an array of errors in a row');

// ->unnestErrors()
$t->diag('->unnestErrors()');
$f->setErrorRowFormatInARow("<li>%error%</li>");
$f->setNamedErrorRowFormatInARow("<li>%name%: %error%</li>");
$errors = array('foo', 'bar', 'foobar' => 'foobar');
$t->is($f->unnestErrors($errors), array('<li>foo</li>', '<li>bar</li>', '<li>foobar: foobar</li>'), '->unnestErrors() returns an array of formatted errors');
$errors = array('foo', 'bar' => array('foo', 'foobar' => 'foobar'));
$t->is($f->unnestErrors($errors), array('<li>foo</li>', '<li>foo</li>', '<li>bar > foobar: foobar</li>'), '->unnestErrors() unnests errors');

foreach (array('RowFormat', 'ErrorRowFormat', 'ErrorListFormatInARow', 'ErrorRowFormatInARow', 'NamedErrorRowFormatInARow', 'DecoratorFormat') as $method)
{
  $getter = sprintf('get%s', $method);
  $setter = sprintf('set%s', $method);
  $t->diag(sprintf('->%s() ->%s()', $getter, $setter));
  $f->$setter($value = rand(1, 99999));
  $t->is($f->$getter(), $value, sprintf('->%s() ->%s()', $getter, $setter));
}
