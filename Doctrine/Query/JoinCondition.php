<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.phpdoctrine.org>.
 */

/**
 * Doctrine_Query_JoinCondition
 *
 * @package     Doctrine
 * @subpackage  Query
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 */
class Doctrine_Query_JoinCondition extends Doctrine_Query_Condition 
{
    public function load($condition) 
    {
        $condition = trim($condition);

        $e = $this->_tokenizer->sqlExplode($condition);

        if (($l = count($e)) > 2) {
            $expr = new Doctrine_Expression($e[0], $this->query->getConnection());
            $e[0] = $expr->getSql();

            $operator  = $e[1];

            // FIX: "field NOT IN (XXX)" issue
            // Related to ticket #1329
            if ($l > 3) {
                $operator .= ' ' . $e[2]; // Glue "NOT" and "IN"
                $e[2] = $e[3]; // Move "(XXX)" to previous index

                unset($e[3]); // Remove unused index
            }

            if (substr(trim($e[2]), 0, 1) != '(') {
                $expr = new Doctrine_Expression($e[2], $this->query->getConnection());
                $e[2] = $expr->getSql();
            }

            // We need to check for agg functions here
            $hasLeftAggExpression = preg_match('/(.*)\(([^\)]*)\)([\)]*)/', $e[0], $leftMatches);

            if ($hasLeftAggExpression) {
                $e[0] = $leftMatches[2];
            }

            $hasRightAggExpression = preg_match('/(.*)\(([^\)]*)\)([\)]*)/', $e[2], $rightMatches);

            if ($hasRightAggExpression) {
                $e[2] = $rightMatches[2];
            }

            $a         = explode('.', $e[0]);
            $field     = array_pop($a);
            $reference = implode('.', $a);
            $value     = $e[2];

            $conn      = $this->query->getConnection();
            $alias     = $this->query->getTableAlias($reference);
            $map       = $this->query->getAliasDeclaration($reference);
            $table     = $map['table'];

            // FIX: Issues with "(" XXX ")"
            if ($hasRightAggExpression) {
                $value = '(' . $value . ')';
            }

            if (substr($value, 0, 1) == '(') {
                // trim brackets
                $trimmed   = $this->_tokenizer->bracketTrim($value);

                if (substr($trimmed, 0, 4) == 'FROM' || substr($trimmed, 0, 6) == 'SELECT') {
                    // subquery found
                    $q     = $this->query->createSubquery()->parseQuery($trimmed, false);
                    $value   = $q->getSql();
                } elseif (substr($trimmed, 0, 4) == 'SQL:') {
                    // Change due to bug "(" XXX ")"
                    //$value = '(' . substr($trimmed, 4) . ')';
                    $value = substr($trimmed, 4);
                } else {
                    // simple in expression found
                    $e     = $this->_tokenizer->sqlExplode($trimmed, ',');

                    $value = array();
                    foreach ($e as $part) {
                        $value[] = $this->parseLiteralValue($part);
                    }

                    // Change due to bug "(" XXX ")"
                    //$value = '(' . implode(', ', $value) . ')';
                    $value = implode(', ', $value);
                }
            } else {
                $value = $this->parseLiteralValue($value);
            }

            switch ($operator) {
                case '<':
                case '>':
                case '=':
                case '!=':
                default:
                    $leftExpr = (($hasLeftAggExpression) ? $leftMatches[1] . '(' : '') 
                              . $conn->quoteIdentifier($alias . '.' . $field)
                              . (($hasLeftAggExpression) ? $leftMatches[3] . ')' : '') ;

                    $rightExpr = (($hasRightAggExpression) ? $rightMatches[1] . '(' : '') 
                              . $value
                              . (($hasRightAggExpression) ? $rightMatches[3] . ')' : '') ;

                    $condition  = $leftExpr . ' ' . $operator . ' ' . $rightExpr;
            }

            return $condition;
        }
        
        $parser = new Doctrine_Query_Where($this->query, $this->_tokenizer);

        return $parser->parse($condition);
    }
}
