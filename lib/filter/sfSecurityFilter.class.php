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
 * sfSecurityFilter provides a base class that classifies a filter as one that
 * handles security.
 *
 * @package    symfony
 * @subpackage filter
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
abstract class sfSecurityFilter extends sfFilter
{
  /**
   * Retrieve a new Controller implementation instance.
   *
   * @param string A Controller implementation name.
   *
   * @return Controller A Controller implementation instance.
   *
   * @throws <b>sfFactoryException</b> If a security filter implementation
   *                                 instance cannot be created.
   *
   * @author Sean Kerr (skerr@mojavi.org)
   * @since  3.0.0
   */
  public static function newInstance ($class)
  {
    // the class exists
    $object = new $class();

    if (!($object instanceof sfSecurityFilter))
    {
      // the class name is of the wrong type
      $error = 'Class "%s" is not of the type sfSecurityFilter';
      $error = sprintf($error, $class);

      throw new sfFactoryException($error);
    }

    return $object;
  }
}
