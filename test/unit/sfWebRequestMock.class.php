<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfWebRequest
{
  function getRelativeUrlRoot()
  {
    return sfConfig::get('test_sfWebRequest_relative_url_root', '');
  }
}
