<?php

class sfConditionOptimizer
{
  public function optimize($tokens)
  {
    $newTokens = array();
    $i = -1;
    while (++$i < count($tokens))
    {
      if (is_array($tokens[$i]))
      {
        list($id, $text) = $tokens[$i];
        if (T_IF == $id)
        {
          list($processed, $processedTokens) = $this->processCondition(array_slice($tokens, $i));
          $i += $processed;
          $newTokens = array_merge($newTokens, $processedTokens);
        }
      }
      $newTokens[] = $tokens[$i];
    }

    return $newTokens;
  }

  protected function processCondition($tokens)
  {
    // remove first T_IF token
    array_shift($tokens);

    $processedTokens = array();
    $conditions = array();
    $condition = array(
      'evaluable' => true,
      'condition' => array(),
      'code' => array(),
    );
    $openBraces = 0;
    $openParens = 0;
    $inCondition = false;
    $conditionOk = false;
    $i = -1;
    while (++$i < count($tokens))
    {
      $token = $tokens[$i];
      $processedTokens[] = $token;
      if (is_string($token))
      {
        switch ($token)
        {
          case '{':
            if ($inCondition) { $condition['condition'][] = $token; } else { if (0 != $openBraces) { $condition['code'][] = $token; } }
            ++$openBraces;
            break;
          case ';':
          case '}':
            if ($inCondition) { $condition['condition'][] = $token; } else { if (';' == $token || 1 != $openBraces) { $condition['code'][] = $token; } }

            if (';' && $token && $conditionOk && 0 == $openBraces && !$inCondition)
            {
              // this is a one line if, so simulate the end of braces }
              $token = '}';
              $openBraces = 1;
            }

            if ('}' == $token && 0 == --$openBraces && !$inCondition)
            {
              $conditions[] = $condition;
              $condition = array(
                'evaluable' => true,
                'condition' => array(),
                'code' => array(),
              );
              $conditionOk = false;

              // end of the if statement
              // but is the next token a else or a elseif?
              while (++$i < count($tokens))
              {
                if (is_string($tokens[$i]))
                {
                  break 3;
                }

                list($id, $text) = $tokens[$i];
                switch ($id)
                {
                  case T_WHITESPACE:
                    $processedTokens[] = $tokens[$i];
                    break;
                  case T_ELSE:
                    // is this a else if?
                    $if = false;
                    while (++$i < count($tokens))
                    {
                      if (is_string($tokens[$i]))
                      {
                        --$i;
                        break;
                      }

                      list($id, $text) = $tokens[$i];
                      switch ($id)
                      {
                        case T_WHITESPACE:
                          $processedTokens[] = $tokens[$i];
                          break;
                        case T_IF:
                          // convert else if to elseif
                          $processedTokens[] = array(T_ELSEIF, 'elseif');
                          $processedTokens[] = array(T_WHITESPACE, ' ');
                          $if = true;
                          break;
                        default:
                          --$i;
                          break 2;
                      }
                    }
                    if (!$if)
                    {
                      $processedTokens[] = array(T_ELSE, 'else');
                    }
                    break 3;
                  case T_ELSEIF:
                    $processedTokens[] = $tokens[$i];
                    break 3;
                  default:
                    break 4;
                }
              }
            }
            break;
          case '(':
            if (!$conditionOk && false === $inCondition && !$openBraces)
            {
              $inCondition = true;
            }
            else
            {
              if ($inCondition) { $condition['condition'][] = $token; } else { $condition['code'][] = $token; }
            }
            ++$openParens;
            break;
          case ')':
            --$openParens;
            if ($inCondition) { if (0 != $openParens) { $condition['condition'][] = $token; } } else { $condition['code'][] = $token; }
            if ($inCondition && 0 == $openParens)
            {
              $inCondition = false;
              $conditionOk = true;
            }
            break;
          default:
            if ($inCondition) { $condition['condition'][] = $token; } else { $condition['code'][] = $token; }
        }

        continue;
      }

      list($id, $text) = $token;
      switch ($id)
      {
        case T_CURLY_OPEN:
        case T_DOLLAR_OPEN_CURLY_BRACES:
          ++$openBraces;
          break;
      }

      if ($inCondition) { $condition['condition'][] = $token; } else { $condition['code'][] = $token; }
    }

    return array(count($processedTokens), $this->optimizeCondition($conditions));
  }

  protected function optimizeCondition($conditions)
  {
    $tokens = array();
    foreach ($conditions as $condition)
    {
      $code = $this->optimize($condition['code']);
      if (array() == $condition['condition'])
      {
        // else
        $tokens = array() == $tokens ? $code : array_merge($tokens, array(T_ELSE, 'else'), array('{'), $code, array('}'));
        break;
      }
      elseif ($this->isConditionEvaluable($condition['condition']))
      {
        // if or elseif
        eval(sprintf("\$result = %s;", sfOptimizer::tokensToPhp($condition['condition'])));
        if ($result)
        {
          $tokens = $code;
          break;
        }
      }
      else
      {
        $condToken = array() == $tokens ? array(T_IF, 'if') : array(T_ELSEIF, 'elseif');
        $tokens = array_merge($tokens, $condToken, array('('), $condition['condition'], array(')'), array('{'), $code, array('}'));
      }
    }

    return $tokens;
  }

  protected function isConditionEvaluable($tokens)
  {
    $i = -1;
    while (++$i < count($tokens))
    {
      $token = $tokens[$i];
      if (is_string($token))
      {
        continue;
      }

      list($id, $text) = $token;
      switch ($id)
      {
        case T_BOOLEAN_AND:
        case T_BOOLEAN_OR:
        case T_BOOL_CAST:
        case T_CONSTANT_ENCAPSED_STRING:
        case T_DNUMBER:
        case T_DOUBLE_CAST:
        case T_INT_CAST:
        case T_IS_EQUAL:
        case T_IS_GREATER_OR_EQUAL:
        case T_IS_IDENTICAL:
        case T_IS_NOT_EQUAL:
        case T_IS_NOT_IDENTICAL:
        case T_IS_SMALLER_OR_EQUAL:
        case T_LNUMBER:
        case T_LOGICAL_AND:
        case T_LOGICAL_OR:
        case T_LOGICAL_XOR:
        case T_STRING_CAST:
        case T_WHITESPACE:
          break;
        default:
          return false;
      }
    }

    return true;
  }
}
