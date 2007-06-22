<?php

/**
 * i18n actions.
 *
 * @package    project
 * @subpackage i18n
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class i18nActions extends sfActions
{
  public function executeIndex()
  {
    $i18n = $this->getContext()->getI18N();

    $this->test = $i18n->__('an english sentence');
    $this->localTest = $i18n->__('a local english sentence');
    $this->otherTest = $i18n->__('an english sentence', array(), 'other');
    $this->otherLocalTest = $i18n->__('a local english sentence', array(), 'other');
  }
}
