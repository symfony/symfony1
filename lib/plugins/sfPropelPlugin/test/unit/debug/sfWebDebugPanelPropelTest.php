<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/../../bootstrap/unit.php');

$t = new lime_test(1);

class sfWebDebugPanelPropelTest extends sfWebDebugPanelPropel
{
  protected function getSlowQueryThreshold()
  {
    return 0.01;
  }
}

// ->getPanelContent()
$t->diag('->getPanelContent()');

$dispatcher = new sfEventDispatcher();
$logger = new sfVarLogger($dispatcher);
$logger->log('{sfPropelLogger} SELECT * FROM foo WHERE bar<1');
$panel = new sfWebDebugPanelPropelTest(new sfWebDebug($dispatcher, $logger));

$t->ok(false !== strpos($panel->getPanelContent(), 'bar&lt;1'), '->getPanelContent() returns escaped queries');
