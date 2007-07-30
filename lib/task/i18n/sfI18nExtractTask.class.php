<?php

/*
 * Current known limitations:
 *   - Can only works with the default "messages" catalogue
 *   - For file backends (XLIFF and gettext), it only saves/deletes strings in the "most global" file
 */

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Extracts i18n strings from php files.
 *
 * @package    symfony
 * @subpackage command
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfI18nExtractTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('culture', sfCommandArgument::REQUIRED, 'The target culture'),
    ));

    $this->addOptions(array(
      new sfCommandOption('display-new', null, sfCommandOption::PARAMETER_NONE, 'Output all new found strings'),
      new sfCommandOption('display-old', null, sfCommandOption::PARAMETER_NONE, 'Output all old strings'),
      new sfCommandOption('auto-save', null, sfCommandOption::PARAMETER_NONE, 'Save the new strings'),
      new sfCommandOption('auto-delete', null, sfCommandOption::PARAMETER_NONE, 'Delete old strings'),
    ));

    $this->namespace = 'i18n';
    $this->name = 'extract';
    $this->briefDescription = 'Extracts i18n strings from php files';

    $this->detailedDescription = <<<EOF
The [i18n:extract|INFO] task extracts i18n strings from your project files
for the given application and target culture:

  [./symfony i18n:extract frontend fr|INFO]

By default, the task only displays the number of new and old strings
it founds in the current project.

If you want to display the new strings, use the [--display-new|COMMENT] option:

  [./symfony i18n:extract --display-new frontend fr|INFO]

To save them in the i18n message catalogue, use the [--auto-save|COMMENT] option:

  [./symfony i18n:extract --auto-save frontend fr|INFO]

If you want to display strings that are present in the i18n messages
catalogue but are not found in the application, use the 
[--display-old|COMMENT] option:

  [./symfony i18n:extract --display-old frontend fr|INFO]

To automatically delete old strings, use the [--auto-delete|COMMENT] but
be careful, especially if you have translations for plugins as they will
appear as old strings but they are not:

  [./symfony i18n:extract --auto-delete frontend fr|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $this->bootstrapSymfony($arguments['application'], 'dev', true);

    $this->log($this->formatSection('i18n', sprintf('extracting i18n strings for the "%s" application', $arguments['application'])));

    $extract = new sfI18nApplicationExtract();
    $extract->initialize($arguments['culture']);
    $extract->extract();

    $this->log($this->formatSection('i18n', sprintf('found "%d" new i18n strings', count($extract->getNewMessages()))));
    $this->log($this->formatSection('i18n', sprintf('found "%d" old i18n strings', count($extract->getOldMessages()))));

    if ($options['display-new'])
    {
      $this->log($this->formatSection('i18n', sprintf('display new i18n strings', count($extract->getOldMessages()))));
      foreach ($extract->getNewMessages() as $message)
      {
        $this->log('               '.$message."\n");
      }
    }

    if ($options['auto-save'])
    {
      $this->log($this->formatSection('i18n', 'saving new i18n strings'));

      $extract->saveNewMessages();
    }

    if ($options['display-old'])
    {
      $this->log($this->formatSection('i18n', sprintf('display old i18n strings', count($extract->getOldMessages()))));
      foreach ($extract->getOldMessages() as $message)
      {
        $this->log('               '.$message."\n");
      }
    }

    if ($options['auto-delete'])
    {
      $this->log($this->formatSection('i18n', 'deleting old i18n strings'));

      $extract->deleteOldMessages();
    }
  }
}
