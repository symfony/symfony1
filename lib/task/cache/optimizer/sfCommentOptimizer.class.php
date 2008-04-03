<?php

class sfCommentOptimizer
{
  public static function optimize($tokens)
  {
    $newTokens = array();

    foreach ($tokens as $token)
    {
      if (is_string($token))
      {
        $newTokens[] = $token;
        continue;
      }

      list($id, $text) = $token;
      switch ($id)
      {
        case T_COMMENT:
        case T_DOC_COMMENT:
          break;
        default:
          $newTokens[] = $token;
          break;
      }
    }

    return $newTokens;
  }
}
