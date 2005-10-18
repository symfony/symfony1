<?php

/*
 * Python-Markdown
 * ===============
 *
 * Started by [Manfred Stienstra](http://www.dwerg.net/).  Continued and
 * maintained  by [Yuri Takhteyev](http://www.freewisdom.org).
 *
 * Project website: http://www.freewisdom.org/projects/python-markdown
 * Contact: yuri [at] freewisdom.org
 *
 * License: GPL 2 (http://www.gnu.org/copyleft/gpl.html)
 */

/**
 * Markdown formatter class for creating an html document from Markdown text
 */
class sfMarkdown
{
  // all tabs will be expanded to up to this many spaces
  const TAB_LENGTH = 4;

  // a placeholder for the backlink entity
  const FN_BACKLINK_TEXT = 'zz1337820767766393qq';

  // a template for html placeholders
  const HTML_PLACEHOLDER_PREFIX = 'qaodmasdkwaspemas';
  const HTML_PLACEHOLDER = 'qaodmasdkwaspemas%dajkqlsmdqpakldnzsdfls';

  // regular expressions used for catching most Markdown patterns
  private $patterns = array(
      // ========== block-level patterns ==================
      'header' =>          '(#*)([^#]*)(#*)', // # A title
      'reference-def'  =>  '(\ ?\ ?\ ?)\[([^\]]*)\]:\s*([^ ]*)(.*)', // [Google]: http://www.google.com/
      'footnote-def'  =>  '(\ ?\ ?\ ?)\[\^([^\]]*)\]:\s*(.*)', // [^foo]: Footnote text
      'containsline' =>    '([-]*)$|^([=]*)', // -----, =====, etc.
      'ol' =>              '[ ]{0,3}[\d]*\.\s+(.*)', // 1. text
      'ul' =>              '[ ]{0,3}[*+-]\s+(.*)', // "* text"
      'isline1' =>         '(\**)', // ***
      'isline2' =>         '(\-*)', // ---
      'isline3' =>         '(\_*)', // ___
      'tabbed' =>          '((\t)|(    ))(.*)', // an indented line
      'quoted'  =>         '> ?(.*)', // a quoted block ("> ...")

      // ========  in-line patterns ======================

      'backtick' =>        '(.*)\`([^\`]*)\`(.*)', // `e= m*c^2`
      'double-backtick' => '(.*)\`\`(.*)\`\`(.*)',  // ``e=f("`")``
      'escape' =>          '(.*)\\(.)(.*)', // \<
      'emphasis'  =>       '(.*)\*([^\*]*)\*(.*)', // *emphasis*
      'emphasis2'  =>      '(.*)_([^_]*)_(.*)', // _emphasis_
      'link' =>            '(.*)\[(.*)\]\s*\(([^\)]*)\)(.*)', // [text](url)
      'link-angled' =>     '(.*)\[([^\]]*)\]\s*\(<([^\)]*)>\)(.*)', // [text](<url>)
      'image-link' =>      '(.*)\!\[([^\]]*)\]\s*\(([^\)]*)\)(.*)', // ![Alt text](http://image.url.com/)
      'footnote-use' =>    '(.*)\[\^([^\]]*)\](.*)', // blah blah [^1] blah
      'footnote-use-short' =>    '\[\^([^\]]*)\]', // just the [^a]
      'reference-use'  =>  '(.*)\[([^\]]*)\]\s*\[([^\]]*)\](.*)', // [Google][3]
      'image-reference-use'  => '(.*)\!\[([^\]]*)\]\s*\[([^\]]*)\](.*)', // ![Alt text][2]
      'not-strong'  =>     '(.*)( \* )(.*)', // stand-alone * or _
      'strong' =>          '(.*)\*\*(.*)\*\*(.*)', // **strong**
      'strong2' =>         '(.*)__([^_]*)__(.*)', // __strong__
      'strong-em' =>       '(.*)\*\*\*([^_]*)\*\*\*(.*)', // ***strong***
      'strong-em2' =>      '(.*)___([^_]*)___(.*)', // ___strong___
      'autolink'  =>       '(.*)<(http://[^>]*)>(.*)', // <http://www.123.com>
      'automail'  =>       '(.*)<([^> ]*@[^> ]*)>(.*)', // <me@example.com>
      'html'  =>           '(.*)(\<[^\>]*\>)(.*)', // <...>
      'entity'  =>         '(.*)(&[\#a-zA-Z0-9]*;)(.*)', // an entity:
      'empty'  =>          '\s*', // a blank line,
  );

