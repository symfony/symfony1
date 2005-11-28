<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfMailView extends sfPHPView
{
  /**
   * Retrieve the template engine associated with this view.
   *
   * Note: This will return null because PHP itself has no engine reference.
   *
   * @return null
   */
  public function &getEngine()
  {
    return 'sfMail';
  }

  public function configure()
  {
    // view.yml configure
    parent::configure();

    // require our configuration
    $viewConfigFile = $this->moduleName.'/'.SF_APP_MODULE_CONFIG_DIR_NAME.'/mailer.yml';
    require(sfConfigCache::checkConfig(SF_APP_MODULE_DIR_NAME.'/'.$viewConfigFile, array('prefix' => $this->moduleName.'_')));
  }

  /**
   * Render the presentation and send the email to the client.
   *
   */
  public function &render()
  {
    $template         = $this->getDirectory().'/'.$this->getTemplate();
    $actionStackEntry = $this->getContext()->getActionStack()->getLastEntry();
    $actionInstance   = $actionStackEntry->getActionInstance();

    $moduleName = $actionInstance->getModuleName();
    $actionName = $actionInstance->getActionName();

    $retval = null;

    // execute pre-render check
    $this->preRenderCheck();

    if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfMailView} render "'.$template.'"');

// FIXME: cache support (be careful: must implement cache for alternate templates!)
//    $retval = $this->getCacheContent();

    // render template if no cache
//    if ($retval === null)
//    {
      // render main template
      $retval = $this->renderFile($template);

      // render main and alternate templates
      $all_template_dir  = dirname($template);
      $all_template_regex = preg_replace('/\\.php$/', '\..+\.php', basename($template));
      $all_templates = pakeFinder::type('file')->name('/^'.$all_template_regex.'$/')->in($all_template_dir);
      $all_retvals = array();
      foreach ($all_templates as $templateFile)
      {
        if (preg_match('/\.(.+?)\.php$/', $templateFile, $matches))
        {
          $all_retvals[$matches[1]] = $this->renderFile($templateFile);
        }
      }

//      $retval = $this->setCacheContent($retval);
//    }

    // now render decorator template, if one exists
    if ($this->isDecorator())
    {
      $retval =& $this->decorate($retval);
    }

//    $this->setPageCacheContent($retval);

    // send email
    if (SF_LOGGING_ACTIVE) $this->getContext()->getLogger()->info('{sfMailView} send email to client');

    // configuration prefix
    $config_prefix = 'SF_MAILER_'.strtoupper($this->moduleName).'_';

    // get sfMail object from action
    $mail = $actionInstance->getVarHolder()->get('mail');

    $vars = array(
      'mailer',
      'priority', 'content_type', 'charset', 'encoding', 'wordwrap',
      'hostname', 'port', 'domain', 'username', 'password'
    );
    foreach ($vars as $var)
    {
      $setter = 'set'.sfInflector::camelize($var);
      $getter = 'get'.sfInflector::camelize($var);
      $value  = $mail->$getter() ? $mail->$getter() : constant($config_prefix.strtoupper($var));
      $mail->$setter($value);
    }

    $mail->setBody($retval);

    // alternate bodies
    $i = 0;
    foreach ($all_retvals as $type => $retval)
    {
      if ($type == 'altbody' && !$mail->getAltBody())
      {
        $mail->setAltBody($retval);
      }
      else
      {
        ++$i;
        $mail->addStringAttachment($retval, 'file'.$i, 'base64', 'text/'.$type);
      }
    }

    // preparing email to be sent
    $mail->prepare();

    // send e-mail
    if (constant($config_prefix.'DELIVER'))
    {
      $mail->send();
    }

    $header = $mail->getRawHeader();
    $body   = $mail->getRawBody();
    $retval = $header.$body;

    return $retval;
  }
}

?>