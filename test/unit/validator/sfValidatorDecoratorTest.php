<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(15, new lime_output_color());

class MyValidator extends sfValidatorDecorator
{
  protected function getValidator()
  {
    return new sfValidatorString(array('min_length' => 2, 'trim' => true), array('required' => 'This string is required.'));
  }
}

// __construct()
$t->diag('__construct()');
$v = new MyValidator(array('required' => false));
$t->is($v->clean(null), null, '__construct() options override the embedded validator options');
$v = new MyValidator(array(), array('required' => 'This is required.'));
try
{
  $v->clean(null);
  $t->fail('->clean() throws a sfValidatorError if the value is required');
  $t->skip();
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() throws a sfValidatorError if the value is required');
  $t->is($e->getMessage(), 'This is required.', '__construct() messages override the embedded validator messages');
}

// ->getErrorCodes()
$t->diag('->getErrorCodes()');
$t->is($v->getErrorCodes(), array('required', 'invalid', 'max_length', 'min_length'), '->getErrorCodes() returns error codes form the embedded validator');

// ->getMessage() ->getMessages() ->setMessage() ->setMessages()
$t->diag('->getMessage() ->getMessages() ->setMessage() ->setMessages()');
$v = new MyValidator();
$t->is($v->getMessage('required'), 'This string is required.', '->getMessage() returns a message from the embedded validator');
$v->setMessage('invalid', 'This string is invalid.');
$t->is($v->getMessages(), array('required' => 'This string is required.', 'invalid' => 'This string is invalid.', 'max_length' => '"%value%" is too long (%max_length% characters max).', 'min_length' => '"%value%" is too short (%min_length% characters min).'), '->getMessages() returns messages from the embedded validator');
$v->setMessages(array('required' => 'Required...'));
$t->is($v->getMessages(), array('required' => 'Required...'), '->setMessages() sets all messages for the embedded validator');

// ->getOption() ->getOptions() ->hasOption() ->getOptions() ->setOptions()
$v = new MyValidator();
$t->is($v->getOption('trim'), true, '->getOption() returns an option from the embedded validator');
$v->setOption('trim', false);
$t->is($v->getOptions(), array('required' => true, 'trim' => false, 'empty_value' => '', 'min_length' => 2), '->getOptions() returns an array of options from the embedded validator');
$t->is($v->hasOption('min_length'), true, '->hasOption() returns true if the embedded validator has a given option');
$v->setOptions(array('min_length' => 10));
$t->is($v->getOptions(), array('min_length' => 10), '->setOptions() sets all options for the embedded validator');

$v = new MyValidator();

// ->clean()
$t->diag('->clean()');
try
{
  $v->clean(null);
  $t->fail('->clean() throws a sfValidatorError if the value is required');
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() throws a sfValidatorError if the value is required');
}

try
{
  $v->clean('f');
  $t->fail('->clean() throws a sfValidatorError if the wrapped validator failed');
}
catch (sfValidatorError $e)
{
  $t->pass('->clean() throws a sfValidatorError if the wrapped validator failed');
}

$t->is($v->clean('  foo  '), 'foo', '->clean() cleans the value by executing the clean() method from the wrapped validator');

class FakeValidator extends sfValidatorDecorator
{
  protected function getValidator()
  {
    return 'foo';
  }
}

try
{
  $v = new FakeValidator();
  $t->fail('->clean() throws a sfValidatorError if getValidator() does not return a sfValidator instance');
}
catch (sfException $e)
{
  $t->pass('->clean() throws a sfValidatorError if getValidator() does not return a sfValidator instance');
}
