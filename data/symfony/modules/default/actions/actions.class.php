<?php

// +---------------------------------------------------------------------------+
// | This file is part of the SymFony Framework package.                        |
// | Copyright (c) 2004, 2005 Fabien POTENCIER.                                          |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.mojavi.org.                             |
// +---------------------------------------------------------------------------+

/**
 *
 *     SymFony
 *
 * @author    Fabien POTENCIER (fabien.potencier@gmail.com)
 *  (c) Fabien POTENCIER
 * @since     1.0.0
 * @version   $Id: actions.class.php 501 2005-10-06 15:56:03Z fabien $
 */

class defaultActions extends sfActions
{
  public function executeUnavailable()
  {
    $this->hasLayout(false);
  }

  public function executeIndex()
  {
  }

  public function executeError404()
  {
  }

  public function executeLogin()
  {
  }

  public function executeDisabled()
  {
  }

  public function executeSecure()
  {
  }
}

?>