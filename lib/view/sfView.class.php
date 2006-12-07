<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * A view represents the presentation layer of an action. Output can be
 * customized by supplying attributes, which a template can manipulate and
 * display.
 *
 * @package    symfony
 * @subpackage view
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
abstract class sfView
{
  /**
   * Show an alert view.
   */
  const ALERT = 'Alert';

  /**
   * Show an error view.
   */
  const ERROR = 'Error';

  /**
   * Show a form input view.
   */
  const INPUT = 'Input';

  /**
   * Skip view execution.
   */
  const NONE = 'None';

  /**
   * Show a success view.
   */
  const SUCCESS = 'Success';

  /**
    * Skip view rendering but output http headers
    */
  const HEADER_ONLY = 'Headers';

  /**
   * Render the presentation to the client.
   */
  const RENDER_CLIENT = 2;

  /**
   * Do not render the presentation.
   */
  const RENDER_NONE = 1;

  /**
   * Render the presentation to a variable.
   */
  const RENDER_VAR = 4;

  protected
    $context            = null,
    $decorator          = false,
    $decoratorDirectory = null,
    $decoratorTemplate  = null,
    $directory          = null,
    $componentSlots     = array(),
    $template           = null,
    $escaping           = null,
    $escapingMethod     = null,
    $attributeHolder    = null,
    $parameterHolder    = null,
    $moduleName         = '',
    $actionName         = '',
    $viewName           = '',
    $extension          = '.php';

  /**
   * Execute any presentation logic and set template attributes.
   *
   * @return void
   */
  abstract function execute ();

  /**
   * Configure template.
   *
   * @return void
   */
  abstract function configure ();

  /**
   * Retrieve the current application context.
   *
   * @return Context The current Context instance.
   */
  public final function getContext ()
  {
    return $this->context;
  }

  /**
   * Retrieve this views decorator template directory.
   *
   * @return string An absolute filesystem path to this views decorator template directory.
   */
  public function getDecoratorDirectory ()
  {
    return $this->decoratorDirectory;
  }

  /**
   * Retrieve this views decorator template.
   *
   * @return string A template filename, if a template has been set, otherwise null.
   */
  public function getDecoratorTemplate ()
  {
    return $this->decoratorTemplate;
  }

  /**
   * Retrieve this views template directory.
   *
   * @return string An absolute filesystem path to this views template directory.
   */
  public function getDirectory ()
  {
    return $this->directory;
  }

  /**
   * Retrieve the template engine associated with this view.
   *
   * Note: This will return null for PHPView instances.
   *
   * @return mixed A template engine instance.
   */
  abstract function getEngine ();

  /**
   * Retrieve this views template.
   *
   * @return string A template filename, if a template has been set, otherwise null.
   */
  public function getTemplate ()
  {
    return $this->template;
  }

  /**
   * Gets the default escaping strategy associated with this view.
   *
   * The escaping strategy specifies how the variables get passed to the view.
   *
   * @return string the escaping strategy.
   */
  public function getEscaping()
  {
    return null === $this->escaping ? sfConfig::get('sf_escaping_strategy') : $this->escaping;
  }

  /**
   * Returns the name of the function that is to be used as the escaping method.
   *
   * If the escaping method is empty, then that is returned. The default value
   * specified by the sub-class will be used. If the method does not exist (in
   * the sense there is no define associated with the method) and exception is
   * thrown.
   *
   * @return string the escaping method as the name of the function to use
   */
  public function getEscapingMethod()
  {
    $method = null === $this->escapingMethod ? sfConfig::get('sf_escaping_method') : $this->escapingMethod;

    if (empty($method))
    {
      return $method;
    }

    if (!defined($method))
    {
      throw new sfException(sprintf('Escaping method "%s" is not available; perhaps another helper needs to be loaded in?', $method));
    }

    return constant($method);
  }

