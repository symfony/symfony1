<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDoctrineRoute represents a route that is bound to a Doctrine class.
 *
 * A Doctrine route can represent a single Doctrine object or a list of objects.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineRoute.class.php 11475 2008-09-12 11:07:23Z fabien $
 */
class sfDoctrineRoute extends sfObjectRoute
{
  protected
    $query = null;

  /**
   * Constructor.
   *
   * @param string $pattern       The pattern to match
   * @param array  $defaults      An array of default parameter values
   * @param array  $requirements  An array of requirements for parameters (regexes)
   * @param array  $options       An array of options
   *
   * @see sfObjectRoute
   */
  public function __construct($pattern, array $defaults = array(), array $requirements = array(), array $options = array())
  {
    parent::__construct($pattern, $defaults, $requirements, $options);

    $this->options['object_model'] = $this->options['model'];
  }

  protected function getObjectForParameters($parameters)
  {
    return $this->getObjectsForParameters($parameters);
  }

  protected function getObjectsForParameters($parameters)
  {
    $this->options['model'] = Doctrine::getTable($this->options['model']);
    if (!isset($this->options['method']))
    {
      $variables = $this->getRealVariables();
      
      switch(count($variables))
      {
        case 0:
          $this->options['method'] = 'findAll';
          break;
        case 1:
          $this->options['method'] = 'findOneBy'.sfInflector::camelize($variables[0]);
          $parameters = $parameters[$variables[0]];
          break;
        default:
          $this->options['method'] = 'findByDQL';
          $wheres = array();
          foreach ($variables as $variable)
          {
            $variable = $this->options['model']->getFieldName($variable);
            $wheres[] = $variable." = '".$parameters[$variable]."'";
          }
          $parameters = implode(' AND ', $wheres);
      }
    }

    return parent::getObjectsForParameters($parameters);
  }

  protected function doConvertObjectToArray($object)
  {
    if (isset($this->options['convert']) || method_exists($object, 'toParams'))
    {
      return parent::doConvertObjectToArray($object);
    }

    $className = $this->options['model'];

    $parameters = array();

    foreach ($this->getRealVariables() as $variable)
    {
      $parameters[$variable] = $object->get($variable);
    }

    return $parameters;
  }
}