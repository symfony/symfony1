<?php

class sfWhitespaceOptimizer
{
  public static function optimize($tokens)
  {
    $newTokens = array();

    $blank = false;
    foreach ($tokens as $token)
    {
      if (is_string($token))
      {
        if (false !== $blank)
        {
          $newTokens[] = array('T_WHITESPACE', $blank);
          $blank = false;
        }
        $newTokens[] = $token;
        continue;
      }

      list($id, $text) = $token;
      switch ($id)
      {
        case T_WHITESPACE:
          $blank = "\n" !== $blank ? (false !== strpos($text, "\n") ? "\n" : ' ') : "\n";
          break;
        default:
          if (false !== $blank)
          {
            $newTokens[] = array('T_WHITESPACE', $blank);
            $blank = false;
          }
          $newTokens[] = $token;
          break;
      }
    }

    return $newTokens;
  }
}