  /**
   * Creates a new Markdown instance.
   *
   * @param text: The text in Markdown format.
   */
  public function __construct ($text)
  {
    $this->regExp = array();

    foreach ($this->patterns->keys() as $key)
    {
      $this->regExp[$key] = sprintf("£^%s$£", $this->patterns[$key]);
    }

    $this->regExp['containsline'] = '£^([-]*)$|^([=]*)$£m';

    $this->regExp['footnote-use-short'] = '£'.$this->patterns['footnote-use-short'].'£m';

    $this->references      = array();
    $this->footnotes       = array();
    $this->footnote_suffix = '-'.int(rand(1000000000));
    $this->used_footnotes  = array();
    $this->rawHtmlBlocks   = array();
    $this->html_counter    = 0; // for counting inline html segments

    $this->_preprocess($text);
  }

  /**
   * Creates the div with class='footnote' and populates it with
   *  the text of the footnotes.
   *
   * @returns: the footnote div as a dom element
   */
  private function _make_footnotes ()
  {
    $div = $this->doc->createElement('div');
    $div->setAttribute('class', 'footnote');
    $hr = $this->doc->createElement('hr');
    $div->appendChild($hr);
    $ol = $this->doc->createElement('ol');
    $div->appendChild($ol);

    $footnotes = [($this->used_footnotes[id], id)
                 for id in $this->footnotes.keys()]
    sort($footnotes);

    foreach ($footnotes as $i => $id)
    {
      $li = $this->doc->createElement('li');
      $li->setAttribute('id', $this->_make_footnote_id($i));

      $this->_processSection($li, $this->footnotes[$id].split("\n"));

      // $li->appendChild($this->doc->createTextNode($this->footnotes[$id]));

      $backlink = $this->doc->createElement('a');
      $backlink->setAttribute('href',  '#'.$this->_make_footnote_ref_id($i));
      $backlink->setAttribute('class', 'footnoteBackLink');
      $backlink->setAttribute('title', 'Jump back to footnote 1 in the text');
      $backlink->appendChild($this->doc->createTextNode(FN_BACKLINK_TEXT));

      $li->appendChild($backlink);
      $ol->appendChild($li);
    }

    return $div;
  }

  /**
   * Transforms the Markdown text into a XHTML body document
   *
   * @returns: An xml.dom.minidom.Document
   */
  private function _transform ()
  {
    $this->doc = new sfMarkdownDocument();
    $this->top_element = $this->doc->createElement("span");
    $this->top_element->appendChild($this->doc->createTextNode('\n'));
    $this->top_element->setAttribute('class', 'markdown');
    $this->doc->appendChild($this->top_element);

    $this->_processSection($this->top_element, $this->lines);
    $this->top_element->appendChild($this->doc->createTextNode('\n'));

    if (keys($this->footnotes))
    {
      $this->top_element->appendChild($this->_make_footnotes());
    }

    return $this->doc;
  }

  private function _record_footnote_use ($match)
  {
    $id = match.group(1);
    $id = trim($id);
    $this->used_footnotes[$id] = count($this->used_footnotes) + 1;
  }

  /**
   *
   *  Replaces underlined headers with hashed headers to avoid
   *   the nead for lookahead later.
   */
  private function _preprocess_headers()
  {
    for i in range(len($this->lines))
    {
      if (!$this->lines[$i])
      {
        continue;
      }

      if ($i + 1 <= count($this->lines) && $this->lines[$i + 1] && in_array($this->lines[$i + 1][0], array('-', '=')))
      {
        $underline = trim($this->lines[$i + 1]);

        if ($underline == str_repeat('=', strlen($underline)))
        {
          $this->lines[$i]     = '# '.trim($this->lines[$i]);
          $this->lines[$i + 1] = '';
        }
        else if ($underline == str_repeat('-', strlen($underline)))
        {
          $this->lines[$i]     = '## '.trim($this->lines[i]);
          $this->lines[$i + 1] = '';
        }
      }
    }
  }

  private function _preprocess_lines()
  {
    // Deal with HR lines (needs to be done before processing lists)

    for i in range(len($this->lines))
    {
      if ($this->_isLine($this->lines[$i]))
      {
        $this->lines[$i] = '<hr />';
      }
    }
  }

