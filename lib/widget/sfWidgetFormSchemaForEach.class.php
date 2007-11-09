<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * 
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfWidgetFormSchemaForEach extends sfWidgetFormSchema
{
  /**
   * Constructor.
   *
   * @param string             The name format string
   * @param sfWidgetFormSchema A sfWidgetFormSchema instance
   * @param integer            The number of times to replicate the widget
   * @param array              An array of options
   * @param array              An array of default HTML attributes
   * @param array              An array of HTML labels
   *
   * @see sfWidgetFormSchema
   */
  public function __construct($nameFormat, sfWidgetFormSchema $widget, $count, $options = array(), $attributes = array(), $labels = array())
  {
    $fields = array();
    for ($i = 0; $i < $count; $i++)
    {
      $clone = clone $widget;
      $clone->setNameFormat(sprintf($nameFormat, $i).'[%s]');

      $fields[$i] = $clone;
    }

    parent::__construct($fields, $options, $attributes, $labels);
  }
}
