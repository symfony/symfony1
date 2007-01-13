<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRichTextEditor is an abstract class for rich text editor classes.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
abstract class sfRichTextEditor
{
  protected
    $name = '',
    $content = '',
    $options = array();

  public function initialize($name, $content, $options = array())
  {
    $this->name = $name;
    $this->content = $content;
    $this->options = $options;
  }

  abstract public function toHTML();
}
