<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2009 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfSwiftMailer Unit Test
 *
 * @package    symfony
 * @subpackage mailer
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: sfSwiftMailerTest.php 12479 2008-10-31 10:54:40Z dwhittle $
 */
require_once(dirname(__FILE__) . '/../../../../../../test/bootstrap/unit.php');

$t = new lime_test(12, new lime_output_color());

// autoloading for tests - normally handled via plugin configuration
$autoload = sfSimpleAutoload::getInstance();
$autoload->addDirectory(realpath(dirname(__FILE__).'/../../../lib'));
$autoload->register();

$dispatcher = new sfEventDispatcher();
$swift = new Swift(new Swift_Connection_NativeMail(), 'localhost.localdomain', Swift::ENABLE_LOGGING | Swift::NO_START);
$options = array('logging'       => false,
                 'from_email'    => 'webmaster@localhost.localdomain',
                 'domain'        => 'localhost.localdomain',
                 'cache'         => 'memory',
                 'charset'       => 'utf8',
                 'culture'       => 'en');
$mailer = new sfSwiftMailer($dispatcher, $swift, $options);

$t->is(($mailer instanceof sfMailer), true, '->__construct() takes an event dispatcher and swift object');
$t->is($dispatcher->hasListeners('email.send'), true, '->initialize() listens to email.send');
$t->is($dispatcher->hasListeners('user.change_culture'), true, '->initialize() listens to user.change_culture');

// check option handling
$t->is($mailer->getOptions(), array('auto_shutdown' => true,
                                    'logging'       => false,
                                    'from_email'    => 'webmaster@localhost.localdomain',
                                    'domain'        => 'localhost.localdomain',
                                    'cache'         => 'memory',
                                    'charset'       => 'utf8',
                                    'culture'       => 'en'), '->getOptions() returns all options');

$t->is($mailer->getOption('culture'), 'en', '->getOption() returns an option given name');
$mailer->setOption('charset', 'utf8');
$t->is($mailer->getOption('charset'), 'utf8', '->setOption() sets an option given name and value');
$t->is(($mailer->getEventDispatcher() instanceof sfEventDispatcher), true, '->getEventDispatcher() returns an instance of sfEventDispatcher');

// check swift
$t->is(($mailer->getSwift() instanceof Swift), true, '->getSwift() returns an instance of Swift');
$t->is(($mailer->getSwift()->connection instanceof Swift_Connection), true, '->getSwift()->connection returns an instance of Swift_Connection');
$t->is(method_exists($mailer->getSwift(), 'send'), true, '->getSwift()->send() exists');

// check email sending (with default config)
$t->is($mailer->send('test@example.com', 'my test subject', 'my test message'), true, '->send() returns true if email is sent.');

// check user.change_culture listener
$dispatcher->notify(new sfEvent($mailer, 'user.change_culture', array('culture' => 'fr')));
$t->is($mailer->getOption('culture'), 'fr', '->listenToUserChangeCulture() listens to user.change_culture event');

