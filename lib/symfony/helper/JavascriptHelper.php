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
 * @author    Fabien POTENCIER (fabien.potencier@gmail.com)
 *  (c) Fabien POTENCIER
 * @since     1.0.0
     * @version   $Id: JavascriptHelper.php 455 2005-09-15 09:22:40Z fabien $
 */

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
      # By default, these remote requests are processed asynchronous during 
      # which various callbacks can be triggered (for progress indicators and
      # the likes).
      #
      # Example:
      #   link_to_remote word,
      #       :url => { :action => "undo", :n => word_counter },
      #       :complete => "undoRequestCompleted(request)"
      #
      # The callbacks that may be specified are:
      #
      # <tt>:loading</tt>::       Called when the remote document is being 
      #                           loaded with data by the browser.
      # <tt>:loaded</tt>::        Called when the browser has finished loading
      #                           the remote document.
      # <tt>:interactive</tt>::   Called when the user can interact with the 
      #                           remote document, even though it has not 
      #                           finished loading.
      # <tt>:complete</tt>::      Called when the XMLHttpRequest is complete.
      #
      # If you for some reason or another need synchronous processing (that'll
      # block the browser while the request is happening), you can specify 
      # <tt>options[:type] = :synchronous</tt>.
      #  
      # You can customize further browser side call logic by passing  
      # in Javascript code snippets via some optional parameters. In  
      # their order of use these are:  
      #  
      # <tt>:confirm</tt>::      Adds confirmation dialog.  
      # <tt>:condition</tt>::    Perform remote request conditionally  
      #                          by this expression. Use this to  
      #                          describe browser-side conditions when  
      #                          request should not be initiated.  
      # <tt>:before</tt>::       Called before request is initiated.  
      # <tt>:after</tt>::        Called immediately after request was  
      #                          initiated and before <tt>:loading</tt>.    */
  function link_to_remote($name, $options = array(), $html_options = array())  
  {
    return link_to_function($name, remote_function($options), $html_options);
  }

  /**
      # Returns a form tag that will submit using XMLHttpRequest in the background instead of the regular 
      # reloading POST arrangement. Even though it's using Javascript to serialize the form elements, the form submission 
      # will work just like a regular submission as viewed by the receiving side (all elements available in @params).
      # The options for specifying the target with :url and defining callbacks is the same as link_to_remote.
  */
  function form_remote_tag($options = array())
  {
        $options['form'] = true;

        if (!array_key_exists('html', $options)) $options['html'] = array();
        $options['html']['onsubmit'] = remote_function($options).'; return false;';

        return tag('form', $options['html'], true);
  }

  function remote_function($options)
  {
    $javascript_options = _options_for_ajax($options);

    $function = array_key_exists('update', $options) ? 'new Ajax.Updater(\''.$options['update'].'\', ' : 'new Ajax.Request(';

    $function .= '\''.url_for($options['url']).'\'';
    $function .= ', '.$javascript_options.')';

    if (array_key_exists('before', $options)) $function = $options['before'].'; '.$function;
    if (array_key_exists('after', $options)) $function = $function.'; '.$options['after'];
    if (array_key_exists('condition', $options)) $function = 'if ('.$options['condition'].') { '.$function.'; }';

    return $function;
  }

  /**
      # Observes the field with the DOM ID specified by +field_id+ and makes
      # an Ajax when its contents have changed.
      # 
      # Required +options+ are:
      # <tt>:frequency</tt>:: The frequency (in seconds) at which changes to
      #                       this field will be detected.
      # <tt>:url</tt>::       +url_for+-style options for the action to call
      #                       when the field has changed.
      # 
      # Additional options are:
      # <tt>:update</tt>::    Specifies the DOM ID of the element whose 
      #                       innerHTML should be updated with the
      #                       XMLHttpRequest response text.
      # <tt>:with</tt>::      A Javascript expression specifying the
      #                       parameters for the XMLHttpRequest. This defaults
      #                       to 'value', which in the evaluated context 
      #                       refers to the new field value.
      #
      # Additionally, you may specify any of the options documented in
      # +link_to_remote.
  */
  function observe_field($field_id, $options = array())
  {
    return _build_observer('Form.Element.Observer', $field_id, $options);
  }
      
  /**
      # Like +observe_field+, but operates on an entire form identified by the
      # DOM ID +form_id+. +options+ are the same as +observe_field+, except 
      # the default value of the <tt>:with</tt> option evaluates to the
      # serialized (request string) value of the form.
  */
  function observe_form($form_id, $options = array())
  {
    return _build_observer('Form.Observer', $form_id, $options);
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

  function _options_for_ajax($options)
  {
    $js_options = _build_callbacks($options);

    $js_options['asynchronous'] = (array_key_exists('type', $options)) ? ($options['type'] != 'synchronous') : 'true';
    if (array_key_exists('method', $options)) $js_options['method'] = $options['method'];
    if (array_key_exists('position', $options)) $js_options['insertion'] = sfInflector::camelize('Insertion.'.$options['position']);

    if (array_key_exists('form', $options))
      $js_options['parameters'] = 'Form.serialize(this)';
    else if (array_key_exists('with', $options))
      $js_options['parameters'] = $options['with'];

        $opts = array();
        foreach ($js_options as $key => $value)
          $opts[] = "$key:$value";

    return '{'.join(', ', $opts).'}';
  }

  function _build_observer($klass, $name, $options = array())
  {
    if (!array_key_exists('with', $options) && $options['update']) $options['with'] = 'value';

    $callback = remote_function(options);

    $javascript = '<script type="text/javascript">';
    $javascript .= 'new '.$klass.'("'.$name.'", ';
    $javascript .= $options['frequency'].', function(element, value) {';
    $javascript .= $callback.'})</script>';

    return $javascript;
  }

  function _build_callbacks($options)
  {
    $callbacks = array();
    foreach (array('uninitialized', 'loading', 'loaded', 'interactive', 'complete') as $callback)
    {
      if (array_key_exists($callback, $options))
      {
        $name = 'on'.ucfirst($callback);
        $code = $options[$callback];
        $callbacks[$name] = 'function(request){'.$code.'}';
      }
    }

    return $callbacks;
  }

?>