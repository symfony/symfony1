<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base test task.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfTestUnitTask.class.php 18731 2009-05-28 12:43:20Z fabien $
 */
abstract class sfTestBaseTask extends sfBaseTask
{
  /**
   * Filters tests through the "task.test.filter_test_files" event.
   * 
   * @param  array $tests     An array of absolute test file paths
   * @param  array $arguments Current task arguments
   * @param  array $options   Current task options
   * 
   * @return array The filtered array of test files
   */
  protected function filterTestFiles($tests, $arguments, $options)
  {
    $event = new sfEvent($this, 'task.test.filter_test_files', array('arguments' => $arguments, 'options' => $options));

    $this->dispatcher->filter($event, $tests);

    return $event->getReturnValue();
  }

  protected function outputHarnessTrace(lime_harness $h)
  {
    $xml = new SimpleXMLElement($h->to_xml());
    foreach ($xml as $testsuite)
    {
      if (!$testsuite['failures'])
      {
        continue;
      }

      $new = true;
      foreach ($testsuite->testcase as $testcase)
      {
        foreach ($testcase->failure as $failure)
        {
          if ($new)
          {
            $this->log('');
            $this->log($this->formatter->format($testsuite['file'], 'ERROR'));
            $new = false;
          }

          $this->log($this->formatter->format(sprintf('  at %s line %s', $testcase['file'], $testcase['line']), 'COMMENT'));
          $this->log($this->formatter->format('  '.$testcase['name'], 'INFO'));
          $this->log($failure);
        }
      }
    }
  }
}
