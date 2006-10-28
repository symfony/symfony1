<?php

/**
 * cache actions.
 *
 * @package    project
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class cacheActions extends sfActions
{
  public function executeIndex()
  {
  }

  public function executePage()
  {
  }

  public function executeForward()
  {
    $this->forward('cache', 'page');
  }

  public function executeMulti()
  {
  }

  public function executeMultiBis()
  {
  }

  public function executeSpecificCacheKey()
  {
  }

  public function executeAction()
  {
    $response = $this->getResponse();
    $response->setHttpHeader('symfony', 'foo');
    $response->setContentType('text/plain');
    $response->setTitle('My title');
    $response->addMeta('meta1', 'bar');
    $response->addHttpMeta('httpmeta1', 'foobar');

    sfConfig::set('ACTION_EXECUTED', true);
  }
}
