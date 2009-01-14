<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2009 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMailer Unit Test
 *
 * @package    symfony
 * @subpackage mailer
 * @author     Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version    SVN: $Id: sfMailerTest.php 12479 2008-10-31 10:54:40Z dwhittle $
 */
require_once (dirname(__FILE__) . '/../../bootstrap/unit.php');

require_once(dirname(__FILE__).'/../../../lib/mailer/sfMailer.class.php'); 

class sfTestMailer extends sfMailer
{
  public function __construct($options)
  {
    $this->initialize($options);
  }
  
  public function initialize($options)
  {
    $this->options = $options;    
  }
  
  public function send($to, $subject, $body, $attachments = null, $options = null)
  {
    return true;
  }
}

$mailer = new sfTestMailer(array('culture' => 'en'));

$t = new lime_test(4, new lime_output_color());

$t->is($mailer->getOptions(), array('culture' => 'en'), '->getOptions() returns options for mailer');
$t->is($mailer->getOption('culture'), 'en', '->getOption() returns an option given name');
$mailer->setOption('charset', 'utf8');
$t->is($mailer->getOption('charset'), 'utf8', '->setOption() sets an option given name and value');

$t->is(method_exists('sfMailer', 'send'), true, '->send() is defined by sfMailer');
