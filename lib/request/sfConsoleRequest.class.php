<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage request
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfConsoleRequest extends sfRequest
{
  /**
   * Initializes this sfRequest.
   *
   * @param sfLogger  A sfLogger instance (can be null)
   * @param sfRouting A sfRouting instance (can be null)
   * @param array     An associative array of initialization parameters
   * @param array     An associative array of initialization attributes
   *
   * @return Boolean true, if initialization completes successfully, otherwise false
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this Request
   */
  public function initialize(sfLogger $logger = null, sfRouting $routing = null, $parameters = array(), $attributes = array())
  {
    parent::initialize($logger, $routing, $parameters, $attributes);

    $this->getParameterHolder()->add($_SERVER['argv']);
  }
}
