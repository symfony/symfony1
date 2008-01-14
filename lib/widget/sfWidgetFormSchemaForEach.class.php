<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormSchemaForEach duplicates a given widget multiple times in a widget schema.
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
   * @param sfWidgetFormSchema A sfWidgetFormSchema instance
   * @param integer            The number of times to duplicate the widget
   * @param array              An array of options
   * @param array              An array of default HTML attributes
   * @param array              An array of HTML labels
   *
   * @see sfWidgetFormSchema
   */
  public function __construct(sfWidgetFormSchema $widget, $count, $options = array(), $attributes = array(), $labels = array())
  {
    parent::__construct(array_fill(0, $count, $widget), $options, $attributes, $labels);
  }
}