  /**
   * Import parameter values and error messages from the request directly as
   * view attributes.
   *
   * @param array An indexed array of file/parameter names.
   * @param bool  Is this a list of files?
   * @param bool  Import error messages too?
   * @param bool  Run strip_tags() on attribute value?
   * @param bool  Run htmlspecialchars() on attribute value?
   *
   * @return void
   */
  public function importAttributes ($names, $files = false, $errors = true, $stripTags = true, $specialChars = true)
  {
    // alias $request to keep the code clean
    $request = $this->context->getRequest();

    // get our array
    if ($files)
    {
      // file names
      $array =& $request->getFiles();
    }
    else
    {
      // parameter names
      $array =& $request->getParameterHolder()->getAll();
    }

    // loop through our parameter names and import them
    foreach ($names as &$name)
    {
        if (preg_match('/^([a-z0-9\-_]+)\{([a-z0-9\s\-_]+)\}$/i', $name, $match))
        {
          // we have a parent
          $parent  = $match[1];
          $subname = $match[2];

          // load the file/parameter value for this attribute if one exists
          if (isset($array[$parent]) && isset($array[$parent][$subname]))
          {
            $value = $array[$parent][$subname];

            if ($stripTags)
              $value = strip_tags($value);

            if ($specialChars)
              $value = htmlspecialchars($value);

            $this->setAttribute($name, $value);
          }
          else
          {
            // set an empty value
            $this->setAttribute($name, '');
          }
        }
        else
        {
          // load the file/parameter value for this attribute if one exists
          if (isset($array[$name]))
          {
            $value = $array[$name];

            if ($stripTags)
              $value = strip_tags($value);

            if ($specialChars)
              $value = htmlspecialchars($value);

            $this->setAttribute($name, $value);
          }
          else
          {
            // set an empty value
            $this->setAttribute($name, '');
          }
        }

        if ($errors)
        {
          if ($request->hasError($name))
          {
            $this->setAttribute($name.'_error', $request->getError($name));
          }
          else
          {
            // set empty error
            $this->setAttribute($name.'_error', '');
          }
        }
    }
  }

  /**
   * Initialize this view.
   *
   * @param Context The current application context.
   * @param string The module name for this view.
   * @param string The action name for this view.
   * @param string The view name.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   */
  public function initialize ($context, $moduleName, $actionName, $viewName)
  {
    if (sfConfig::get('sf_logging_enabled'))
    {
      $context->getLogger()->info(sprintf('{sfView} initialize view for "%s/%s"', $moduleName, $actionName));
    }

    $this->moduleName = $moduleName;
    $this->actionName = $actionName;
    $this->viewName   = $viewName;

    $this->context = $context;
    $this->attributeHolder = new sfParameterHolder();
    $this->parameterHolder = new sfParameterHolder();

    $this->parameterHolder->add(sfConfig::get('mod_'.strtolower($moduleName).'_view_param', array()));

    $this->decoratorDirectory = sfConfig::get('sf_app_template_dir');

    // include view configuration
    $this->configure();

    return true;
  }

  public function getAttributeHolder()
  {
    return $this->attributeHolder;
  }

  public function getAttribute($name, $default = null, $ns = null)
  {
    return $this->attributeHolder->get($name, $default, $ns);
  }

  public function hasAttribute($name, $ns = null)
  {
    return $this->attributeHolder->has($name, $ns);
  }

  public function setAttribute($name, $value, $ns = null)
  {
    return $this->attributeHolder->set($name, $value, $ns);
  }

  public function getParameterHolder()
  {
    return $this->parameterHolder;
  }

  public function getParameter($name, $default = null, $ns = null)
  {
    return $this->parameterHolder->get($name, $default, $ns);
  }

  public function hasParameter($name, $ns = null)
  {
    return $this->parameterHolder->has($name, $ns);
  }

  public function setParameter($name, $value, $ns = null)
  {
    return $this->parameterHolder->set($name, $value, $ns);
  }

  /**
   * Indicates that this view is a decorating view.
   *
   * @return bool true, if this view is a decorating view, otherwise false.
   */
  public function isDecorator ()
  {
    return $this->decorator;
  }

  public function setDecorator ($boolean)
  {
    $this->decorator = (boolean) $boolean;
  }

  /**
   * Execute a basic pre-render check to verify all required variables exist
   * and that the template is readable.
   *
   * @return void
   *
   * @throws <b>sfRenderException</b> If the pre-render check fails.
   */
  protected function preRenderCheck ()
  {
    if ($this->template == null)
    {
      // a template has not been set
      $error = 'A template has not been set';

      throw new sfRenderException($error);
    }

    $template = $this->directory.'/'.$this->template;

    if (!is_readable($template))
    {
      // the template isn't readable
      throw new sfRenderException(sprintf('The template "%s" does not exist in: %s', $template, $this->directory));
    }

    // check to see if this is a decorator template
    if ($this->decorator)
    {
      $template = $this->decoratorDirectory.'/'.$this->decoratorTemplate;

      if (!is_readable($template))
      {
        // the decorator template isn't readable
        $error = 'The decorator template "%s" does not exist or is unreadable';
        $error = sprintf($error, $template);

        throw new sfRenderException($error);
      }
    }
  }