  /**
   * Preprocess the source text.  In particular, this includes
   * normalizing new lines and building a set of reference
   * definitions.
   *
   * @param text: The text in Markdown format.
   * @returns: Normalized text without reference definitions.
   */
  private function _preprocess($text)
  {
      // Remove whitespace.
      $text = trim($text);

      // Zap carriage returns.
      $text = str_replace("\r\n", "\n", $text);
      $text = str_replace("\r", "\n",   $text);
      $text .= "\n\n";

      // Replace tabs with spaces
      $text = text.expandtabs(TAB_LENGTH);

      // Split into lines.  (We'll be doing most of the future operations on lines.
      $this->lines = split("\n", $text);

      // HTMLize the headers
      $this->_preprocess_headers();

      $this->_preprocess_lines();

      // Remove the html blocks (we'll store them in a hash for now)
      $this->_removeHtmlBlocks();

      // Extract the footnote definitions
      $this->lines = $this->_handle_footnote_definitions($this->lines);

      // Make a hash of all footnote marks in the text so that we
      // know in what order they are supposed to appear.  (This
      // function call doesn't really substitute anything - it's just
      // a way to get a callback for each occurence.

      $text = "\n".join($this->lines);
      $this->regExp['footnote-use-short'].sub($this->_record_footnote_use, $text)
      $this->lines = split("\n", $text);

      // Extract references
      // E.g., [id]: http://example.com/  "Optional Title Here"
      $text_no_ref = array();

      foreach ($this->lines as $line)
      {
        $m = $this->regExp['reference-def'].match($line);
        if ($m)
        {
          $id    = strtolower(trim($m.group(2)));
          $title = dequote(trim($m.group(4))).replace('"', "&quot;");
          $this->references[$id] = array($m.group(3), $title);
        }
        else
        {
          $text_no_ref[] = $line;
        }
      }

      $this->lines = $text_no_ref #."\n";
  }

  /**
   * An auxiliary method to be passed to _findHead
   */
  public function detabbed_fn($line)
  {
    if (preg_match($this->regExp['tabbed'], $line, $match))
    {
      return $match[4];
    }
    else
    {
      return null;
    }
  }

  /**
   * Recursively finds all footnote definitions in the lines.
   *
   * @param lines: a list of lines of text
   * @returns: a string representing the text with footnote definitions removed
   */
  private function _handle_footnote_definitions ($lines)
  {
    list($i, $id, $footnote) = $this->_find_footnote_definition($lines);

    if ($id)
    {
      $plain = array_splice($lines, 0, $i);
      $plain[] = '';

      list($detabbed, $theRest) = $this->_findHead(array_splice($lines, $i + 1), $this->detabbed_fn, $allowBlank = 1)

      $this->footnotes[id] = $footnote."\n".join("\n", $detabbed);
      $more_plain = $this->_handle_footnote_definitions($theRest);

      return $plain + $more_plain;
    }
    else
    {
      return $lines;
    }
  }

  /**
   *  Finds the first line of a footnote definition.
   *
   * @param lines: a list of lines of text
   * @returns: the index of the line containing a footnote definition
   */
  private function _find_footnote_definition ($lines)
  {
    $counter = 0;
    foreach ($lines as $line)
    {
      if (preg_match($this->regExp['footnote-def'], $line, $match))
      {
        return array($counter, $match[2], $match[3]);
      }

      $counter += 1
    }

    return array($counter, null, null);
  }

  /**
   * Saves an HTML segment for later reinsertion.  Returns a
   * placeholder string that needs to be inserted into the
   * document.
   *
   * @param html: an html segment
   * @returns : a placeholder string
   */
  private function _getPlaceholderForHtml ($html)
  {
    $this->rawHtmlBlocks.append($html);
    $placeholder = sprintf(HTML_PLACEHOLDER, $this->html_counter);
    $this->html_counter += 1;

    return $placeholder;
  }

  /**
   * Removes html blocks from $this->lines
   *
   * @returns: None
   */
  private function _removeHtmlBlocks()
  {
    $new_blocks = array();

    $text = join("\n", $this->lines);
    foreach (split("\n\n", $text) as $block)
    {
      if (($block{0} == '<') || preg_match('/^\s*</s', $block)) && preg_match('/>\s*$/s', $block))
      {
        $new_blocks[] = $this->_getPlaceholderForHtml(trim($block));
      }
      else
      {
        $new_blocks[] = $block;
      }
    }

