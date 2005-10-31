<?php

// +---------------------------------------------------------------------------+
// | This file is part of the SymFony Framework project.                        |
// | Copyright (c) 2004, 2005 Fabien POTENCIER.                                          |
// +---------------------------------------------------------------------------+

/**
 *
 * @package   sf_runtime
 * @subpackage helper
 *
 * @author    Fabien POTENCIER (fabien.potencier@symfony-project.com)
 *  (c) Fabien POTENCIER
 * @since     1.0.0
     * @version   $Id: JavascriptHelper.php 455 2005-09-15 09:22:40Z fabien $
 */

/*
# Provides a set of helpers for calling JavaScript functions and, most importantly, to call remote methods using what has 
# been labelled AJAX[http://www.adaptivepath.com/publications/essays/archives/000385.php]. This means that you can call 
# actions in your controllers without reloading the page, but still update certain parts of it using injections into the 
# DOM. The common use case is having a form that adds a new element to a list without reloading the page.
#
# To be able to use the JavaScript helpers, you must include the Prototype JavaScript Framework and for some functions
# script.aculo.us (which both come with Rails) on your pages. Choose one of these options:
#
# * Use <tt><%= javascript_include_tag :defaults %></tt> in the HEAD section of your page (recommended):
#   The function will return references to the JavaScript files created by the +rails+ command in your
#   <tt>public/javascripts</tt> directory. Using it is recommended as the browser can then cache the libraries
#   instead of fetching all the functions anew on every request.
# * Use <tt><%= javascript_include_tag 'prototype' %></tt>: As above, but will only include the Prototype core library,
#   which means you are able to use all basic AJAX functionality. For the script.aculo.us-based JavaScript helpers,
#   like visual effects, autocompletion, drag and drop and so on, you should use the method described above.
# * Use <tt><%= define_javascript_functions %></tt>: this will copy all the JavaScript support functions within a single
#   script block.
#
# For documentation on +javascript_include_tag+ see ActionView::Helpers::AssetTagHelper.
#
# If you're the visual type, there's an AJAX movie[http://www.rubyonrails.com/media/video/rails-ajax.mov] demonstrating
# the use of form_remote_tag.
*/

  function get_callbacks()
  {
    $callbacks = array(
      'uninitialized', 'loading', 'loaded', 'interactive', 'complete', 'failure', 'success'
    );
    for ($i = 100; $i <= 599; $i++)
    {
      $callbacks[] = $i;
    }

    return $callbacks;
  }

  function get_ajax_options()
  {
    $ajax_options = array(
      'before', 'after', 'condition', 'url', 'asynchronous', 'method',
      'insertion', 'position', 'form', 'with', 'update', 'script'
    );

    return array_merge($ajax_options, get_callbacks());
  }

  /**
      # Returns a link that'll trigger a javascript +function+ using the 
      # onclick handler and return false after the fact.
      #
      # Examples:
      #   link_to_function "Greeting", "alert('Hello world!')"
      #   link_to_function(image_tag("delete"), "if confirm('Really?'){ do_delete(); }")
  */
  function link_to_function($name, $function, $html_options = array())
  {
    $html_options['href'] = '#';
    $html_options['onclick'] = $function.'; return false;';

    return content_tag('a', $name, $html_options);
  }

  /**
    # Returns a link to a remote action defined by <tt>options[:url]</tt> 
    # (using the url_for format) that's called in the background using 
    # XMLHttpRequest. The result of that request can then be inserted into a
    # DOM object whose id can be specified with <tt>options[:update]</tt>. 
    # Usually, the result would be a partial prepared by the controller with
    # either render_partial or render_partial_collection. 
    #
    # Examples:
    #  link_to_remote "Delete this post", :update => "posts", :url => { :action => "destroy", :id => post.id }
    #  link_to_remote(image_tag("refresh"), :update => "emails", :url => { :action => "list_emails" })
    #
    # You can also specify a hash for <tt>options[:update]</tt> to allow for
    # easy redirection of output to an other DOM element if a server-side error occurs:
    #
    # Example:
    #  link_to_remote "Delete this post",
    #      :url => { :action => "destroy", :id => post.id },
    #      :update => { :success => "posts", :failure => "error" }
    #
    # Optionally, you can use the <tt>options[:position]</tt> parameter to influence
    # how the target DOM element is updated. It must be one of 
    # <tt>:before</tt>, <tt>:top</tt>, <tt>:bottom</tt>, or <tt>:after</tt>.
    #
    # By default, these remote requests are processed asynchronous during 
    # which various JavaScript callbacks can be triggered (for progress indicators and
    # the likes). All callbacks get access to the <tt>request</tt> object,
    # which holds the underlying XMLHttpRequest. 
    #
    # To access the server response, use <tt>request.responseText</tt>, to
    # find out the HTTP status, use <tt>request.status</tt>.
    #
    # Example:
    #   link_to_remote word,
    #       :url => { :action => "undo", :n => word_counter },
    #       :complete => "undoRequestCompleted(request)"
    #
    # The callbacks that may be specified are (in order):
    #
    # <tt>:loading</tt>::       Called when the remote document is being 
    #                           loaded with data by the browser.
    # <tt>:loaded</tt>::        Called when the browser has finished loading
    #                           the remote document.
    # <tt>:interactive</tt>::   Called when the user can interact with the 
    #                           remote document, even though it has not 
    #                           finished loading.
    # <tt>:success</tt>::       Called when the XMLHttpRequest is completed,
    #                           and the HTTP status code is in the 2XX range.
    # <tt>:failure</tt>::       Called when the XMLHttpRequest is completed,
    #                           and the HTTP status code is not in the 2XX
    #                           range.
    # <tt>:complete</tt>::      Called when the XMLHttpRequest is complete 
    #                           (fires after success/failure if they are present).,
    #                     
    # You can further refine <tt>:success</tt> and <tt>:failure</tt> by adding additional 
    # callbacks for specific status codes:
    #
    # Example:
    #   link_to_remote word,
    #       :url => { :action => "action" },
    #       404 => "alert('Not found...? Wrong URL...?')",
    #       :failure => "alert('HTTP Error ' + request.status + '!')"
    #
    # A status code callback overrides the success/failure handlers if present.
    #
    # If you for some reason or another need synchronous processing (that'll
    # block the browser while the request is happening), you can specify 
    # <tt>options[:type] = :synchronous</tt>.
    #
    # You can customize further browser side call logic by passing
    # in JavaScript code snippets via some optional parameters. In
    # their order of use these are:
    #
    # <tt>:confirm</tt>::      Adds confirmation dialog.
    # <tt>:condition</tt>::    Perform remote request conditionally
    #                          by this expression. Use this to
    #                          describe browser-side conditions when
    #                          request should not be initiated.
    # <tt>:before</tt>::       Called before request is initiated.
    # <tt>:after</tt>::        Called immediately after request was
    #                          initiated and before <tt>:loading</tt>.
    # <tt>:submit</tt>::       Specifies the DOM element ID that's used
    #                          as the parent of the form elements. By 
    #                          default this is the current form, but
    #                          it could just as well be the ID of a
    #                          table row or any other DOM element.
  */
  function link_to_remote($name, $options = array(), $html_options = array())
  {
    return link_to_function($name, remote_function($options), $html_options);
  }

  /**
    # Periodically calls the specified url (<tt>options[:url]</tt>) every <tt>options[:frequency]</tt> seconds (default is 10).
    # Usually used to update a specified div (<tt>options[:update]</tt>) with the results of the remote call.
    # The options for specifying the target with :url and defining callbacks is the same as link_to_remote.
  */
  function periodically_call_remote($options = array())
  {
    $frequency = isset($options['frequency']) ? $options['frequency'] : 10; // every ten seconds by default
    $code = 'new PeriodicalExecuter(function() {'.remote_function($options).'}, '.$frequency.')';

    return javascript_tag($code);
  }

  /**
    # Returns a form tag that will submit using XMLHttpRequest in the background instead of the regular 
    # reloading POST arrangement. Even though it's using JavaScript to serialize the form elements, the form submission 
    # will work just like a regular submission as viewed by the receiving side (all elements available in @params).
    # The options for specifying the target with :url and defining callbacks is the same as link_to_remote.
    #
    # A "fall-through" target for browsers that doesn't do JavaScript can be specified with the :action/:method options on :html
    #
    #   form_remote_tag :html => { :action => url_for(:controller => "some", :action => "place") }
    # The Hash passed to the :html key is equivalent to the options (2nd) argument in the FormTagHelper.form_tag method.
    #
    # By default the fall-through action is the same as the one specified in the :url (and the default method is :post).
  */
  function form_remote_tag($options = array())
  {
    $options['form'] = true;

    if (!array_key_exists('html', $options))
    {
      $options['html'] = array();
    }
    $options['html']['onsubmit'] = remote_function($options).'; return false;';
    $options['html']['action'] = isset($options['html']['action']) ? $options['html']['action'] : url_for($options['url']);
    $options['html']['method'] = isset($options['html']['method']) ? $options['html']['method'] : 'post';

    return tag('form', $options['html'], true);
  }

  /**
    # Returns a button input tag that will submit form using XMLHttpRequest in the background instead of regular
    # reloading POST arrangement. <tt>options</tt> argument is the same as in <tt>form_remote_tag</tt>
  */
  function submit_to_remote($name, $value, $options = array())
  {
    if (!isset($options['with']))
    {
      $options['with'] = 'Form.serialize(this.form)';
    }

    if (!isset($options['html']))
    {
      $options['html'] = array();
    }
    $options['html']['type'] = 'button';
    $options['html']['onclick'] = remote_function($options).'; return false;';
    $options['html']['name'] = $name;
    $options['html']['value'] = $value;

    tag('input', $options['html'], false);
  }

  /**
    # Returns a Javascript function (or expression) that'll update a DOM element according to the options passed.
    #
    # * <tt>:content</tt>: The content to use for updating. Can be left out if using block, see example.
    # * <tt>:action</tt>: Valid options are :update (assumed by default), :empty, :remove
    # * <tt>:position</tt> If the :action is :update, you can optionally specify one of the following positions: :before, :top, :bottom, :after.
    #
    # Examples:
    #   <%= javascript_tag(update_element_function(
    #         "products", :position => :bottom, :content => "<p>New product!</p>")) %>
    #
    #   <% replacement_function = update_element_function("products") do %>
    #     <p>Product 1</p>
    #     <p>Product 2</p>
    #   <% end %>
    #   <%= javascript_tag(replacement_function) %>
    #
    # This method can also be used in combination with remote method call where the result is evaluated afterwards to cause
    # multiple updates on a page. Example:
    #
    #   # Calling view
    #   <%= form_remote_tag :url => { :action => "buy" }, :complete => evaluate_remote_response %>
    #   all the inputs here...
    #
    #   # Controller action
    #   def buy
    #     @product = Product.find(1)
    #   end
    #
    #   # Returning view
    #   <%= update_element_function(
    #         "cart", :action => :update, :position => :bottom, 
    #         :content => "<p>New Product: #{@product.name}</p>")) %>
    #   <% update_element_function("status", :binding => binding) do %>
    #     You've bought a new product!
    #   <% end %>
    #
    # Notice how the second call doesn't need to be in an ERb output block since it uses a block and passes in the binding
    # to render directly. This trick will however only work in ERb (not Builder or other template forms).
  */
  function update_element_function($element_id, $options = array())
  {
    sfContext::getInstance()->getRequest()->setAttribute(
      'javascript_update_element_function',
      array('/sf/js/prototype'),
      'helper/asset/auto/javascript'
    );

    $content = escape_javascript(isset($options['content']) ? $options['content'] : '');

    $value = isset($options['action']) ? $options['action'] : 'update';
    switch ($value)
    {
      case 'update':
        if ($options['position'])
        {
          $javascript_function = "new Insertion.".sfInflector::camelize($options['position'])."('$element_id','$content')";
        }
        else
        {
          $javascript_function = "\$('$element_id').innerHTML = '$content'";
        }
        break;

      case 'empty':
        $javascript_function = "\$('$element_id').innerHTML = ''";
        break;

      case 'remove':
        $javascript_function = "Element.remove('$element_id')";
        break;

      default:
        throw new sfException('Invalid action, choose one of update, remove, empty');
    }

    $javascript_function .= ";\n";

    return ($options['binding'] ? $javascript_function.$options['binding'] : $javascript_function);
  }

  /**
    # Returns 'eval(request.responseText)' which is the Javascript function that form_remote_tag can call in :complete to
    # evaluate a multiple update return document using update_element_function calls.
  */
  function evaluate_remote_response()
  {
    return 'eval(request.responseText)';
  }

  /**
    # Returns the javascript needed for a remote function.
    # Takes the same arguments as link_to_remote.
    # 
    # Example:
    #   <select id="options" onchange="<%= remote_function(:update => "options", :url => { :action => :update_options }) %>">
    #     <option value="0">Hello</option>
    #     <option value="1">World</option>
    #   </select>
  */
  function remote_function($options)
  {
    sfContext::getInstance()->getRequest()->setAttribute(
      'javascript_remote_function',
      array('/sf/js/prototype'),
      'helper/asset/auto/javascript'
    );

    $javascript_options = _options_for_ajax($options);

    $update = '';
    if (isset($options['update']) && is_array($options['update']))
    {
      $update = array();
      if (isset($options['update']['success']))
      {
        $update[] = "success:'".$options['update']['success']."}'";
      }
      if (isset($options['update']['failure']))
      {
        $update[] = "failure:'".$options['update']['failure']."}'";
      }
      $update = '{'.join(',', $update).'}';
    }
    else if (isset($options['update']))
    {
      $update .= "'".$options['update']."'";
    }

    $function = !$update ?  "new Ajax.Request(" : "new Ajax.Updater($update, ";

    $function .= '\''.url_for($options['url']).'\'';
    $function .= ', '.$javascript_options.')';

    if (isset($options['before']))
    {
      $function = $options['before'].'; '.$function;
    }
    if (isset($options['after']))
    {
      $function = $function.'; '.$options['after'];
    }
    if (isset($options['condition']))
    {
      $function = 'if ('.$options['condition'].') { '.$function.'; }';
    }
    if (isset($options['confirm']))
    {
      $function = "if (confirm('".escape_javascript($options['confirm'])."')) { $function; }";
    }

    return $function;
  }

  /**
    # Observes the field with the DOM ID specified by +field_id+ and makes
    # an AJAX call when its contents have changed.
    # 
    # Required +options+ are:
    # <tt>:url</tt>::       +url_for+-style options for the action to call
    #                       when the field has changed.
    # 
    # Additional options are:
    # <tt>:frequency</tt>:: The frequency (in seconds) at which changes to
    #                       this field will be detected. Not setting this
    #                       option at all or to a value equal to or less than
    #                       zero will use event based observation instead of
    #                       time based observation.
    # <tt>:update</tt>::    Specifies the DOM ID of the element whose 
    #                       innerHTML should be updated with the
    #                       XMLHttpRequest response text.
    # <tt>:with</tt>::      A JavaScript expression specifying the
    #                       parameters for the XMLHttpRequest. This defaults
    #                       to 'value', which in the evaluated context 
    #                       refers to the new field value.
    #
    # Additionally, you may specify any of the options documented in
    # link_to_remote.
  */
  function observe_field($field_id, $options = array())
  {
    sfContext::getInstance()->getRequest()->setAttribute(
      'javascript_observe_field',
      array('/sf/js/prototype'),
      'helper/asset/auto/javascript'
    );

    if (isset($options['frequency']) && $options['frequency'] > 0)
    {
      return _build_observer('Form.Element.Observer', $field_id, $options);
    }
    else
    {
      return _build_observer('Form.Element.EventObserver', $field_id, $options);
    }
  }

  /**
    # Like +observe_field+, but operates on an entire form identified by the
    # DOM ID +form_id+. +options+ are the same as +observe_field+, except 
    # the default value of the <tt>:with</tt> option evaluates to the
    # serialized (request string) value of the form.
  */
  function observe_form($form_id, $options = array())
  {
    sfContext::getInstance()->getRequest()->setAttribute(
      'javascript_observe_form',
      array('/sf/js/prototype'),
      'helper/asset/auto/javascript'
    );

    if (isset($options['frequency']) && $options['frequency'] > 0)
    {
      return _build_observer('Form.Observer', $form_id, $options);
    }
    else
    {
      return _build_observer('Form.EventObserver', $form_id, $options);
    }
  }

  /**
    # Returns a JavaScript snippet to be used on the AJAX callbacks for starting
    # visual effects.
    #
    # This method requires the inclusion of the script.aculo.us JavaScript library.
    #
    # Example:
    #   <%= link_to_remote "Reload", :update => "posts", 
    #         :url => { :action => "reload" }, 
    #         :complete => visual_effect(:highlight, "posts", :duration => 0.5 )
    #
    # If no element_id is given, it assumes "element" which should be a local
    # variable in the generated JavaScript execution context. This can be used
    # for example with drop_receiving_element:
    #
    #   <%= drop_receving_element (...), :loading => visual_effect(:fade) %>
    #
    # This would fade the element that was dropped on the drop receiving element.
    #
    # You can change the behaviour with various options, see
    # http://script.aculo.us for more documentation.
  */
  function visual_effect($name, $element_id = false, $js_options = array())
  {
    sfContext::getInstance()->getRequest()->setAttribute(
      'javascript_visual_effect',
      array('/sf/js/prototype', '/sf/js/effects'),
      'helper/asset/auto/javascript'
    );

    $element = $element_id ? "'$element_id'" : 'element';

    return "new Effect.".sfInflector::camelize($name)."($element,"._options_for_javascript($js_options).");";
  }

  /**
    # Makes the element with the DOM ID specified by +element_id+ sortable
    # by drag-and-drop and make an AJAX call whenever the sort order has
    # changed. By default, the action called gets the serialized sortable
    # element as parameters.
    #
    # This method requires the inclusion of the script.aculo.us JavaScript library.
    #
    # Example:
    #   <%= sortable_element("my_list", :url => { :action => "order" }) %>
    #
    # In the example, the action gets a "my_list" array parameter 
    # containing the values of the ids of elements the sortable consists 
    # of, in the current order.
    #
    # You can change the behaviour with various options, see
    # http://script.aculo.us for more documentation.
  */
  function sortable_element($element_id, $options = array())
  {
    sfContext::getInstance()->getRequest()->setAttribute(
      'javascript_sortable_element',
      array('/sf/js/prototype', '/sf/js/effects', '/sf/js/dragdrop'),
      'helper/asset/auto/javascript'
    );

    if (!isset($options['with']))
    {
      $options['with'] = "Sortable.serialize('$element_id')";
    }

    if (!isset($options['onUpdate']))
    {
      $options['onUpdate'] = "function(){".remote_function($options)."}";
    }

    foreach (get_ajax_options() as $key)
    {
      unset($options[$key]);
    }

    foreach (array('tag', 'overlap', 'constraint', 'handle') as $option)
    {
      if (isset($options[$option]))
      {
        $options[$option] = "'{$options[$option]}'";
      }
    }

    if (isset($options['containment']))
    {
      $options['containment'] = _array_or_string_for_javascript($options['containment']);
    }

    if (isset($options['only']))
    {
      $options['only'] = _array_or_string_for_javascript($options['only']);
    }

    return javascript_tag("Sortable.create('$element_id', "._options_for_javascript($options).")");
  }

  /**
    # Makes the element with the DOM ID specified by +element_id+ draggable.
    #
    # This method requires the inclusion of the script.aculo.us JavaScript library.
    #
    # Example:
    #   <%= draggable_element("my_image", :revert => true)
    # 
    # You can change the behaviour with various options, see
    # http://script.aculo.us for more documentation. 
  */
  function draggable_element($element_id, $options = array())
  {
    sfContext::getInstance()->getRequest()->setAttribute(
      'javascript_draggable_element',
      array('/sf/js/prototype', '/sf/js/effects', '/sf/js/dragdrop'),
      'helper/asset/auto/javascript'
    );

    return javascript_tag("new Draggable('$element_id', "._options_for_javascript($options).")");
  }

  /**
    # Makes the element with the DOM ID specified by +element_id+ receive
    # dropped draggable elements (created by draggable_element).
    # and make an AJAX call  By default, the action called gets the DOM ID of the
    # element as parameter.
    #
    # This method requires the inclusion of the script.aculo.us JavaScript library.
    #
    # Example:
    #   <%= drop_receiving_element("my_cart", :url => { :controller => "cart", :action => "add" }) %>
    #
    # You can change the behaviour with various options, see
    # http://script.aculo.us for more documentation.
  */
  function drop_receiving_element($element_id, $options = array())
  {
    sfContext::getInstance()->getRequest()->setAttribute(
      'javascript_drop_receiving_element',
      array('/sf/js/prototype', '/sf/js/effects', '/sf/js/dragdrop'),
      'helper/asset/auto/javascript'
    );

    if (!isset($options['with']))
    {
      $options['with'] = "'id=' + encodeURIComponent(element.id)";
    }
    if (!isset($options['onDrop']))
    {
      $options['onDrop'] = "function(element){".remote_function($options)."}";
    }

    foreach (get_ajax_options() as $key)
    {
      unset($options[$key]);
    }

    if (isset($options['accept']))
    {
      $options['accept'] = _array_or_string_for_javascript($options['accept']);
    }

    if (isset($options['hoverclass']))
    {
      $options['hoverclass'] = "'{$options['hoverclass']}'";
    }

    return javascript_tag("Droppables.add('$element_id', "._options_for_javascript($options).")");
  }

  /**
    # Escape carrier returns and single and double quotes for Javascript segments.
  */
  function escape_javascript($javascript = '')
  {
    $javascript = preg_replace('/\r\n|\n|\r/', "\\n", $javascript);
    $javascript = preg_replace('/(["\'])/', '\\\\1', $javascript);

    return $javascript;
  }

  /**
    # Returns a JavaScript tag with the +content+ inside. Example:
    #   javascript_tag "alert('All is good')" # => <script type="text/javascript">alert('All is good')</script>
  */
  function javascript_tag($content)
  {
    return content_tag('script', javascript_cdata_section($content), array('type' => 'text/javascript'));
  }

  function javascript_cdata_section($content)
  {
    return $content;
//FIXME
//    return "\n//".cdata_section("\n$content\n//")."\n";
  }

  function _options_for_javascript($options)
  {
    $opts = array();
    foreach ($options as $key => $value)
    {
      $opts[] = "$key:$value";
    }
    sort($opts);

    return '{'.join(', ', $opts).'}';
  }

  function _array_or_string_for_javascript($option)
  {
    if (is_array($option))
    {
      return "['".join('\',\'', $option)."']";
    }
    else if ($option)
    {
      return "'$option'";
    }
  }

  function _options_for_ajax($options)
  {
    $js_options = _build_callbacks($options);

    $js_options['asynchronous'] = (isset($options['type'])) ? ($options['type'] != 'synchronous') : 'true';
    if (isset($options['method'])) $js_options['method'] = _method_option_to_s($options['method']);
    if (isset($options['insertion'])) $js_options['insertion'] = "Insertion.".sfInflector::camelize($options['position']);
    $js_options['evalScripts'] = !isset($options['script']) || $options['script'];

    if (isset($options['form']))
    {
      $js_options['parameters'] = 'Form.serialize(this)';
    }
    else if (isset($options['submit']))
    {
      $js_options['parameters'] = "Form.serialize(document.getElementById('{$options['submit']}'))";
    }
    else if (isset($options['with']))
    {
      $js_options['parameters'] = $options['with'];
    }

    return _options_for_javascript($js_options);
  }

  function method_option_to_s($method)
  {
    return (is_string($method) && $method{0} != "'") ? $method : "'$method'";
  }

  function _build_observer($klass, $name, $options = array())
  {
    if (!isset($options['with']) && $options['update'])
    {
      $options['with'] = 'value';
    }

    $callback = remote_function(options);

    $javascript .= 'new '.$klass.'("'.$name.'", ';
    if (isset($options['frequency']))
    {
      $javascript .= $options['frequency'].", ";
    }
    $javascript .= "function(element, value) {";
    $javascript .= $options['frequency'].', function(element, value) {';
    $javascript .= $callback.'})';

    return javascript_tag($javascript);
  }

  function _build_callbacks($options)
  {
    $callbacks = array();
    foreach (get_callbacks() as $callback)
    {
      if (isset($options[$callback]))
      {
        $name = 'on'.ucfirst($callback);
        $code = $options[$callback];
        $callbacks[$name] = 'function(request){'.$code.'}';
      }
    }

    return $callbacks;
  }
?>