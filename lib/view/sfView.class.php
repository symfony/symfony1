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

  /**
   * Render the presentation from cache.
   */
  const RENDER_CACHE = 8;

  private
    $context            = null,
    $decorator          = false,
    $decoratorDirectory = null,
    $decoratorTemplate  = null,
    $directory          = null,
    $slots              = array(),
    $componentSlots     = array(),
    $template           = null;

  protected
    $attribute_holder   = null,
    $parameter_holder   = null,
    $moduleName         = '',
    $viewName           = '',
    $extension          = '.php';

  /**
   * Loop through all template slots and fill them in with the results of presentation data.
   *
   * @param string A chunk of decorator content.
   *
   * @return string A decorated template.
   */
  protected function & decorate (&$content)
  {
    // alias controller
    $controller = $this->getContext()->getController();

    // get original render mode
    $renderMode = $controller->getRenderMode();

    // set render mode to var
    $controller->setRenderMode(self::RENDER_VAR);

    // grab the action stack
    $actionStack = $controller->getActionStack();

    // loop through our slots, and replace them one-by-one in the
    // decorator template
    $slots =& $this->getSlots();

    foreach ($slots as $name => &$slot)
    {
      // grab this next forward's action stack index
      $index = $actionStack->getSize();

      // forward to the first slot action
      $controller->forward($slot['module_name'], $slot['action_name'], true);

      // grab the action entry from this forward
      $actionEntry = $actionStack->getEntry($index);

      // set the presentation data as a template attribute
      $presentation =& $actionEntry->getPresentation();

      $this->attribute_holder->setByRef($name, $presentation);
    }

    // put render mode back
    $controller->setRenderMode($renderMode);

    // set the decorator content as an attribute
    $this->attribute_holder->setByRef('content', $content);

    // return a null value to satisfy the requirement
    $retval = null;

    return $retval;
  }

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
  abstract function & getEngine ();

  /**
   * Retrieve an array of specified slots for the decorator template.
   *
   * @return array An associative array of decorator slots.
   */
  protected function & getSlots ()
  {
    return $this->slots;
  }

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
            $this->setAttribute($name.'_error', $request->getError($name));
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
   * @param string The view name.
   *
   * @return bool true, if initialization completes successfully, otherwise false.
   */
  public function initialize ($context, $moduleName, $viewName)
  {
    $this->moduleName = $moduleName;
    $this->viewName   = $viewName;

    $this->context = $context;
    $this->attribute_holder = new sfParameterHolder();
    $this->parameter_holder = new sfParameterHolder();

    $this->parameter_holder->add(sfConfig::get('mod_'.strtolower($moduleName).'_view_param', array()));

    // set the currently executing module's template directory as the default template directory
    $module = $context->getModuleName();

    $this->decoratorDirectory = sfConfig::get('sf_app_module_dir').'/'.$module.'/'.sfConfig::get('sf_app_module_template_dir_name');
    $this->directory          = $this->decoratorDirectory;

    // include view configuration
    $this->configure();

    return true;
  }

  public function getAttributeHolder()
  {
    return $this->attribute_holder;
  }

  public function getAttribute($name, $default = null, $ns = null)
  {
    return $this->attribute_holder->get($name, $default, $ns);
  }

  public function hasAttribute($name, $ns = null)
  {
    return $this->attribute_holder->has($name, $ns);
  }

  public function setAttribute($name, $value, $ns = null)
  {
    return $this->attribute_holder->set($name, $value, $ns);
  }

  public function getParameterHolder()
  {
    return $this->parameter_holder;
  }

  public function getParameter($name, $default = null, $ns = null)
  {
    return $this->parameter_holder->get($name, $default, $ns);
  }

  public function hasParameter($name, $ns = null)
  {
    return $this->parameter_holder->has($name, $ns);
  }

  public function setParameter($name, $value, $ns = null)
  {
    return $this->parameter_holder->set($name, $value, $ns);
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
      $error = 'The template "%s" does not exist or is unreadable';
      $error = sprintf($error, $template);

      throw new sfRenderException($error);
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
   * @return string A string representing the rendered presentation, if
   *                the controller render mode is sfView::RENDER_VAR, otherwise
   *                null.
   */
  abstract function & render ();

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
      $this->decoratorTemplate .= $this->extension;
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
   * @param string An action name.
   *
   * @return void
   */
  public function setSlot ($attributeName, $moduleName, $actionName)
  {
    $this->slots[$attributeName]                = array();
    $this->slots[$attributeName]['module_name'] = $moduleName;
    $this->slots[$attributeName]['action_name'] = $actionName;
  }

  /**
   * Indicates whether or not a slot exists.
   *
   * @param  string slot name
   * @return bool true, if the slot exists, otherwise false.
   */
  public function hasSlot($name)
  {
    return isset($this->slots[$name]);
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
}

?>