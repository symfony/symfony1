<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * TextHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

  /*
      # Truncates +text+ to the length of +length+ and replaces the last three characters with the +truncate_string+
      # if the +text+ is longer than +length+.
  */
  function truncate_text($text, $length = 30, $truncate_string = '...')
  {
    if ($text == '') return '';
    if (strlen($text) > $length)
      return substr($text, 0, $length - strlen($truncate_string)).$truncate_string;
    else
      return $text;

  }

  /*
      # Highlights the +phrase+ where it is found in the +text+ by surrounding it like
      # <strong class="highlight">I'm a highlight phrase</strong>. The highlighter can be specialized by
      # passing +highlighter+ as single-quoted string with \1 where the phrase is supposed to be inserted.
      # N.B.: The +phrase+ is sanitized to include only letters, digits, and spaces before use.
  */
  function highlight_text($text, $phrase, $highlighter = '<strong class="highlight">\\1</strong>')
  {
    if ($text == '')
      return '';
    else if ($phrase != '')
      return preg_replace('/('.preg_quote($phrase).')/i', $highlighter, $text);
    else
      return $text;
  }

  /*
      # Extracts an excerpt from the +text+ surrounding the +phrase+ with a number of characters on each side determined
      # by +radius+. If the phrase isn't found, nil is returned. Ex:
      #   excerpt("hello my world", "my", 3) => "...lo my wo..."
  */
  function excerpt_text($text, $phrase, $radius = 100, $excerpt_string = '...')
  {
    if ($text == '')
      return '';
    else if ($phrase != '')
    {
      $phrase = preg_quote($phrase);

      $found_pos = strpos(strtolower($text), strtolower($phrase));
      if ($found_pos !== false)
      {
        $start_pos = max($found_pos - $radius, 0);
        $end_pos = min($found_pos + strlen($phrase) + $radius, strlen($text));

        $prefix = ($start_pos > 0) ? $excerpt_string : '';
        $postfix = $end_pos < strlen($text) ? $excerpt_string : '';

        return $prefix.substr($text, $start_pos, $end_pos - $start_pos).$postfix;
      }
    }
    else
      return '';
  }

  /*
      # Returns +text+ transformed into html using very simple formatting rules
      # Surrounds paragraphs with <tt>&lt;p&gt;</tt> tags, and converts line breaks into <tt>&lt;br /&gt;</tt>
      # Two consecutive newlines(<tt>\n\n</tt>) are considered as a paragraph, one newline (<tt>\n</tt>) is
      # considered a linebreak, three or more consecutive newlines are turned into two newlines
  */
  function simple_format_text($text)
  {
    $text = sfToolkit::pregtr($text, array("/(\r\n|\r)/"        => "\n",               // lets make them newlines crossplatform
                                           "/\n{3,}/"           => "\n\n",             // zap dupes
                                           "/\n\n/"             => "</p>\\0<p>",       // turn two newlines into paragraph
                                           "/([^\n])\n([^\n])/" => "\\1\n<br />\\2")); // turn single newline into <br/>

    return '<p>'.$text.'</p>'; // wrap the first and last line in paragraphs before we're done
  }

  /*
      # Turns all urls and email addresses into clickable links. The +link+ parameter can limit what should be linked.
      # Options are :all (default), :email_addresses, and :urls.
      #
      # Example:
      #   auto_link("Go to http://www.rubyonrails.com and say hello to david@loudthinking.com") =>
      #     Go to <a href="http://www.rubyonrails.com">http://www.rubyonrails.com</a> and
      #     say hello to <a href="mailto:david@loudthinking.com">david@loudthinking.com</a>
  */
  function auto_link_text($text, $link = 'all')
  {
    if ($link == 'all')
    {
      return _auto_link_urls(_auto_link_email_addresses($text));
    }
    else if ($link == 'email_addresses')
    {
      return _auto_link_email_addresses($text);
    }
    else if ($link == 'urls')
    {
      return _auto_link_urls($text);
    }
  }

  /*
      # Turns all links into words, like "<a href="something">else</a>" to "else".
  */
  function strip_links_text($text)
  {
    return preg_replace('/<a.*>(.*)<\/a>/m', '\\1', $text);
  }

/*
      # Attempts to pluralize the +singular+ word unless +count+ is 1. See source for pluralization rules.
      def pluralize(count, singular, plural = nil)
         "#{count} " + if count == 1
          singular
        elsif plural
          plural
        elsif Object.const_defined?("Inflector")
          Inflector.pluralize(singular)
        else
          singular + "s"
        end
      end

      begin
        require "redcloth"

        # Returns the text with all the Textile codes turned into HTML-tags.
        # <i>This method is only available if RedCloth can be required</i>.
        def textilize(text)
          text.blank? ? "" : RedCloth.new(text, [ :hard_breaks ]).to_html
        end

        # Returns the text with all the Textile codes turned into HTML-tags, but without the regular bounding <p> tag.
        # <i>This method is only available if RedCloth can be required</i>.
        def textilize_without_paragraph(text)
          textiled = textilize(text)
          if textiled[0..2] == "<p>" then textiled = textiled[3..-1] end
          if textiled[-4..-1] == "</p>" then textiled = textiled[0..-5] end
          return textiled
        end
      rescue LoadError
        # We can't really help what's not there
      end

      begin
        require "bluecloth"

        # Returns the text with all the Markdown codes turned into HTML-tags.
        # <i>This method is only available if BlueCloth can be required</i>.
        def markdown(text)
          text.blank? ? "" : BlueCloth.new(text).to_html
        end
      rescue LoadError
        # We can't really help what's not there
      end

      # Turns all links into words, like "<a href="something">else</a>" to "else".
      def strip_links(text)
        text.gsub(/<a.*>(.*)<\/a>/m, '\1')
      end

      private
    end
  end

*/

  /*
        # Turns all urls into clickable links.
  */
  function _auto_link_urls($text)
  {
    return preg_replace_callback(
      '/(<\w+.*?>|[^=!:\'"\/]|^)((?:http[s]?:\/\/)|(?:www\.))([^\s<]+\/?)([[:punct:]]|\s|<|$)/',
      create_function('$matches', '
        if (preg_match("/<a\s/i", $matches[1]))
          return $matches[0];
        else
          return $matches[1].\'<a href="\'.($matches[2] == "www." ? "http://www." : $matches[2]).$matches[3].\'">\'.$matches[2].$matches[3].\'</a>\'.$matches[4];
      ')
    , $text);
  }

  /*
        # Turns all email addresses into clickable links.
  */
  function _auto_link_email_addresses($text)
  {
    return preg_replace('/([\w\.!#\$%\-+.]+@[A-Za-z0-9\-]+(\.[A-Za-z0-9\-]+)+)/', '<a href="mailto:\\1">\\1</a>', $text);
  }

?>