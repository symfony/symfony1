<?php
/*
 *  $Id: CreoleTypes.php,v 1.16 2004/03/20 04:16:49 hlellelid Exp $
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
 * and is licensed under the LGPL. For more information please see
 * <http://creole.phpdb.org>.
 */

/**
 * Generic Creole types modeled on JDBC types.
 * 
 * @author    David Giffin <david@giffin.org>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.16 $
 * @package   creole
 */
abstract class CreoleTypes {

        const BOOLEAN = 1;
        const BIGINT = 2;
        const SMALLINT = 3;
        const TINYINT = 4;
        const INTEGER = 5;
        const CHAR = 6;
        const VARCHAR = 7;
        const TEXT = 17;
        const FLOAT = 8;
        const DOUBLE = 9;
        const DATE = 10;
        const TIME = 11;
        const TIMESTAMP = 12;
        const VARBINARY = 13;
        const NUMERIC = 14;
        const BLOB = 15;
        const CLOB = 16;
        const LONGVARCHAR = 17;
        const DECIMAL = 18;
        const REAL = 19;
        const BINARY = 20;
        const LONGVARBINARY = 21;
        const YEAR = 22;
        
        /** this is "ARRAY" from JDBC types */
        const ARR = 23;
        
        const OTHER = -1;
        
        /** Map of Creole type integers to the setter/getter affix. */
        protected static $affixMap = array(
                self::BOOLEAN => 'Boolean',
                self::BIGINT => 'Int',
                self::CHAR => 'String',
                self::DATE => 'Date',
                self::DOUBLE => 'Float',
                self::FLOAT => 'Float',
                self::INTEGER => 'Int',
                self::SMALLINT => 'Int',
                self::TINYINT => 'Int',
                self::TIME => 'Time',
                self::TIMESTAMP => 'Timestamp',
                self::VARCHAR => 'String',                
                self::VARBINARY => 'Blob',
                self::NUMERIC => 'Float',
                self::BLOB => 'Blob',
                self::CLOB => 'Clob',
                self::LONGVARCHAR => 'String',
                self::DECIMAL => 'Float',
                self::REAL => 'Float',
                self::BINARY => 'Blob',
                self::LONGVARBINARY => 'Blob',
                self::YEAR => 'Int',
                self::ARR => 'Array',
                self::OTHER => '', // get() and set() for unknown
                );
        
        /** Map of Creole type integers to their textual name. */
        protected static $creoleTypeMap = array(
                self::BOOLEAN => 'BOOLEAN',
                self::BIGINT => 'BIGINT',
                self::SMALLINT => 'SMALLINT',
                self::TINYINT => 'TINYINT',
                self::INTEGER => 'INTEGER',
                self::NUMERIC => 'NUMERIC',
                self::DECIMAL => 'DECIMAL',
                self::REAL => 'REAL',
                self::FLOAT => 'FLOAT',
                self::DOUBLE => 'DOUBLE',
                self::CHAR => 'CHAR',
                self::VARCHAR => 'VARCHAR',
                self::TEXT => 'TEXT',
                self::TIME => 'TIME',
                self::TIMESTAMP => 'TIMESTAMP',
                self::DATE => 'DATE',
                self::YEAR => 'YEAR',
                self::VARBINARY => 'VARBINARY',                
                self::BLOB => 'BLOB',
                self::CLOB => 'CLOB',
                self::LONGVARCHAR => 'LONGVARCHAR',
                self::BINARY => 'BINARY',
                self::LONGVARBINARY => 'LONGVARBINARY',                
                self::ARR => 'ARR',
                self::OTHER => 'OTHER', // string is "raw" return
                );
        
        /**
         * This method returns the generic Creole (JDBC-like) type
         * when given the native db type.
         * @param string $nativeType DB native type (e.g. 'TEXT', 'byetea', etc.).
         * @return int Creole native type (e.g. Types::LONGVARCHAR, Types::BINARY, etc.).
         */
        abstract static function getType($nativeType);
        
        /**
         * This method will return a native type that corresponds to the specified
         * Creole (JDBC-like) type.
         * If there is more than one matching native type, then the LAST defined 
         * native type will be returned.
         * @return string Native type string.
         */
        abstract static function getNativeType($creoleType);
        
        /**
         * Gets the "affix" to use for ResultSet::get*() and PreparedStatement::set*() methods.
         * <code>
         * $setter = 'set' . CreoleTypes::getAffix(CreoleTypes::INTEGER);
         * $stmt->$setter(1, $intval);
         * // or
         * $getter = 'get' . CreoleTypes::getAffix(CreoleTypes::TIMESTAMP);
         * $timestamp = $rs->$getter();
         * </code>
         * @param int $creoleType The Creole types.
         * @return string The default affix for getting/setting cols of this type.
         * @throws SQLException if $creoleType does not correspond to an affix
         */
        public static function getAffix($creoleType)
        {
            if (!isset(self::$affixMap[$creoleType])) {
                $e = new SQLException("Unable to return 'affix' for unknown CreoleType: " . $creoleType);
                print $e;
                throw $e;
            }
            return self::$affixMap[$creoleType];
        }
        
        /**
         * Given the integer type, this method will return the corresponding type name.
         * @param int $creoleType the integer Creole type.
         * @return string The name of the Creole type (e.g. 'VARCHAR').
         */
        public static function getCreoleName($creoleType)
        {
            if (!isset(self::$creoleTypeMap[$creoleType])) {
                return null;
            }
            return self::$creoleTypeMap[$creoleType];
        }
        
        /**
         * Given the name of a type (e.g. 'VARCHAR') this method will return the corresponding integer.
         * @param string $creoleTypeName The case-sensisive (must be uppercase) name of the Creole type (e.g. 'VARCHAR').
         * @return int the Creole type.
         */
        public static function getCreoleCode($creoleTypeName)
        {
            $type = array_search($creoleTypeName, self::$creoleTypeMap);
            if ($type === false) {
               return null;
            }
            return $type;
        }
}
