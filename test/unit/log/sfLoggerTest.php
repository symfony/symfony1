<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(136, new lime_output_color());

class myLogger
{
  public $log = '';

  public function log($message, $priority)
  {
    $this->log .= $message;
  }
}

$myLogger = new myLogger();

// ->getInstance()
$t->diag('->getInstance()');
$t->isa_ok(sfLogger::getInstance(), 'sfLogger', '::getInstance() returns a sfLogger instance');
$t->is(sfLogger::getInstance(), sfLogger::getInstance(), '::getInstance() is a singleton');

$logger = sfLogger::getInstance();

// ->getLoggers()
$t->diag('->getLoggers()');
$t->is($logger->getLoggers(), array(), '->getLoggers() returns an array of registered loggers');

// ->registerLogger()
$t->diag('->registerLogger()');
$logger->registerLogger($myLogger);
$t->is($logger->getLoggers(), array($myLogger), '->registerLogger() registers a new logger instance');

// ->initialize()
$t->diag('->initialize()');
$logger->initialize();
$t->is($logger->getLoggers(), array(), '->initialize() initializes the logger and clears all current registered loggers');

// ->setLogLevel() ->getLogLevel()
$t->diag('->setLogLevel() ->getLogLevel()');
$t->is($logger->getLogLevel(), SF_LOG_EMERG, '->getLogLevel() gets the current log level');
$logger->setLogLevel(SF_LOG_WARNING);
$t->is($logger->getLogLevel(), SF_LOG_WARNING, '->setLogLevel() sets the log level');

// ->log()
$t->diag('->log()');
$logger->initialize();
$logger->setLogLevel(SF_LOG_DEBUG);
$logger->registerLogger($myLogger);
$logger->registerLogger($myLogger);
$logger->log('message');
$t->is($myLogger->log, 'messagemessage', '->log() calls all registered loggers');

// log level
$t->diag('log levels');
foreach (array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug') as $level)
{
  $levelConstant = 'SF_LOG_'.strtoupper($level);

  foreach (array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug') as $logLevel)
  {
    $logLevelConstant = 'SF_LOG_'.strtoupper($logLevel);
    $logger->setLogLevel(constant($logLevelConstant));

    $myLogger->log = '';
    $logger->log('foo', constant($levelConstant));

    $t->is($myLogger->log, constant($logLevelConstant) >= constant($levelConstant), sprintf('->log() only logs if the level is >= to the defined log level (%s >= %s)', $logLevelConstant, $levelConstant));
  }
}

// shortcuts
$t->diag('log shortcuts');
foreach (array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug') as $level)
{
  $levelConstant = 'SF_LOG_'.strtoupper($level);

  foreach (array('emerg', 'alert', 'crit', 'err', 'warning', 'notice', 'info', 'debug') as $logLevel)
  {
    $logger->setLogLevel(constant('SF_LOG_'.strtoupper($logLevel)));

    $myLogger->log = '';
    $logger->log('foo', constant($levelConstant));
    $log1 = $myLogger->log;

    $myLogger->log = '';
    $logger->$level('foo');
    $log2 = $myLogger->log;

    $t->is($log1, $log2, sprintf('->%s($msg) is a shortcut for ->log($msg, %s)', $level, $levelConstant));
  }
}
