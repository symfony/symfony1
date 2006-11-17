<?php

/**
 * sfI18NPlugin actions.
 *
 * @package    project
 * @subpackage i18n
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfI18NPluginActions extends sfActions
{
  public function executeIndex()
  {
    $this->test = $this->getContext()->getI18N()->__('an english sentence');

    $this->localTest = $this->getContext()->getI18N()->__('a local english sentence');
  }
}
