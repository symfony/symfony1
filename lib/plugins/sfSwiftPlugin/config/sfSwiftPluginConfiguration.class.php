<?php

/**
 * sfSwiftPlugin configuration.
 * 
 * @package     sfSwiftPlugin
 * @subpackage  config
 * @author      Dustin Whittle <dustin.whittle@symfony-project.com>
 * @version     SVN: $Id: sfSwiftPluginConfiguration.class.php 14408 2008-12-31 18:22:00Z dwhittle $
 *
 * Enable plugin and customize factories.yml:
 *
 *  mailer:
 *    class:     sfSwiftMailer
 *    param:
 *      logging:      %SF_LOGGING_ENABLED%
 *      charset:      %SF_CHARSET%;
 *      culture:      %SF_DEFAULT_CULTURE%
 *      content-type: text/html
 *      from_email:   username@gmail.com
 *      cache:        memory     # memory | disk
 *      connection:
 *        class: Swift_Connection_SMTP
 *        param:
 *          host: smtp.gmail.com
 *          port: 465
 *          ssl:  true
 *          tls:  true
 *          username: username@gmail.com
 *          password: 1234
 *
 */
class sfSwiftPluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */
  public function configure()
  {
    sfToolkit::addIncludePath(array(
      sfConfig::get('sf_root_dir'),
      sfConfig::get('sf_symfony_lib_dir'),
      realpath(dirname(__FILE__).'/../lib/vendor'),
    ));
    
    $this->dispatcher->connect('context.load_factories', array($this, 'listenToContextLoadFactoriesEvent'));
  }

  public function listenToContextLoadFactoriesEvent(sfEvent $event)
  {
    $context = $event->getSubject();    
    $factories = sfFactoryConfigHandler::getConfiguration($this->configuration->getConfigPaths('config/factories.yml'));
    if(isset($factories['mailer']))
    {
      $defaults = array (
        'class' => 'sfSwiftMailer',
        'param' => 
        array (
          'logging' => false,
          'charset' => 'utf-8;',
          'culture' => 'en',
          'content-type' => 'text/html',
          'cache' => 'memory',
          'from_email' => 'webmaster@localhost.localdomain',
          'connection' => array ( 'class' => 'Swift_Connection_Native', 'param' => array('options' => array())),
        ));
        $factories['mailer'] = isset($factories['mailer']) ? sfToolkit::arrayDeepMerge($defaults, $factories['mailer']) : $defaults;
    
        $class = sfConfig::get('sf_factory_mailer', $factories['mailer']['class']);
        $parameters = sfConfig::get('sf_factory_mailer_parameters', $factories['mailer']['param']);
    
        if($parameters['connection']['class'] = 'Swift_Connection_SMTP')
        {
          if(!isset($parameters['connection']['param']['host']))
          {
            $parameters['connection']['param']['host'] = 'localhost';
          }

          if(!isset($parameters['connection']['param']['port']))
          {
            if(isset($parameters['connection']['param']['ssl']))
            {
              // default smtp ssl port
              $parameters['connection']['param']['port'] = Swift_Connection_SMTP::PORT_SECURE;
            }
            else
            {
              // default smtp port
              $parameters['connection']['param']['port'] = Swift_Connection_SMTP::PORT_DEFAULT;
            }
          }

          if(isset($parameters['connection']['param']['ssl']))
          {
            if(isset($parameters['connection']['param']['ssl']) && isset($parameters['connection']['param']['tls']))
            {
              // default smtp ssl port
              $parameters['connection']['param']['type'] = Swift_Connection_SMTP::ENC_TLS;
            }
            elseif(!isset($parameters['connection']['param']['tls']))
            {
              $parameters['connection']['param']['type'] = Swift_Connection_SMTP::ENC_SSL;
            }
          }
          else
          {
            $parameters['connection']['param']['type'] = 'null';
          }

          $connection = new $parameters['connection']['class']($parameters['connection']['param']['host'], $parameters['connection']['param']['port'], $parameters['connection']['param']['type']);
          if(isset($parameters['connection']['param']['username']))
          {
            $connection->setUsername($parameters['connection']['param']['username']);
          }
          if(isset($parameters['connection']['param']['password']))
          {
            $connection->setPassword($parameters['connection']['param']['password']);
          }
        }
        else
        {
          $connection = new $parameters['connection']['class']($parameters['connection']['param']['options']);
        }
        unset($parameters['connection']);
    
        $domain = isset($parameters['domain']) ? $parameters['domain'] : null;
    
        $swift = new Swift($connection, $domain, Swift::ENABLE_LOGGING | Swift::NO_START);
        $mailer = new $class($this->dispatcher, $swift, $parameters);
        $context->set('mailer', $mailer); 
      }
  }
}