  /**
   * Render the presentation.
   *
   * When the controller render mode is sfView::RENDER_CLIENT, this method will
   * render the presentation directly to the client and null will be returned.
   *
   * @param  array  An array with variables that will be extracted for the template
   *                If empty, the current actions var holder will be extracted.
   * @return string A string representing the rendered presentation, if
   *                the controller render mode is sfView::RENDER_VAR, otherwise null.
   */
  abstract function render ($templateVars = null);

  /**
   * Set the decorator template directory for this view.
   *
   * @param string An absolute filesystem path to a template directory.
   *
   * @return void
   */
  public function setDecoratorDirectory ($directory)
  {
    $this->decoratorDirectory = $directory;
  }

  public function setEscaping($escaping)
  {
    $this->escaping = $escaping;
  }

  public function setEscapingMethod($method)
  {
    $this->escapingMethod = $method;
  }

  /**
   * Set the decorator template for this view.
   *
   * If the template path is relative, it will be based on the currently
   * executing module's template sub-directory.
   *
   * @param string An absolute or relative filesystem path to a template.
   *
   * @return void
   */
  public function setDecoratorTemplate ($template)
  {
    if (sfToolkit::isPathAbsolute($template))
    {
      $this->decoratorDirectory = dirname($template);
      $this->decoratorTemplate  = basename($template);
    }
    else
    {
      $this->decoratorTemplate = $template;
    }

    if (!strpos($this->decoratorTemplate, '.'))
    {
      $this->decoratorTemplate .= $this->getExtension();
    }

    // set decorator status
    $this->decorator = true;
  }

  /**
   * Set the template directory for this view.
   *
   * @param string An absolute filesystem path to a template directory.
   *
   * @return void
   */
  public function setDirectory ($directory)
  {
    $this->directory = $directory;
  }

  /**
   * Set the module and action to be executed in place of a particular
   * template attribute.
   *
   * @param string A template attribute name.
   * @param string A module name.
   * @param string A omponent name.
   *
   * @return void
   */
  public function setComponentSlot ($attributeName, $moduleName, $componentName)
  {
    $this->componentSlots[$attributeName]                   = array();
    $this->componentSlots[$attributeName]['module_name']    = $moduleName;
    $this->componentSlots[$attributeName]['component_name'] = $componentName;
  }

  /**
   * Indicates whether or not a component slot exists.
   *
   * @param  string component slot name
   * @return bool true, if the component slot exists, otherwise false.
   */
  public function hasComponentSlot($name)
  {
    return isset($this->componentSlots[$name]);
  }

  /**
   * Get a component slot.
   *
   * @param  string component slot name
   * @return array component slot.
   */
  public function getComponentSlot($name)
  {
    if (isset($this->componentSlots[$name]) && $this->componentSlots[$name]['module_name'] && $this->componentSlots[$name]['component_name'])
    {
      return array($this->componentSlots[$name]['module_name'], $this->componentSlots[$name]['component_name']);
    }

    return null;
  }

  /**
   * Set the template for this view.
   *
   * If the template path is relative, it will be based on the currently
   * executing module's template sub-directory.
   *
   * @param string An absolute or relative filesystem path to a template.
   *
   * @return void
   */
  public function setTemplate ($template)
  {
    if (sfToolkit::isPathAbsolute($template))
    {
      $this->directory = dirname($template);
      $this->template  = basename($template);
    }
    else
    {
      $this->template = $template;
    }
  }

  public function getExtension ()
  {
    return $this->extension;
  }

  public function setExtension ($ext)
  {
    $this->extension = $ext;
  }

  public function __call($method, $arguments)
  {
    if (!$callable = sfMixer::getCallable('sfView:'.$method))
    {
      throw new sfException(sprintf('Call to undefined method sfView::%s', $method));
    }

    array_unshift($arguments, $this);

    return call_user_func_array($callable, $arguments);
  }
}
