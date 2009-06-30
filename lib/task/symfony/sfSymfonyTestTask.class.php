<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Launches the symfony test suite.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfTestAllTask.class.php 8148 2008-03-29 07:58:59Z fabien $
 */
class sfSymfonyTestTask extends sfTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('update-autoloader', 'u', sfCommandOption::PARAMETER_NONE, 'Update the sfCoreAutoload class'),
      new sfCommandOption('only-failed', 'f', sfCommandOption::PARAMETER_NONE, 'Only run tests that failed last time'),
      new sfCommandOption('xml', null, sfCommandOption::PARAMETER_REQUIRED, 'The file name for the JUnit compatible XML log file'),
    ));

    $this->namespace = 'symfony';
    $this->name = 'test';
    $this->briefDescription = 'Launches the symfony test suite';

    $this->detailedDescription = <<<EOF
The [test:all|INFO] task launches the symfony test suite:

  [./symfony symfony:test|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    require_once(dirname(__FILE__).'/../../vendor/lime/lime.php');
    require_once(dirname(__FILE__).'/lime_symfony.php');

    // cleanup
    require_once(dirname(__FILE__).'/../../util/sfToolkit.class.php');
    if ($files = glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'/sf_autoload_unit_*'))
    {
      foreach ($files as $file)
      {
        unlink($file);
      }
    }

    // update sfCoreAutoload
    if ($options['update-autoloader'])
    {
      require_once(dirname(__FILE__).'/../../autoload/sfCoreAutoload.class.php');
      sfCoreAutoload::make();
    }

    $status = false;
    $statusFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.sprintf('/.test_symfony_%s_status', md5(dirname(__FILE__)));
    if ($options['only-failed'])
    {
      if (file_exists($statusFile))
      {
        $status = unserialize(file_get_contents($statusFile));
      }
    }

    $h = new lime_symfony(array('force_colors' => $options['color']));
    $h->base_dir = realpath(dirname(__FILE__).'/../../../test');

    if ($status)
    {
      foreach ($status as $file)
      {
        $h->register($file);
      }
    }
    else
    {
      $h->register(sfFinder::type('file')->prune('fixtures')->name('*Test.php')->in(array_merge(
        // unit tests
        array($h->base_dir.'/unit'),
        glob($h->base_dir.'/../lib/plugins/*/test/unit'),

        // functional tests
        array($h->base_dir.'/functional'),
        glob($h->base_dir.'/../lib/plugins/*/test/functional'),

        // other tests
        array($h->base_dir.'/other')
      )));
    }

    $ret = $h->run() ? 0 : 1;

    file_put_contents($statusFile, serialize($h->get_failed_files()));

    if ($options['trace'])
    {
      $this->outputHarnessTrace($h);
    }

    if ($options['xml'])
    {
      file_put_contents($options['xml'], $h->to_xml());
    }

    return $ret;
  }

  // master is at sfTestBaseTask
  /**
   * @see sfTestBaseTask::outputHarnessTrace()
   */
  protected function outputHarnessTrace(lime_harness $h)
  {
    $xml = new SimpleXMLElement($h->to_xml());
    foreach ($xml as $testsuite)
    {
      if ($testsuite['failures'])
      {
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
}
