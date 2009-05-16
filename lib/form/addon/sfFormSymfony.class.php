<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extends the form component with symfony-specific functionality.
 * 
 * @package    symfony
 * @subpackage form
 * @author     Kris Wallsmith <kris.wallsmith@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfFormSymfony extends sfForm
{
  /**
   * Smartly binds request files and parameters.
   * 
   * @param sfWebRequest $request
   */
  public function bindRequest(sfWebRequest $request)
  {
    if ($name = $this->getName())
    {
      $this->bind($request->getParameter($name), $request->getFiles($name));
    }
    else if ($request->isMethod('get'))
    {
      $this->bind($request->getGetParameters());
    }
    else
    {
      $this->bind($request->getPostParameters(), $request->getFiles());
    }
  }
}
