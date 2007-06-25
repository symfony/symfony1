<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony
 * @subpackage i18n
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfI18nYamlValidateExtractor extends sfI18nYamlExtractor
{
  /**
   * Extract i18n strings for the given content.
   *
   * @param  string The content
   *
   * @return array An array of i18n strings
   */
  public function extract($content)
  {
    $strings = array();

    $config = sfYaml::load($content);

    // New validate.yml format

    // fields
    if (isset($config['fields']))
    {
      foreach ($config['fields'] as $field => $validation)
      {
        foreach ($validation as $type => $parameters)
        {
          foreach ($parameters as $key => $value)
          {
            if (preg_match('/(msg|error)$/', $key))
            {
              $strings[] = $value;
            }
          }
        }
      }
    }

    // validators
    if (isset($config['validators']))
    {
      foreach (array_keys($config['validators']) as $name)
      {
        if (!isset($config['validators'][$name]['param']))
        {
          continue;
        }
        foreach ($config['validators'][$name]['param'] as $key => $value)
        {
          if (preg_match('/(msg|error)$/', $key))
          {
            $strings[] = $value;
          }
        }
      }
    }

    return $strings;
  }
}
