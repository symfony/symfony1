<?php

/**
 * format actions.
 *
 * @package    project
 * @subpackage format
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class formatActions extends sfActions
{
  static protected $registered = false;

  public function executeIndex($request)
  {
    if (!self::$registered)
    {
      // in real code, register view.configure_format events in ProjectConfiguration or XXXApplicationConfiguration, not here!
      self::$registered = true;
      $this->getContext()->getEventDispatcher()->connect('view.configure_format', array($this, 'configureFormat'));
    }

    if ('xml' == $request->getRequestFormat())
    {
      $this->setLayout('layout');
    }
  }

  public function executeForTheIPhone($request)
  {
    $this->setTemplate('index');
  }

  public function executeJs($request)
  {
    $request->setRequestFormat('js');
  }

  public function executeJsWithAccept()
  {
    $this->setTemplate('index');
  }

  public function configureFormat(sfEvent $event)
  {
    if ('foo' != $event['format'])
    {
      return;
    }

    $event['response']->setHttpHeader('x-foo', 'true');
    $event->getSubject()->setExtension('.php');
  }
}
