<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Launches unit tests.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfTestUnitTask.class.php 18731 2009-05-28 12:43:20Z fabien $
 */
abstract class sfTestBaseTask extends sfBaseTask
{
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
