<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Send emails stored in a queue.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfProjectSendEmailsTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->namespace = 'project';
    $this->name = 'send-emails';

    $this->briefDescription = 'Sends emails stored in a queue';

    $this->detailedDescription = <<<EOF
The [project:send-emails|INFO] sends emails stored in a queue:

  [php symfony project:send-emails|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $mailer = $this->initializeMailer();

    $sent = $mailer->flushQueue();

    $this->logSection('project', sprintf('sent %s emails', $sent));
  }

  protected function initializeMailer()
  {
    require_once sfConfig::get('sf_symfony_lib_dir').'/vendor/swiftmailer/classes/Swift.php';
    Swift::registerAutoload();
    sfMailer::initialize();

    $config = sfFactoryConfigHandler::getConfiguration($this->configuration->getConfigPaths('config/factories.yml'));

    return new $config['mailer']['class']($this->dispatcher, $config['mailer']['param']);
  }
}