    $this->lines = split("\n", join("\n\n", $new_blocks));
  }

  /**
   * Process a section of a source document, looking for high
   * level structural elements like lists, block quotes, code
   * segments, html blocks, etc.  Some those then get stripped
   * of their high level markup (e.g. get unindented) and the
   * lower-level markup is processed recursively.
   *
   * @param parent_elem: DOM element to which the content will be added
   * @param lines: a list of lines
   * @param inList: a level
   * @returns: None
   */
  private function _processSection($parent_elem, $lines, $inList = 0, $looseList = 0)
  {
//    debug (lines, "Section: %d" % inList)
    if (!$lines)
    {
      return;
    }

    // Check if this section starts with a list, a blockquote or a code block
    $processFn = array(
      'ul'     => '_processUList',
      'ol'     => '_processOList',
      'quoted' => '_processQuote',
      'tabbed' => '_processCodeBlock',
    );

    foreach(array('ul', 'ol', 'quoted', 'tabbed') as $regexp)
    {
      if (preg_match($this->regExp[regexp], $lines[0], $match)
      {
        call_user_func_array(array($this, $processFn[$regexp]), array($parent_elem, $lines, $inList));
        return;
      }
    }

    // We are NOT looking at one of the high-level structures like
    // lists or blockquotes.  So, it's just a regular paragraph
    // (though perhaps nested inside a list or something else).  If
    // we are NOT inside a list, we just need to look for a blank
    // line to find the end of the block.  If we ARE inside a
    // list, however, we need to consider that a sublist does not
    // need to be separated by a blank line.  Rather, the following
    // markup is legal:
    #
    // * The top level list item
    #
    //     Another paragraph of the list.  This is where we are now.
    //     * Underneath we might have a sublist.
    #

    if ($inList)
    {
      start, theRest = $this->_linesUntil(lines, (lambda line:
                       $this->regExp['ul'].match(line)
                       or $this->regExp['ol'].match(line)
                                        or not line.strip()))

      $this->_processSection(parent_elem, start, inList - 1, looseList = looseList)
      $this->_processSection(parent_elem, theRest, inList - 1, looseList = looseList)
    }

    else : // Ok, so it's just a simple block

        paragraph, theRest = $this->_linesUntil(lines, lambda line:
                                             not line.strip())

        debug(paragraph, "Para: ")

        if len(paragraph) and paragraph[0].startswith('#') :
            m = $this->regExp['header'].match(paragraph[0])
            if m :
                level = len(m.group(1))
                h = $this->doc->createElement("h%d" % level)
                parent_elem->appendChild(h)
                for item in $this->_handleInline(m.group(2)) :
                    h->appendChild(item)
            else :
                print "We've got a problem header!"

        elif paragraph :

            list = $this->_handleInline("\n".join(paragraph))

            if ( parent_elem.nodeName == 'li'
                 and not (looseList or parent_elem.childNodes)):

                #and not parent_elem.childNodes) :
                // If this is the first paragraph inside "li", don't
                // put <p> around it - append the paragraph bits directly
                // onto parent_elem
                el = parent_elem
            else :
                // Otherwise make a "p" element
                el = $this->doc->createElement("p")
                parent_elem->appendChild(el)

            for item in list :
                el->appendChild(item)

        if theRest :
            theRest = theRest[1:]  // skip the first (blank) line
        debug(theRest[:1], "%%")

        $this->_processSection(parent_elem, theRest, inList)
  }

/*
      public function _processUList(self, parent_elem, lines, inList) :
          $this->_processList(parent_elem, lines, inList,
                           listexpr='ul', tag = 'ul')

      public function _processOList(self, parent_elem, lines, inList) :
          $this->_processList(parent_elem, lines, inList,
                           listexpr='ol', tag = 'ol')


      public function _processList(self, parent_elem, lines, inList, listexpr, tag) :
          """
             Given a list of document lines starting with a list item,
             finds the end of the list, breaks it up, and recursively
             processes each list item and the remainder of the text file.

             @param parent_elem: DOM element to which the content will be added
             @param lines: a list of lines
             @param inList: a level
             @returns: None
          """

          ul = $this->doc->createElement(tag)  // ul might actually be '<ol>'
          parent_elem->appendChild(ul)

          looseList = 0

          // Make a list of list items
          items = [] 
          item = -1

          i = 0  // a counter to keep track of where we are

          for line in lines :

              loose = 0
              if not line.strip() :
                  // If we see a blank line, this _might_ be the end of the list
                  i += 1
                  loose = 1

                  // Find the next non-blank line
                  for j in range(i, len(lines)) :
                      if lines[j].strip() :
                          next = lines[j]
                          break
                  else :
                      // There is no more text => end of the list
                      break

                  // Check if the next non-blank line is still a part of the list
                  if ( $this->regExp[listexpr].match(next) or
                       $this->regExp['tabbed'].match(next) ):
                      items[item].append(line)
                      looseList = loose or looseList
                      continue
                  else :
                      break // found end of the list

              // Now we need to detect list items (at the current level)
              // while also detabing child elements if necessary

              for expr in [listexpr, 'tabbed']:

                  m = $this->regExp[expr].match(line)
                  if m :
                      if expr == listexpr :  // We are looking at a new item
                          if m.group(1) : 
                              items.append([m.group(1)])
                              item += 1
                          else :
                              debug(item, "Item: ")
                      elif expr == 'tabbed' :  // This line needs to be detabbed
                          items[item].append(m.group(4)) #after the 'tab'

                      i += 1
                      break
              else :
                  items[item].append(line)  // Just regular continuation 
          else :
              i += 1

          // Add the dom elements
          for item in items :
              li = $this->doc->createElement("li")
              ul->appendChild(li)
              debug(item, "LI:")
              $this->_processSection(li, item, inList + 1, looseList = looseList)

          // Process the remaining part of the section
          $this->_processSection(parent_elem, lines[i:], inList)


      public function _linesUntil(self, lines, condition) :
          """ A utility function to break a list of lines upon the
              first line that satisfied a condition.  The condition
              argument should be a predicate function.
              """

          i = -1
          for line in lines :
              i += 1
              if condition(line) : break
          else :
              i += 1
          return lines[:i], lines[i:]

      public function _processQuote(self, parent_elem, lines, inList) :
          """
             Given a list of document lines starting with a quote finds
             the end of the quote, unindents it and recursively
             processes the body of the quote and the remainder of the
             text file.

             @param parent_elem: DOM element to which the content will be added
             @param lines: a list of lines
             @param inList: a level
             @returns: None
          """

          dequoted = []
          i = 0
          for line in lines :
              m = $this->regExp['quoted'].match(line)
              if m :
                  dequoted.append(m.group(1))
                  i += 1
              else :
                  break
          else :
              i += 1

          blockquote = $this->doc->createElement('blockquote')
          parent_elem->appendChild(blockquote)

          $this->_processSection(blockquote, dequoted, inList)
          $this->_processSection(parent_elem, lines[i:], inList)




      public function _findHead(self, lines, fn, allowBlank=0) :

          """
             Functional magic to help determine boundaries of indented
             blocks.

             @param lines: an array of strings
             @param fn: a function that returns a substring of a string
                        if the string matches the necessary criteria
             @param allowBlank: specifies whether it's ok to have blank
                        lines between matching functions
             @returns: a list of post processes items and the unused
                        remainder of the original list

          """

          items = [] 
          item = -1

          i = 0 // to keep track of where we are

          for line in lines :

              if not line.strip() and not allowBlank:
                  return items, lines[i:]

              if not line.strip() and allowBlank:
                  // If we see a blank line, this _might_ be the end
                  i += 1

                  // Find the next non-blank line
                  for j in range(i, len(lines)) :
                      if lines[j].strip() :
                          next = lines[j]
                          break
                  else :
                      // There is no more text => this is the end
                      break

                  // Check if the next non-blank line is still a part of the list

                  part = fn(next)

                  if part :
                      items.append("")
                      continue
                  else :
                      break // found end of the list

              part = fn(line)

              if part :
                  items.append(part)
                  i += 1
                  continue
              else :
                  return items, lines[i:]
          else :
              i += 1

          return items, lines[i:]


      public function _processCodeBlock(self, parent_elem, lines, inList) :
          """
             Given a list of document lines starting with a code block
             finds the end of the block, puts it into the dom verbatim
             wrapped in ("<pre><code>") and recursively processes the 
             the remainder of the text file.

             @param parent_elem: DOM element to which the content will be added
             @param lines: a list of lines
             @param inList: a level
             @returns: None
          """

          detabbed, theRest = $this->_findHead(lines, $this->detabbed_fn,
                                             allowBlank = 1)

          pre = $this->doc->createElement('pre')
          code = $this->doc->createElement('code')
          parent_elem->appendChild(pre)
          pre->appendChild(code)
          text = "\n".join(detabbed).rstrip()+"\n"
          code->appendChild($this->doc->createTextNode(text))
          $this->_processSection(parent_elem, theRest, inList)


      public function _handleInline(self,  line):
          """
          Transform a Markdown line with inline elements to an XHTML fragment.

          Note that this function works recursively: we look for a
          pattern, which usually splits the paragraph in half, and then
          call this function on the two parts.  Also note that all the
          regular expressions used in this function try to capture the
          whole block.  For this reason, they all start with '^' and end
          with '!'.  Finally, the order in which regular expressions are
          applied is very important - e.g. if we first replace
          http://.../ links with <a> tags and _then_ try to replace
          inline html, we would end up with a mess.  So, we apply the
          expressions in the following order:

          * escape and backticks have to go before everything else, so
            that we can preempt any markdown patterns by escaping them.

          * then we handle auto-links (must be done before inline html)

          * then we handle inline HTML.  At this point we will simply
            replace all inline HTML strings with a placeholder and add
            the actual HTML to a hash.

          * then inline images (must be done before links)

          * then bracketed links, first regular then reference-style

          * finally we apply strong and emphasis

          @param item: A block of Markdown text
          @return: A list of xml.dom.minidom elements
          """
          if not(line):
              return [$this->doc->createTextNode(' ')]
          // two spaces at the end of the line denote a <br/>
          if line.endswith('  '):
              list = $this->_handleInline( line.rstrip())
              list.append($this->doc->createElement('br'))
              return list

          for pattern in ['double-backtick', 'backtick', 'escape', 
                          'image-link',
                          'footnote-use',
                          'image-reference-use',
                          'reference-use',
                          'link-angled', 'link',                        
                          'autolink',
                          'automail',
                          'html', 'entity',
                          'not-strong',
                          'strong-em', 'strong-em2',
                          'strong', 'strong2',
                          'emphasis', 'emphasis2'] :
              list = $this->_applyPattern( line, pattern)
              if list: return list

          return [$this->doc->createTextNode(line)]

      public function _make_footnote_id(self, num) :
          return 'fn%d%s' % (num, $this->footnote_suffix)

      public function _make_footnote_ref_id(self, num) :
          return 'fnr%d%s' % (num, $this->footnote_suffix)

      public function _applyPattern(self,  line, pattern) :

          """ Given a pattern name, this function checks if the line
              fits the pattern, creates the necessary elements and
              recursively calls _handleInline (via. _inlineRecurse)

          @param line: the text to be processed
          @param pattern: the pattern to be checked

          @returns: the appropriate newly created DOM element if the
          pattern matches, None otherwise.
          """

          m = $this->regExp[pattern].match(line)

          // if we didn't get a match, move on
          if not m :
              return None

          // if we did, let's see what we were looking for

          simple_tags = { 'emphasis' : 'em',
                          'emphasis2' : 'em',
                          'strong' : 'strong',
                          'strong2' : 'strong',
                          'strong-em' : 'strong,em',
                          'strong-em2' : 'strong,em',
                          'double-backtick' : 'code',
                          'backtick' : 'code',
                          'escape' : '',
                          'not-strong' : ''}

          if pattern in simple_tags.keys() :

              tag = simple_tags[pattern]
              return $this->_inlineRecurse( m.group(1), m.group(3),
                                          m.group(2), tag)

          if pattern in ['html', 'entity'] : 
              place_holder = $this->_getPlaceholderForHtml(m.group(2))

              return $this->_inlineRecurse( m.group(1), m.group(3),
                                          place_holder, None)


          if pattern in ['link', 'link-angled'] :

              attributes = []
              parts = m.group(3).split()

              if parts :
                  attributes.append((('href'), parts[0]))
              else :
                  attributes.append((('href'), ""))
              if len(parts) > 1 :
                  // we also got a title
                  title = " ".join(parts[1:]).strip()
                  title = dequote(title).replace('"', "&quot;")
                  attributes.append(('title', title))

              return $this->_inlineRecurse( m.group(1), m.group(4),
                                          m.group(2), 'a',
                                          attributes = attributes )

          if pattern == 'image-link' :

              attributes = [('src', m.group(3)),
                            ('alt', m.group(2))]

              return $this->_inlineRecurse( m.group(1), m.group(4),
                                          '', 'img',
                                          attributes = attributes )

          if pattern in ['footnote-use'] :

              id = m.group(2)

              num = $this->used_footnotes[id]

              attributes = [ ('href', '#' + $this->_make_footnote_id(num)),
                             ('id', $this->_make_footnote_ref_id(num))]


              return $this->_inlineRecurse( m.group(1), m.group(3),
                                          str(num),
                                          'sup,a',
                                          attributes = attributes)

          if pattern in ['reference-use', 'image-reference-use'] :

              if m.group(3) :
                  id = m.group(3)
              else :
                  id = m.group(2)  // if we got something like "[Google][]"
                                   // we'll use "Google" as the id
              id = id.strip().lower()

              if not $this->references.has_key(id) :
                  #If this reference isn't defined, ignore the pattern
                  #return None

                  href, title = (id, id)

              else :

                  href, title = $this->references[id]

              if pattern == 'image-reference-use' :
                  tag = 'img'
                  attributes = [('src', href), ('alt', m.group(2))]
                  text = ""
              else :
                  tag = 'a'
                  attributes = [('href', href)]
                  text = m.group(2)

              if title :
                  attributes.append(('title', title))

              return $this->_inlineRecurse( m.group(1), m.group(4),
                                          text, tag,
                                          attributes = attributes )


          if pattern in ['autolink', 'automail'] :

              if pattern == 'automail' :
                  mailto = "mailto:" + m.group(2)
                  mailto = "".join(['&#%d;' % ord(letter) for letter in mailto])

                  link = $this->_getPlaceholderForHtml(mailto)

              else:
                  link = m.group(2)
              attributes = [('href', link)]
              return $this->_inlineRecurse( m.group(1), m.group(3),
                                          m.group(2), 'a', attributes)


      public function _inlineRecurse(self, left, right, text, tag, attributes = []) :

          """ Recursively calls _handleInline on the "left" and "right"
          parameters.  Then puts the results together with a new tag
          with a text element inside.

          @param left: the text left of the match (will be processed
          recursively)

          @param right: the text right of the match (will be processed
          recursively)

          @param text: a text for a text element

          @param tag: a tag to be placed around the text element

          @attributes : attribute/value pairs to be added to the tag

          @return: a list of dom nodes corresponding to left + tag + right.

          """

          txtEl = $this->doc->createTextNode(text)


          if tag :

              tags = tag.split(",")
              el = $this->doc->createElement(tags[0])
              last = el

              if len(tags) > 1 :
                  // Let's worry about tripple-tags when we have them
                  el2 = $this->doc->createElement(tags[1])
                  last = el2
                  el->appendChild(el2)

              if text :
                  last->appendChild(txtEl)
              if attributes :
                  for attr, value in attributes :
                      placeholder = $this->_getPlaceholderForHtml(value)
                      last->setAttribute(attr, placeholder)

          left_list = $this->_handleInline( left)
          right_list = $this->_handleInline( right)

          if tag :
              left_list.append(el)
          else :
              left_list.append(txtEl)

          left_list.extend(right_list)
          return left_list

      public function _handleParagraph(self, block, noinline=False):
          """
          Transform a Markdown paragraph to an XHTML part

          @param block: A block of Markdown text 
          @param noinline: Whether to live inline elements alone or not
          @return: An xml.dom.minidom element
          """

          return $this->_handleInline( block.replace("\n", " "))

          #el = $this->doc->createElement('p')

          if noinline:
              #el->appendChild($this->doc->createTextNode(block))
              parent->appendChild($this->doc->createTextNode(block))
          else :
              for item in $this->_handleInline( block.replace("\n", " ")) :
                  parent->appendChild(item)

          return parent

      public function _isLine(self, block) :

          """
              Determines if a block should be replaced with an <HR>
          """

          if block.startswith("    ") : return 0  // a code block

          text = "".join([x for x in block if not x.isspace()])

          if len(text) <= 2 :
              return 0

          for pattern in ['isline1', 'isline2', 'isline3'] :
              m = $this->regExp[pattern].match(text)
              if (m and m.group(1)) :
                  return 1
          else:

              return 0


      public function __str__(self):
          """
          Return the document in XHTML format.

          @returns: A serialized XHTML body.
          """
          doc = $this->_transform()
          xml = doc.toxml() 

          // Let's stick in all the raw html pieces
          for i in range($this->html_counter) :
              xml = xml.replace("<p>%s\n</p>" % (HTML_PLACEHOLDER % i),
                                $this->rawHtmlBlocks[i] + "\n")
              xml = xml.replace(HTML_PLACEHOLDER % i, $this->rawHtmlBlocks[i])

          xml = xml.replace(FN_BACKLINK_TEXT, "&#8617;")

          // And return everything but the first line (<?xml...>)

          xml = xml.strip()[23:-7]

          return xml


      toString = __str__


  public function markdown(text):
      return Markdown(text).toString() 

  if __name__ == '__main__':
      print Markdown(file(sys.argv[1]).read())




      public function print_error(string):
          """
          Print an error string to stderr
          """
          sys.stderr.write(string +'\n')


      public function dequote(string) :
          """ Removes quotes from around a string """
          if ( ( string.startswith('"') and string.endswith('"'))
               or (string.startswith("'") and string.endswith("'")) ) :
              return string[1:-1]
          else :
              return string

      #debug_file = open("md.log", "w")

      public function debug(data, prefix) :
          return
          debug_file.write( "---------- %s ------------------\n" % prefix )
          if type(data) == type('asdfa') :
              debug_file.write(data + "\n")

          elif type(data) == type([]) :
              for x in data :
                  debug_file.write(x + "\n")

*/
}

