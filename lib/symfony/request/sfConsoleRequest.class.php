<?php

/**
 * @package    symfony
 * @subpackage request
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @copyright  2004-2005 Fabien Potencier <fabien.potencier@gmail.com>
 * @license    SymFony License 1.0
 * @version    SVN: $Id$
 */

/**
 *
 * @package    symfony
 * @subpackage request
 * @author     Fabien Potencier <fabien.potencier@gmail.com>
 * @copyright  2004-2005 Fabien Potencier <fabien.potencier@gmail.com>
 * @license    SymFony License 1.0
 * @version    SVN: $Id$
 */
class sfConsoleRequest extends sfRequest
{
  /**
   * Initialize this Request.
   *
   * @param sfContext A sfContext instance.
   * @param array   An associative array of initialization parameters.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this Request.
   */
  public function initialize ($context, $parameters = null)
  {
  }

  /**
   * Execute the shutdown procedure.
   *
   * @return void
   */
  public function shutdown ()
  {
  }
}

?>