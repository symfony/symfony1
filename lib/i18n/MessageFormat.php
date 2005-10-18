<?php

/**
 * MessageFormat class file.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the BSD License.
 *
 * Copyright(c) 2004 by Qiang Xue. All rights reserved.
 *
 * To contact the author write to {@link mailto:qiang.xue@gmail.com Qiang Xue}
 * The latest version of PRADO can be obtained from:
 * {@link http://prado.sourceforge.net/}
 *
 * @author Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @version $Revision: 1.3 $  $Date: 2005/01/09 22:15:32 $
 * @package System.I18N.core
 */

/**
 * Get the MessageSource classes.
 */
require_once(dirname(__FILE__).'/MessageSource.php');

/**
 * MessageFormat class.
 * 
 * Format a message, that is, for a particular message find the 
 * translated message. The following is an example using 
 * a SQLite database to store the translation message. 
 * Create a new message format instance and echo "Hello"
 * in simplified Chinese. This assumes that the world "Hello"
 * is translated in the database.
 *
 * <code>
 *  $source = MessageSource::factory('SQLite', 'sqlite://messages.db');
 *	$source->setCulture('zh_CN');
 *	$source->setCache(new MessageCache('./tmp'));
 *
 * 	$formatter = new MessageFormat($source); 
 * 	
 *	echo $formatter->format('Hello');
 * </code>
 *
 * @author Xiang Wei Zhuo <weizhuo[at]gmail[dot]com>
 * @version v1.0, last update on Fri Dec 24 20:46:16 EST 2004
 * @package System.I18N.core
 */
class MessageFormat
{
	/**
	 * The message source.
	 * @var MessageSource 
	 */
	protected $source;
	
	/**
	 * A list of loaded message catalogues.
	 * @var array 
	 */
	protected $catagloues = array();
	
	/**
	 * The translation messages.
	 * @var array 
	 */
	protected $messages = array();
	
	/**
	 * A list of untranslated messages.
	 * @var array 
	 */
	protected $untranslated = array();
	
	/**
	 * The prefix and suffix to append to untranslated messages.
	 * @var array 
	 */
	protected $postscript = array('','');
	
	/**
	 * Set the default catalogue.
	 * @var string 
	 */
	public $Catalogue;

	/**
	 * Constructor.
	 * Create a new instance of MessageFormat using the messages
	 * from the supplied message source.
	 * @param MessageSource the source of translation messages.
	 */
	function __construct(IMessageSource $source)
	{
		$this->source = $source;	
	}
	
	/**
	 * Load the message from a particular catalogue. A listed
	 * loaded catalogues is kept to prevent reload of the same
	 * catalogue. The load catalogue messages are stored
	 * in the $this->message array.
	 * @param string message catalogue to load.
	 */
	protected function loadCatalogue($catalogue)
	{
		if(in_array($catalogue,$this->catagloues))
			return;
			
		if($this->source->load($catalogue))
		{
			$this->messages[$catalogue] = $this->source->read();
			$this->catagloues[] = $catalogue;						
		}
	}

	/**
	 * Format the string. That is, for a particular string find
	 * the corresponding translation. Variable subsitution is performed
	 * for the $args parameter. A different catalogue can be specified
	 * using the $catalogue parameter.
	 * @param string the string to translate.
	 * @param array a list of string to substitute.
	 * @param string get the translation from a particular message
	 * catalogue.
	 * @return string translated string.
	 */
	function format($string, $args=array(), $catalogue=null)
	{		
		if(empty($catalogue))
		{
			if(empty($this->Catalogue))
				$catalogue = 'messages';
			else 
				$catalogue = $this->Catalogue;
		}
				
		$this->loadCatalogue($catalogue);
		
		if(empty($args))
			$args = array();		
		
		foreach($this->messages[$catalogue] as $variant)
		{
			// foreach of the translation units
			foreach($variant as $source => $result)
			{ 
				// we found it, so return the target translation
				if($source == $string)
				{
					//check if it contains only strings.
					if(is_string($result))
						$target = $result;
					else
					{
						$target = $result[0];
					}
					//found, but untranslated
					if(empty($target))
					{
						return 	$this->postscript[0].
								strtr($string, $args).
								$this->postscript[1];		
					}
					else
						return strtr($target, $args);
				}
			}
		}
		
		// well we did not find the translation string.
		$this->source->append($string);
		
		return 	$this->postscript[0].
				strtr($string, $args).
				$this->postscript[1];
	}
	
	/**
	 * Get the message source.
	 * @return MessageSource 
	 */
	function getSource()
	{
		return $this->source;
	}
	
	/**
	 * Set the prefix and suffix to append to untranslated messages.
	 * e.g. $postscript=array('[T]','[/T]'); will output 
	 * "[T]Hello[/T]" if the translation for "Hello" can not be determined.
	 * @param array first element is the prefix, second element the suffix.
	 */
	function setUntranslatedPS($postscript)
	{
		if(is_array($postscript) && count($postscript)>=2)
		{
			$this->postscript[0] = $postscript[0];
			$this->postscript[1] = $postscript[1];
		}
	}
}

?>