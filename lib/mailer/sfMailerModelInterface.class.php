<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMailerModelInterface is the interface all model used with sfMailerTransportQueue should use.
 *
 * @package    symfony
 * @subpackage mailer
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
interface sfMailerModelInterface
{
  /**
   * Sets the message associated with this model.
   *
   * @param Swift_Mime_Message $message A Swift_Mime_Message instance
   */
  public function setMessage(Swift_Mime_Message $message);

  /**
   * Gets the message associated with this model.
   *
   * @return Swift_Mime_Message A Swift_Mime_Message instance
   */
  public function getMessage();
}
