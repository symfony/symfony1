<?php
  /** 
   * Spyc -- A Simple PHP YAML Class
   * @version $Id: spyc.php,v 1.7 2005/06/27 05:25:51 ozmm Exp $
   * @author Chris Wanstrath <chris@ozmm.org>
   * @link http://ozmm.org/spyc/
   * @copyright Copyright 2005 Chris Wanstrath
   * @license http://www.opensource.org/licenses/mit-license.php MIT License
   * @package Spyc
   */

  /** 
   * A node, used by Spyc for parsing YAML.
   * @package Spyc
   */
  class YAMLNode {
    /**#@+
     * @access public
     * @var string
     */ 
    var $parent;
    var $id;
    /**#@+*/
    /** 
     * @access public
     * @var mixed
     */
    var $data;
    /** 
     * @access public
     * @var int
     */
    var $indent;
    /** 
     * @access public
     * @var bool
     */
    var $children = false;

    /**
     * The constructor assigns the node a unique ID.
     * @access public
     * @return void
     */
    function YAMLNode() {
      $this->id = uniqid();
    }
  }

  /**
   * The Simple PHP YAML Class.
   *
   * This class can be used to read a YAML file and convert its contents
   * into a PHP array.  It currently supports a very limited subsection of
   * the YAML spec.
   *
   * Usage:
   * <code>
   *   $parser = new Spyc;
   *   $array  = $parser->load($file);
   * </code>
   * @package Spyc
   */
  class Spyc {
    
    /**
     * Load YAML into a PHP array statically
     *
     * The load method, when supplied with a YAML stream (string or file), 
     * will do its best to convert YAML in a file into a PHP array.  Pretty 
     * simple.
     *  Usage: 
     *  <code>
     *   $array = Spyc::YAMLLoad('lucky.yml');
     *   print_r($array);
     *  </code>
     * @access public
     * @return array
     * @param string $input Path of YAML file or string containing YAML
     */
    function YAMLLoad($input) {
      $spyc = new Spyc;
      return $spyc->load($input);
    }
    
    /**
     * Dump YAML from PHP array statically
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.  Pretty simple.  Feel free to
     * save the returned string as nothing.yml and pass it around.
     * @access public
     * @return string
     * @param array $array PHP array
     */
    function YAMLDump($array) {
      $spyc = new Spyc;
      return $spyc->dump($array);
    }
  
    /**
     * Load YAML into a PHP array from an instantiated object
     *
     * The load method, when supplied with a YAML stream (string or file path), 
     * will do its best to convert the YAML into a PHP array.  Pretty simple.
     *  Usage: 
     *  <code>
     *   $parser = new Spyc;
     *   $array  = $parser->load('lucky.yml');
     *   print_r($array);
     *  </code>
     * @access public
     * @return array
     * @param string $input Path of YAML file or string containing YAML
     */
    function load($input) {
      // See what type of input we're talking about
      // If it's not a file, assume it's a string
      if (!empty($input) && file_exists($input)) {
        $yaml = file($input);
      } else {
        $yaml = explode("\n",$input);
      }
      // Initiate some objects and values
      $base              = new YAMLNode;
      $base->indent      = 0;
      $this->_lastIndent = 0;
      $this->_lastNode   = $base->id;
      $this->_inBlock    = false;
      $this->_isInline   = false;
  
      foreach ($yaml as $line) {
        // If the line starts with a # its a comment
        $ifchk = trim($line);
        
        if ($this->_inBlock === false && empty($ifchk)) {
          continue;
        } elseif ($this->_inBlock == true && empty($ifchk)) {
          $last =& $this->_allNodes[$this->_lastNode];
          $last->data[key($last->data)] .= "\n";
        } elseif ($ifchk{0} != '#' && substr($ifchk,0,3) != '---') {
          // Create a new node and get its indent
          $node         = new YAMLNode();
          $node->indent = $this->_getIndent($line);
          
          // Check where the node lies in the hierarchy
          if ($this->_lastIndent == $node->indent) {
            // If we're in a block, add the text to the parent's data
            if ($this->_inBlock === true) {
              $parent =& $this->_allNodes[$this->_lastNode];
              $parent->data[key($parent->data)] .= trim($line).$this->_blockEnd;
            } else {
              // The current node's parent is the same as the previous node's
              if ($this->_allNodes[$this->_lastNode])
              {
                $node->parent = $this->_allNodes[$this->_lastNode]->parent;
              }
            }
          } elseif ($this->_lastIndent < $node->indent) {            
            if ($this->_inBlock === true) {
              $parent =& $this->_allNodes[$this->_lastNode];
              $parent->data[key($parent->data)] .= trim($line).$this->_blockEnd;
            } elseif ($this->_inBlock === false) {
              // The current node's parent is the previous node
              $node->parent = $this->_lastNode;
              
              // If the value of the last node's data was > or | we need to 
              // start blocking i.e. taking in all lines as a text value until 
              // we drop our indent.
              $parent =& $this->_allNodes[$node->parent];
              $this->_allNodes[$node->parent]->children = true;
              if (is_array($parent->data)) {
                $chk = $parent->data[key($parent->data)];
                if ($chk === '>') {
                  $this->_inBlock  = true;
                  $this->_blockEnd = ' ';
                  $parent->data[key($parent->data)] = 
                        str_replace('>','',$parent->data[key($parent->data)]);
                  $parent->data[key($parent->data)] .= trim($line).' ';
                  $this->_allNodes[$node->parent]->children = false;
                  $this->_lastIndent = $node->indent;
                } elseif ($chk === '|') {
                  $this->_inBlock  = true;
                  $this->_blockEnd = "\n";
                  $parent->data[key($parent->data)] =               
                        str_replace('|','',$parent->data[key($parent->data)]);
                  $parent->data[key($parent->data)] .= trim($line)."\n";
                  $this->_allNodes[$node->parent]->children = false;
                  $this->_lastIndent = $node->indent;
                }
              }
            }
          } elseif ($this->_lastIndent > $node->indent) {
            // Any block we had going is dead now
            if ($this->_inBlock === true) {
              $this->_inBlock = false;
              if ($this->_blockEnd = "\n") {
                $last =& $this->_allNodes[$this->_lastNode];
                $last->data[key($last->data)] = 
                      trim($last->data[key($last->data)]);
              }
            }
            
            // We don't know the parent of the node so we have to find it
            // foreach ($this->_allNodes as $n) {
            foreach ($this->_indentSort[$node->indent] as $n) {
              if ($n->indent == $node->indent) {
                $node->parent = $n->parent;
              }
            }
          }
        
          if ($this->_inBlock === false) {
            // Set these properties with information from our current node
            $this->_lastIndent           = $node->indent;
            
            // Set the last node
            $this->_lastNode             = $node->id;
            // Parse the YAML line and return its data
            $node->data = $this->_parseLine($line);
            // Add the node to the master list
            $this->_allNodes[$node->id] = $node;
            // Add a reference to the node in an indent array
            $this->_indentSort[$node->indent][] =& $this->_allNodes[$node->id];
            // Add a reference to the node in a References array if this node
            // has a YAML reference in it.
            if ( 
              ( (is_array($node->data)) && (!@is_array($node->data[key($node->data)])) )
              &&
              ( (preg_match('/^&([^ ]+)/',@$node->data[key($node->data)])) 
                || 
                (preg_match('/^\*([^ ]+)/',@$node->data[key($node->data)])) )
            ) {
                $this->_haveRefs[] =& $this->_allNodes[$node->id];
            } elseif (
              ( (is_array($node->data)) &&
                   (is_array(@$node->data[key($node->data)])) )
            ) {
              // Incomplete reference making code.  Ugly, needs cleaned up.
              foreach ($node->data[key($node->data)] as $d) {
                if ( !is_array($d) && 
                  ( (preg_match('/^&([^ ]+)/',$d)) 
                    || 
                    (preg_match('/^\*([^ ]+)/',$d)) )
                  ) {
                    $this->_haveRefs[] =& $this->_allNodes[$node->id];
                }
              }
            }
          }
        }
      }
      unset($node);
      
      // Here we travel through node-space and pick out references (& and *)
      $this->_linkReferences();
      
      // Build the PHP array out of node-space
      $trunk = $this->_buildArray();
      return $trunk;
    }
  
    /**
     * Dump PHP array to YAML
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.  Pretty simple.  Feel free to
     * save the returned string as tasteful.yml and pass it around.
     * @access public
     * @return string
     * @param array $array PHP array
     */
    function dump($array) {
      // Dumps to some very clean YAML.  We'll have to add some more features
      // and options soon.  And support for folding.
      
      // New YAML document
      $string = "---\n";
      
      // Start at the base of the array and move through it.
      foreach ($array as $key => $value) {
        $string .= $this->_yamlize($key,$value,0);
      }
      return $string;
    }
  
    /**** Private Properties ****/
    
    /**#@+
     * @access private
     * @var mixed
     */ 
    var $_haveRefs;
    var $_allNodes;
    var $_lastIndent;
    var $_lastNode;
    var $_inBlock;
    var $_isInline;
    /**#@+*/

    /**** Private Methods ****/
    
    /**
     * Attempts to convert a key / value array item to YAML
     * @access private
     * @return string
     * @param $key The name of the key
     * @param $value The value of the item
     * @param $indent The indent of the current node
     */    
    function _yamlize($key,$value,$indent) {
      if (is_array($value)) {
        // It has children.  What to do?
        // Make it the right kind of item
        $string = $this->_dumpNode($key,NULL,$indent);
        // Add the indent
        $indent += 2;
        // Yamlize the array
        $string .= $this->_yamlizeArray($value,$indent);
      } elseif (!is_array($value)) {
        // It doesn't have children.  Yip.
        $string = $this->_dumpNode($key,$value,$indent);
      }
      return $string;
    }
    
    /**
     * Attempts to convert an array to YAML
     * @access private
     * @return string
     * @param $array The array you want to convert
     * @param $indent The indent of the current level
     */ 
    function _yamlizeArray($array,$indent) {
      if (is_array($array)) {
        foreach ($array as $key => $value) {
          $string .= $this->_yamlize($key,$value,$indent);
        }
        return $string;
      } else {
        return false;
      }
    }
  
    /**
     * Returns YAML from a key and a value
     * @access private
     * @return string
     * @param $key The name of the key
     * @param $value The value of the item
     * @param $indent The indent of the current node
     */ 
    function _dumpNode($key,$value,$indent) {
      $value  = $this->_doFolding($value,$indent);
      $indent = str_repeat(' ',$indent);
      if (!preg_match('/^[A-Za-z]{1}[A-Za-z0-9 ]*$/',$key)) {
        // It's a sequence
        $string = $indent.'- '.$value."\n";
      } else {
        // It's mapped
        $string = $indent.$key.': '.$value."\n";
      }
      return $string;
    }
    
    /**
     * Folds a string of text, if necessary
     * @access private
     * @return string
     * @param $value The string you wish to fold
     */
    function _doFolding($value,$indent) {
      if (strlen($value) > 40) {
        $indent += 2;
        $indent = str_repeat(' ',$indent);
        $wrapped = wordwrap($value,40,"\n$indent");
        $value   = ">\n".$indent.$wrapped;
      }
      return $value;
    }
  
    /* Methods used in loading */
    
    /**
     * Finds and returns the indentation of a YAML line
     * @access private
     * @return int
     * @param string $line A line from the YAML file
     */
    function _getIndent($line) {
      preg_match('/^\s{1,}/',$line,$match);
      if (!empty($match[0])) {
        $indent = substr_count($match[0],' ');
      } else {
        $indent = 0;
      }
      return $indent;
    }

    /**
     * Parses YAML code and returns an array for a node
     * @access private
     * @return array
     * @param string $line A line from the YAML file
     */
    function _parseLine($line) {
      $line = trim($line);
      
      $array = array();

      // Cut out comments
      if (preg_match('/#(.+)$/',$line)) {
        $explode = explode('# ',$line);
        array_pop($explode);
        $line    = implode('# ',$explode);
      }
      
      if (preg_match('/^-(.*):$/',$line)) {
        // It's a mapped sequence
        $key         = trim(substr(substr($line,1),0,-1));
        $array[$key] = '';
      } elseif (isset($line[0]) && $line[0] == '-' && substr($line,0,3) != '---') {
        // It's a list item but not a new stream
        if (strlen($line) > 1) {
          $value   = trim(substr($line,1));
          // Set the type of the value.  Int, string, etc
          $value   = $this->_toType($value);
          $array[] = $value;
        } else {
          $array[]   = array();
        }
      } elseif (preg_match('/^(.+):/',$line,$key)) {
        // It's a key/value pair most likely
        // If the key is in double quotes pull it out
        if (preg_match('/^(["\'](.*)["\']:)/',$line,$matches)) {
          $value = trim(str_replace($matches[1],'',$line));
          $key   = $matches[2];
        } else {
          // Do some guesswork as to the key and the value
          $explode = explode(':',$line);
          $key     = trim($explode[0]);
          array_shift($explode);
          $value   = trim(implode(':',$explode));
        }

        // Set the type of the value.  Int, string, etc
        $value = $this->_toType($value);
        if (empty($key)) {
          $array[]     = $value;
        } else {
          $array[$key] = $value;
        }
      }
      return $array;
    }
    
    /**
     * Finds the type of the passed value, returns the value as the new type.
     * @access private
     * @param string $value
     * @return mixed
     */
    function _toType($value) {
      if (preg_match('/^["\'](.*)["\']$/',$value,$matches)) {
        $value   = (string)$matches[1];
      } elseif (preg_match('/^\\[(.+)\\]$/',$value,$matches)) {
        // Inline Sequence

        // Take out strings sequences and mappings
        $explode = $this->_inlineEscape($matches[1]);
        
        // Propogate value array
        $value  = array();
        foreach ($explode as $v) {
          $value[] = $this->_toType($v);
        }
      } elseif (strpos($value,': ')!==false && !preg_match('/^{(.+)/',$value)) {
          // It's a map
          $array = explode(': ',$value);
          $key   = trim($array[0]);
          array_shift($array);
          $value = trim(implode(': ',$array));
          $value = $this->_toType($value);
          $value = array($key => $value);
      } elseif (preg_match("/{(.+)}$/",$value,$matches)) {
        // Inline Mapping

        // Take out strings sequences and mappings
        $explode = $this->_inlineEscape($matches[1]);

        // Propogate value array
        $array = array();
        foreach ($explode as $v) {
          $array = array_merge($array,$this->_toType($v));
        }
        $value = $array;
      } elseif (strtolower($value) == 'null' or $value == '' or $value == '~') {
        $value = null;
      } elseif (ctype_digit($value)) {
        $value = (int)$value;
      } elseif (strtolower($value) == 'true' or strtolower($value) == 'on' or $value == '+') {
        $value = true;
      } elseif (strtolower($value) == 'false' or strtolower($value) == 'off' or $value == '-') {
        $value = false;
      } elseif (is_numeric($value)) {
        $value = (float)$value;
      }
      return $value;
    }
    
    /**
     * Used in inlines to check for more inlines or quoted strings
     * @access private
     * @return array
     */
    function _inlineEscape($inline) {
      // There's gotta be a cleaner way to do this...
      // While pure sequences seem to be nesting just fine,
      // pure mappings and mappings with sequences inside can't go very
      // deep.  This needs to be fixed.
      
      // Check for strings
      if (preg_match_all('/"([^"]+)"/',$inline,$strings)) {
        $strings = $strings[1];
        $inline  = preg_replace('/"([^"]+)"/','YAMLString',$inline);
      }
      
      // Check for sequences
      if (preg_match_all('/\[(.+)\]/U',$inline,$seqs)) {
        $inline = preg_replace('/\[(.+)\]/U','YAMLSeq',$inline);
        $seqs   = $seqs[0];
      }
      
      // Check for mappings
      if (preg_match_all('/{(.+)}/U',$inline,$maps)) {
        $inline = preg_replace('/{(.+)}/U','YAMLMap',$inline);
        $maps   = $maps[0];
      }
      
      $explode = explode(', ',$inline);
      
      // Re-add the strings
      if (!empty($strings)) {
        $i = 0;
        foreach ($explode as $key => $value) {
          if ($value == 'YAMLString') {
            $explode[$key] = $strings[$i];
            ++$i;
          }
        }
      }
      
      // Re-add the sequences
      if (!empty($seqs)) {
        $i = 0;
        foreach ($explode as $key => $value) {
          if (strpos($value,'YAMLSeq') !== false) {
            $explode[$key] = str_replace('YAMLSeq',$seqs[$i],$value);
            ++$i;
          }
        }
      }
      
      // Re-add the mappings
      if (!empty($maps)) {
        $i = 0;
        foreach ($explode as $key => $value) {
          if (strpos($value,'YAMLMap') !== false) {
            $explode[$key] = str_replace('YAMLMap',$maps[$i],$value);
            ++$i;
          }
        }
      }
      
      return $explode;
    }
  
    /**
     * Builds the PHP array from all the YAML nodes we've gathered
     * @access private
     * @return array
     */
    function _buildArray() {
      if (!@$this->_indentSort[0])
        return array();

      $trunk = array();
      foreach ($this->_indentSort[0] as $n) {
        if (empty($n->parent)) {
          if (is_array($n->data) && $n->children == true) {
            // This node has children, so we need to find them
            $arr           = $this->_gatherChildren($n->id);
            // We've gathered all our children's data and are ready to use it
            $key           = key($n->data);
            $key           = empty($key) ? 0 : $key;
            // If it's an array, add to it of course
            if (is_array($n->data[$key])) {
              $n->data[$key] = array_merge($n->data[$key],$arr);
            } else {
              $n->data[$key] = $arr;
            }
          } elseif (!is_array($n->data) && $n->children == true) {
            // Same as above, find the children of this node
            $arr       = $this->_gatherChildren($n->id);
            $n->data   = array();
            $n->data[] = $arr;
          }
          // Check for references and copy the needed data to complete them.
          $this->_makeReferences($n);
          // Merge our data with the big array we're building
          $trunk = array_merge($trunk,$n->data);
        }
      }
      return $trunk;
    }
  
    /**
     * Traverses node-space and sets references (& and *) accordingly
     * @access private
     * @return bool
     */
    function _linkReferences() {
      if (is_array($this->_haveRefs)) {
        foreach ($this->_haveRefs as $node) {
          if (!empty($node->data)) {
            $key = key($node->data);
            // If it's an array, don't check.
            if (is_array($node->data[$key])) {  
              foreach ($node->data[$key] as $k => $v) {
                $this->_linkRef($node,$key,$k,$v);
              }
            } else {
              $this->_linkRef($node,$key);
            }
          }
        } 
      }
      return true;
    }
    
    function _linkRef(&$n,$key,$k = NULL,$v = NULL) {
      if (empty($k) && empty($v)) {
        // Look for &refs
        if (preg_match('/^&([^ ]+)/',$n->data[$key],$matches)) {
          // Flag the node so we know it's a reference
          $this->_allNodes[$n->id]->ref = substr($matches[0],1);
          $this->_allNodes[$n->id]->data[$key] = 
                   substr($n->data[$key],strlen($matches[0])+1);
        // Look for *refs
        } elseif (preg_match('/^\*([^ ]+)/',$n->data[$key],$matches)) {
          $ref = substr($matches[0],1);
          // Flag the node as having a reference
          $this->_allNodes[$n->id]->refKey =  $ref;
        }
      } elseif (!empty($k) && !empty($v)) {
        if (preg_match('/^&([^ ]+)/',$v,$matches)) {
          // Flag the node so we know it's a reference
          $this->_allNodes[$n->id]->ref = substr($matches[0],1);
          $this->_allNodes[$n->id]->data[$key][$k] = 
                              substr($v,strlen($matches[0])+1);
        // Look for *refs
        } elseif (preg_match('/^\*([^ ]+)/',$v,$matches)) {
          $ref = substr($matches[0],1);
          // Flag the node as having a reference
          $this->_allNodes[$n->id]->refKey =  $ref;
        }
      }
    }
  
    /**
     * Finds the children of a node and aids in the building of the PHP array
     * @access private
     * @param int $nid The id of the node whose children we're gathering
     * @return array
     */
    function _gatherChildren($nid) {
      $return = array();
      $node   =& $this->_allNodes[$nid];
      foreach ($this->_allNodes as $z) {
        if ($z->parent == $node->id) {
          // We found a child
          if (is_array($z->data) && $z->children == true) {
            // It has children, so repeat the whole process
            $array         = $this->_gatherChildren($z->id);
            // Got an array of data from the children, so we can use that for 
            // our data
            $key           = key($z->data);
            // If it's an array, add to it of course
            if (is_array($z->data[$key])) {
              $z->data[$key] = array_merge($z->data[$key],$array);
            } else {
              $z->data[$key] = $array;
            }
          } elseif (!is_array($z->data) && $z->children == true) {
            // Find children again
            $array     = $this->_gatherChildren($z->id);
            $z->data   = array();
            $z->data[] = $array;
          }
          // Check for references
          $this->_makeReferences($z);
          // Merge with the big array we're returning
          // The big array being all the data of the children of our parent node
          $return = array_merge($return,$z->data);
        }
      }
      return $return;
    }
  
    /**
     * Traverses node-space and copies references to / from this object.
     * @access private
     * @param object $z A node whose references we wish to make real
     * @return bool
     */
    function _makeReferences(&$z) {
      // It is a reference
      if (isset($z->ref)) {
        $key                = key($z->data);
        // Copy the data to this object for easy retrieval later
        $this->ref[$z->ref] =& $z->data[$key];
      // It has a reference
      } elseif (isset($z->refKey)) {
        if (isset($this->ref[$z->refKey])) {
          $key           = key($z->data);
          // Copy the data from this object to make the node a real reference
          $z->data[$key] =& $this->ref[$z->refKey];
        }
      }
      return true;
    }

  }
?>