<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfObjectRouteCollection represents a collection of routes bound to objects.
 *
 * @package    symfony
 * @subpackage routing
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfObjectRouteCollection extends sfRouteCollection
{
  protected
    $routeClass = 'sfObjectRoute';

  /**
   * Constructor.
   *
   * @param array $options An array of options
   */
  public function __construct(array $options)
  {
    parent::__construct($options);

    if (!isset($this->options['model']))
    {
      throw new InvalidArgumentException('You must pass a "model" option to sfObjectRouteCollection');
    }

    $this->options = array_merge(array(
      'plural'      => $this->options['name'],
      'singular'    => substr($this->options['name'], 0, -1),
      'actions'     => false,
      'module'      => $this->options['name'],
      'prefix_path' => '/'.$this->options['name'],
      'column'      => 'id',
      'with_show'   => true,
      'segment_names'  => array('edit' => 'edit', 'new' => 'new'),
    ), $this->options);

    if (isset($this->options['route_class']))
    {
      $this->routeClass = $this->options['route_class'];
    }

    $this->generateRoutes();
  }

  protected function generateRoutes()
  {
    $actions = false === $this->options['actions'] ? $this->getDefaultActions() : $this->options['actions'];

    foreach ($actions as $action)
    {
      $method = 'getRouteFor'.ucfirst($action);
      if (!method_exists($this, $method))
      {
        throw new InvalidArgumentException(sprintf('Unable to generate a route for the "%s" action.', $action));
      }

      $this->routes[$this->getRoute($action)] = $this->$method();
    }
  }

  protected function getRouteForList()
  {
    return new $this->routeClass(
      sprintf('%s.:sf_format', $this->options['prefix_path']),
      array('module' => $this->options['module'], 'action' => $this->getActionMethod('list'), 'sf_format' => 'html'),
      array_merge($this->options['requirements'], array('sf_method' => 'get')),
      array('model' => $this->options['model'], 'list' => $this->options['plural'])
    );
  }

  protected function getRouteForNew()
  {
    return new $this->routeClass(
      sprintf('%s/%s.:sf_format', $this->options['prefix_path'], $this->options['segment_names']['new']),
      array('module' => $this->options['module'], 'action' => $this->getActionMethod('new'), 'sf_format' => 'html'),
      array_merge($this->options['requirements'], array('sf_method' => 'get')),
      array('model' => $this->options['model'], 'object' => $this->options['singular'])
    );
  }

  protected function getRouteForCreate()
  {
    return new $this->routeClass(
      sprintf('%s.:sf_format', $this->options['prefix_path']),
      array('module' => $this->options['module'], 'action' => $this->getActionMethod('create'), 'sf_format' => 'html'),
      array_merge($this->options['requirements'], array('sf_method' => 'post')),
      array('model' => $this->options['model'], 'object' => $this->options['singular'])
    );
  }

  protected function getRouteForShow()
  {
    return new $this->routeClass(
      sprintf('%s/:%s.:sf_format', $this->options['prefix_path'], $this->options['column']),
      array('module' => $this->options['module'], 'action' => $this->getActionMethod('show'), 'sf_format' => 'html'),
      array_merge($this->options['requirements'], array('sf_method' => 'get')),
      array('model' => $this->options['model'], 'object' => $this->options['singular'])
    );
  }

  protected function getRouteForEdit()
  {
    return new $this->routeClass(
      sprintf('%s/:%s/%s.:sf_format', $this->options['prefix_path'], $this->options['column'], $this->options['segment_names']['edit']),
      array('module' => $this->options['module'], 'action' => $this->getActionMethod('edit'), 'sf_format' => 'html'),
      array_merge($this->options['requirements'], array('sf_method' => 'get')),
      array('model' => $this->options['model'], 'object' => $this->options['singular'])
    );
  }

  protected function getRouteForUpdate()
  {
    return new $this->routeClass(
      sprintf('%s/:%s.:sf_format', $this->options['prefix_path'], $this->options['column']),
      array('module' => $this->options['module'], 'action' => $this->getActionMethod('update'), 'sf_format' => 'html'),
      array_merge($this->options['requirements'], array('sf_method' => 'put')),
      array('model' => $this->options['model'], 'object' => $this->options['singular'])
    );
  }

  protected function getRouteForDelete()
  {
    return new $this->routeClass(
      sprintf('%s/:%s.:sf_format', $this->options['prefix_path'], $this->options['column']),
      array('module' => $this->options['module'], 'action' => $this->getActionMethod('delete'), 'sf_format' => 'html'),
      array('sf_method' => 'delete'),
      array('model' => $this->options['model'], 'object' => $this->options['singular'])
    );
  }

  protected function getDefaultActions()
  {
    $actions = array('list', 'new', 'create', 'show', 'edit', 'update', 'delete');

    if ($this->options['with_show'])
    {
      $actions[] = 'show';
    }

    return $actions;
  }

  protected function getRoute($action)
  {
    return 'list' == $action ? $this->options['name'] : $this->options['name'].'_'.$action;
  }

  protected function getActionMethod($action)
  {
    return 'list' == $action ? 'index' : $action;
  }
}