class sfMarkdownDocument
{
  private
    $top,
    $parent;

  public function appendChild ($child)
  {
    $this->top     = $child;
    $child->parent = $this;
  }

  public function createElement ($tag)
  {
    return new sfMarkdownElement($tag);
  }

  public function createTextNode (text)
  {
    return new sfMarkdownTextNode($text);
  }

  public function toxml ()
  {
    return $this->top->toxml();
  }
}

class sfMarkdownElement
{
  private
    $type              = '',
    $nodeName          = '',
    $attributes        = array(),
    $attributes_values = array(),
    childNodes         = array();

  public function __construct ($tag)
  {
    $this->type     = 'element';
    $this->nodeName = tag;
  }

  public function setAttribute ($attr, $value)
  {
    if (!array_key_exists($attr, $this->attributes)
    {
      $this->attributes->append($attr);
    }

    $this->attribute_values[$attr] = $value;
  }

  public function appendChild ($child)
  {
    $this->childNodes->append($child);
    $child->parent = $this;
  }

  public function toXml ()
  {
    $buffer = '';

    if (in_array($this->nodeName, array('h1', 'h2', 'h3', 'h4')))
    {
      $buffer .= "\n";
    }
    else if ($this->nodeName == 'li')
    {
      $buffer .= "\n ";
    }
    
    $buffer .= '<'.$this->nodeName;

    foreach ($this->attributes as $attr)
    {
      $buffer .= sprintf(' %s="%s"', $attr, $this->attribute_values[$attr]);
    }

    if ($this->childNodes)
    {
      $buffer .= '>';
      foreach ($this->childNodes as $child)
      {
        $buffer .= $child->toXml();
      }

      if ($this->nodeName == 'p')
      {
        $buffer .= "\n";
      }
      else if ($this->nodeName == 'li')
      {
        $buffer .= "\n ";
      }
      $buffer .= sprintf('</%s>', $this->nodeName);
    }
    else
    {
      $buffer .= '/>';
    }

    if (in_array($this->nodeName, array('p', 'li', 'ul', 'ol', 'h1', 'h2', 'h3', 'h4')))
    {
      $buffer .= "\n";
    }

    return $buffer;
  }
}

class sfMarkdownTextNode
{
  private
    $type  = '',
    $value = '';

  public function __construct ($text)
  {
    $this->type  = 'text';
    $this->value = text;
  }

  public function toXml ()
  {
    $text = $this->value;

    if (!preg_match('/'.HTML_PLACEHOLDER_PREFIX.'/', $text))
    {
      if ($this->parent->nodeName == 'p')
      {
        $text = str_replace("\n", "\n   ", $text);
      }
      else if ($this->parent->nodeName == 'li' && $this->parent->childNodes[0] === $this)
      {
        $text = "\n     ".str_replace("\n", "\n     ", $text);
      }
    }

    $text = str_replace('&', '&amp;', $text);
    $text = str_replace('<', '&lt;',  $text);
    $text = str_replace('>', '&gt;',  $text);

    return $text;
  }
}

?>