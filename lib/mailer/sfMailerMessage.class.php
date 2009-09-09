<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMailerMessage represents an email message.
 *
 * @package    symfony
 * @subpackage mailer
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfMailerMessage extends Swift_Message
{
  public function __construct($subject = null, $body = null, $contentType = null, $charset = null)
  {
    parent::__construct($subject, $body, $contentType, $charset);
  }

  static public function newInstance($subject = null, $body = null, $contentType = null, $charset = null)
  {
    return new self($subject, $body, $contentType, $charset);
  }

  public function __wakeup()
  {
    Swift_DependencyContainer::getInstance()->createDependenciesFor('mime.message');
  }
}
