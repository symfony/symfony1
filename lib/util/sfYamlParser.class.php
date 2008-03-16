<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfYamlInline.class.php');

/**
 * sfYamlParser class.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfYamlParser
{
  protected
    $value         = '',
    $offset        = 0,
    $lines         = array(),
    $currentLineNb = -1,
    $currentLine   = '';

  /**
   * Constructor
   *
   * @param integer The offset of YAML document (used for line numbers in error messages)
   */
  public function __construct($offset = 0)
  {
    $this->offset = $offset;
  }

  /**
   * Parses a YAML string to a PHP value.
   *
   * @param  string A YAML string
   *
   * @return mixed  A PHP value
   */
  public function parse($value)
  {
    $this->value = $this->cleanup($value);
    $this->currentLineNb = -1;
    $this->currentLine = '';
    $this->lines = explode("\n", $this->value);

    $data = array();
    while ($this->moveToNextLine())
    {
      if ($this->isCurrentLineEmpty())
      {
        continue;
      }

      // tab?
      if (preg_match('#^\t+#', $this->currentLine))
      {
        throw new InvalidArgumentException(sprintf('A YAML file cannot contain tabs as indentation at line %d (%s).', $this->getRealCurrentLineNb(), $this->currentLine));
      }

      if (preg_match('#^\-(\s+(?P<value>.+?))?\s*$#', $this->currentLine, $values))
      {
        // array
        if (!isset($values['value']) || '' == trim($values['value'], ' ') || 0 === strpos(ltrim($values['value'], ' '), '#'))
        {
          $c = $this->getRealCurrentLineNb() + 1;
          $parser = new sfYamlParser($c);
          $data[] = $parser->parse($this->getNextEmbedBlock());
        }
        else
        {
          $data[] = $this->parseValue($values['value']);
        }
      }
      else if (preg_match('#^(?P<key>[^ ].*?) *\:(\s+(?P<value>.+?))?\s*$#', $this->currentLine, $values))
      {
        $key = sfYamlInline::parseScalar($values['key']);

        // hash
        if (!isset($values['value']) || '' == trim($values['value'], ' ') || 0 === strpos(ltrim($values['value'], ' '), '#'))
        {
          // if next line is less indented or equal, then it means that the current value is null
          if ($this->isNextLineIndented())
          {
            $data[$key] = null;
          }
          else
          {
            $c = $this->getRealCurrentLineNb() + 1;
            $parser = new sfYamlParser($c);
            $data[$key] = $parser->parse($this->getNextEmbedBlock());
          }
        }
        else
        {
          $data[$key] = $this->parseValue($values['value']);
        }
      }
      else
      {
        // one liner?
        if (1 == count(explode("\n", rtrim($this->value, "\n"))))
        {
          return sfYamlInline::load($this->lines[0]);
        }

        throw new InvalidArgumentException(sprintf('Unable to parse line %d (%s).', $this->getRealCurrentLineNb(), $this->currentLine));
      }
    }

    return empty($data) ? null : $data;
  }

  /**
   * Returns the current line number (takes the offset into account).
   *
   * @return integer The current line number
   */
  protected function getRealCurrentLineNb()
  {
    return $this->currentLineNb + $this->offset;
  }

  /**
   * Returns the current line indentation.
   *
   * @returns integer The current line indentation
   */
  protected function getCurrentLineIndentation()
  {
    return strlen($this->currentLine) - strlen(ltrim($this->currentLine, ' '));
  }

  /**
   * Returns the next embed block of YAML.
   *
   * @param string A YAML string
   */
  protected function getNextEmbedBlock()
  {
    $this->moveToNextLine();

    $newIndent = $this->getCurrentLineIndentation();

    if (!$this->isCurrentLineEmpty() && 0 == $newIndent)
    {
      throw new InvalidArgumentException(sprintf('Indentation problem at line %d (%s)', $this->getRealCurrentLineNb(), $this->currentLine));
    }

    $data = array(substr($this->currentLine, $newIndent));

    while ($this->moveToNextLine())
    {
      if ($this->isCurrentLineEmpty())
      {
        if (!$this->isCurrentLineComment())
        {
          $data[] = substr($this->currentLine, $newIndent);
        }

        continue;
      }

      $indent = $this->getCurrentLineIndentation();

      if (preg_match('#^(?P<text> *)$#', $this->currentLine, $match))
      {
        // empty line
        $data[] = $match['text'];
      }
      else if ($indent >= $newIndent)
      {
        $data[] = substr($this->currentLine, $newIndent);
      }
      else if (0 == $indent)
      {
        $this->moveToPreviousLine();

        break;
      }
      else
      {
        throw new InvalidArgumentException(sprintf('Indentation problem at line %d (%s)', $this->getRealCurrentLineNb(), $this->currentLine));
      }
    }

    return implode("\n", $data);
  }

  /**
   * Moves the parser to the next line.
   */
  protected function moveToNextLine()
  {
    if ($this->currentLineNb >= count($this->lines) - 1)
    {
      return false;
    }

    $this->currentLine = $this->lines[++$this->currentLineNb];

    return true;
  }

  /**
   * Moves the parser to the previous line.
   */
  protected function moveToPreviousLine()
  {
    $this->currentLine = $this->lines[--$this->currentLineNb];
  }

  /**
   * Parses a YAML value.
   *
   * @param  string A YAML value
   *
   * @return mixed  A PHP value
   */
  protected function parseValue($value)
  {
    switch ($value)
    {
      case '|':
        return $this->parseFoldedScalar("\n", '');
      case '>':
        return $this->parseFoldedScalar(' ', '');
      case '|+':
        return $this->parseFoldedScalar("\n", '+');
      case '>+':
        return $this->parseFoldedScalar(' ', '+');
      case '|-':
        return $this->parseFoldedScalar("\n", '-');
      case '>-':
        return $this->parseFoldedScalar(' ', '-');
      default:
        return sfYamlInline::load($value);
    }
  }

  /**
   * Parses a folded scalar.
   *
   * @param  string The separator that was used to begin this folded scalar
   * @param  string The indicator that was used to begin this folded scalar
   *
   * @return string The text value
   */
  protected function parseFoldedScalar($separator, $indicator = '')
  {
    $this->moveToNextLine();

    if (!preg_match('#^(?P<indent> +)(?P<text>.*)$#', $this->currentLine, $matches))
    {
      throw new InvalidArgumentException(sprintf('Wrong indentation at line %d (%s)', $this->getRealCurrentLineNb(), $this->currentLine));
    }

    $textIndent = $matches['indent'];

    $text = $matches['text'].$separator;
    while ($this->currentLineNb + 1 < count($this->lines))
    {
      $this->moveToNextLine();

      if (preg_match('#^'.$textIndent.'(?P<text>.+)$#', $this->currentLine, $matches))
      {
        $text .= $matches['text'].$separator;
      }
      else if (preg_match('#^(?P<text> *)$#', $this->currentLine, $matches))
      {
        $text .= preg_replace('#^ {1,'.strlen($textIndent).'}#', '', $matches['text'])."\n";
      }
      else
      {
        $this->moveToPreviousLine();

        break;
      }
    }

    if (' ' == $separator)
    {
      // replace last separator by a newline
      $text = preg_replace('/ (\n*)$/', "\n$1", $text);
    }

    switch ($indicator)
    {
      case '':
        $text = preg_replace('#\n+$#s', "\n", $text);
        break;
      case '+':
        break;
      case '-':
        $text = preg_replace('#\n+$#s', '', $text);
        break;
    }

    return $text;
  }

  /**
   * Returns true if the next line is indented.
   *
   * @return Boolean Returns true if the next line is indented, false otherwise
   */
  protected function isNextLineIndented()
  {
    $currentIndentation = $this->getCurrentLineIndentation();
    $notEOF = $this->moveToNextLine();

    while ($this->isCurrentLineEmpty() && $notEOF)
    {
      $notEOF = $this->moveToNextLine();
    }

    if (false === $notEOF)
    {
      return false;
    }

    $ret = false;
    if ($this->getCurrentLineIndentation() <= $currentIndentation)
    {
      $ret = true;
    }

    $this->moveToPreviousLine();

    return $ret;
  }

  /**
   * Returns true if the current line is empty or if it is a comment line.
   *
   * @return Boolean Returns true if the current line is empty or if it is a comment line, false otherwise
   */
  protected function isCurrentLineEmpty()
  {
    return '' == trim($this->currentLine, ' ') || $this->isCurrentLineComment();
  }

  /**
   * Returns true if the current line is a comment line.
   *
   * @return Boolean Returns true if the current line is a comment line, false otherwise
   */
  protected function isCurrentLineComment()
  {
    return 0 === strpos(ltrim($this->currentLine, ' '), '#');
  }

  /**
   * Cleanups a YAML string to be parsed.
   *
   * @param  string The input YAML string
   *
   * @return string A cleaned up YAML string
   */
  protected function cleanup($value)
  {
    $value = str_replace(array("\r\n", "\r"), "\n", $value);

    if (!preg_match("#\n$#", $value))
    {
      $value .= "\n";
    }

    // strip YAML header
    preg_replace('#^\%YAML[: ][\d\.]+.*\n#s', '', $value);

    // remove ---
    $value = preg_replace('#^\-\-\-.*?\n#s', '', $value);

    return $value;
  }
}
