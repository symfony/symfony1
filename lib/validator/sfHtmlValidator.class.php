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
 * @subpackage validator
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfHtmlValidator extends sfValidator
{
    public function execute (&$value, &$error)
    {
      if (trim(strip_tags($value)) == '')
      {
        // If page contains an object or an image, it's ok
        if (preg_match('/<img/i', $value) || preg_match('/<object/i', $value))
          return true;
        else
        {
          $error = $this->getParameterHolder()->get('html_error');
          return false;
        }
      }

      return true;
    }

    public function initialize ($context, $parameters = null)
    {
      // initialize parent
      parent::initialize($context);

      // set defaults
      $this->getParameterHolder()->set('html_error', 'Invalid input');

      $this->getParameterHolder()->add($parameters);

      return true;
    }
}

?>