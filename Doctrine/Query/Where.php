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
 * Doctrine_Query_Where
 *
 * @package     Doctrine
 * @subpackage  Query
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 */
class Doctrine_Query_Where extends Doctrine_Query_Condition
{
    public function load($where)
    {
        // Handle operator ("AND" | "OR"), reducing overhead of this method processment
        $possibleOp = strtolower($where);

        if ($possibleOp == 'and' || $possibleOp == 'or')
        {
            return $where;
        }

        $where = $this->_tokenizer->bracketTrim(trim($where));
        $conn  = $this->query->getConnection();
        $terms = $this->_tokenizer->sqlExplode($where);  

        if (count($terms) > 1) {
            if (substr($where, 0, 6) == 'EXISTS') {
                return $this->parseExists($where, true);
            } elseif (substr($where, 0, 10) == 'NOT EXISTS') {
                return $this->parseExists($where, false);
            }
        }

        if (count($terms) < 3) {
            $terms = $this->_tokenizer->sqlExplode($where, array('=', '<', '<>', '>', '!='));
        }

        if (count($terms) > 1) {
            $leftExpr = array_shift($terms);
            $rightExpr = array_pop($terms);
            $operator = trim(substr($where, strlen($leftExpr), -strlen($rightExpr)));

            if (strpos($leftExpr, "'") === false && strpos($leftExpr, '(') === false) {
                // normal field reference found
                $a = explode('.', $leftExpr);
                array_pop($a); // Discard the field name (not needed!)
                $reference = implode('.', $a);

                if (empty($reference)) {
                    $map = $this->query->getRootDeclaration();
                    $alias = $this->query->getSqlTableAlias($this->query->getRootAlias());
                } else {
                    $map = $this->query->load($reference, false);
                    $alias = $this->query->getSqlTableAlias($reference);
                }
            }

            $sql = $this->_buildSql($leftExpr, $operator, $rightExpr);

            return $sql;
        } else {
            return $where;
        }
    }
    

    protected function _buildSql($leftExpr, $operator, $rightExpr)
    {
        $leftExprOriginal = $leftExpr;        
        $leftExpr = $this->query->parseClause($leftExpr);
        
        // BETWEEN operation
        if ('BETWEEN' == strtoupper(substr($operator, 0, 7))) {
            $midExpr = trim(substr($operator, 7, -3));
            $operator = 'BETWEEN ' . $this->query->parseClause($midExpr) . ' AND';
        }
     
        $op = strtolower($operator);
        $isInX = ($op == 'in' || $op == 'not in');

        // Check if we are not dealing with "obj.field IN :named"
        if (substr($rightExpr, 0 , 1) == ':' && $isInX) {
            throw new Doctrine_Query_Exception(
                'Cannot use ' . $operator . ' with a named parameter in "' . 
                $leftExprOriginal . ' ' . $operator . ' ' . $rightExpr . '"'
            );
        }

        // Right Expression
        $rightExpr = ($rightExpr == '?' && $isInX)
            ? $this->_buildWhereInArraySqlPart($rightExpr)
            : $this->parseValue($rightExpr);

        return $leftExpr . ' ' . $operator . ' ' . $rightExpr;
    }
    

    protected function _buildWhereInArraySqlPart($rightExpr)
    {
        $params = $this->query->getInternalParams();
        $value = array();

        for ($i = 0, $l = count($params); $i < $l; $i++) {
            if (is_array($params[$i])) {
                $value = array_fill(0, count($params[$i]), $rightExpr);
                $this->query->adjustProcessedParam($i);

                break;
            }
        }

        return '(' . (count($value) > 0 ? implode(', ', $value) : $rightExpr) . ')';
    }


    public function parseValue($rightExpr)
    {
        $conn = $this->query->getConnection();

        // If value is contained in paranthesis
        if (substr($rightExpr, 0, 1) == '(') {
            // trim brackets
            $trimmed = $this->_tokenizer->bracketTrim($rightExpr);

            // If subquery found which begins with FROM and SELECT
            // FROM User u WHERE u.id IN(SELECT u.id FROM User u WHERE u.id = 1)
            if (substr($trimmed, 0, 4) == 'FROM' ||
                substr($trimmed, 0, 6) == 'SELECT') {

                // subquery found
                $q = $this->query->createSubquery()
                    ->parseDqlQuery($trimmed, false);
                $sql   = $q->getSqlQuery();
                $q->free();
                $rightExpr = '(' . $sql . ')';

            // If custom sql for custom subquery
            // You can specify SQL: followed by any valid sql expression
            // FROM User u WHERE u.id = SQL:(select id from user where id = 1)
            } elseif (substr($trimmed, 0, 4) == 'SQL:') {
                $rightExpr = '(' . substr($trimmed, 4) . ')';
            // simple in expression found
            } else {
                $e = $this->_tokenizer->sqlExplode($trimmed, ',');

                $value = array();
                $index = false;

                foreach ($e as $part) {
                    $value[] = $this->parseLiteralValue($part);
                }

                $rightExpr = '(' . implode(', ', $value) . ')';
            }
        } else {
            $rightExpr = $this->parseLiteralValue($rightExpr);
        }

        return $rightExpr;
    }

    /**
     * parses an EXISTS expression
     *
     * @param string $where         query where part to be parsed
     * @param boolean $negation     whether or not to use the NOT keyword
     * @return string
     */
    public function parseExists($where, $negation)
    {
        $operator = ($negation) ? 'EXISTS' : 'NOT EXISTS';

        $pos = strpos($where, '(');

        if ($pos == false) {
            throw new Doctrine_Query_Exception('Unknown expression, expected a subquery with () -marks');
        }

        $sub = $this->_tokenizer->bracketTrim(substr($where, $pos));

        $q = $this->query->createSubquery()
            ->parseDqlQuery($sub, false);
        $sql = $q->getSqlQuery();
        $q->free();
        return $operator . ' (' . $sql . ')';
    }
}
