<?php

/**
 * browser actions.
 *
 * @package    project
 * @subpackage browser
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class browserActions extends sfActions
{
  public function executeIndex()
  {
    return $this->renderText('<html><body><h1>html</h1></body></html>');
  }

  public function executeText()
  {
    $this->getResponse()->setContentType('text/plain');
    return $this->renderText('text');
  }
}
<?php

/**
 * browser actions.
 *
 * @package    project
 * @subpackage browser
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class browserActions extends sfActions
{
  public function executeIndex()
  {
    return $this->renderText('<html><body><h1>html</h1></body></html>');
  }

  public function executeText()
  {
    $this->getResponse()->setContentType('text/plain');
    return $this->renderText('text');
  }
}
