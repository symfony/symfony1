<?php
/*
 *  $Id: PgSQLDatabaseInfo.php,v 1.9 2005/03/30 11:45:26 hlellelid Exp $
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

require_once 'creole/metadata/DatabaseInfo.php';

/**
 * MySQL implementation of DatabaseInfo.
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 * @version   $Revision: 1.9 $
 * @package   creole.drivers.pgsql.metadata
 */
class PgSQLDatabaseInfo extends DatabaseInfo {

    /**
     * @throws SQLException
     * @return void
     */
    protected function initTables()
    {
        include_once 'creole/drivers/pgsql/metadata/PgSQLTableInfo.php';
        
        // Get Database Version
        $result = pg_exec ($this->dblink, "SELECT version() as ver");
        
        if (!$result)
        {
        	throw new SQLException ("Failed to select database version");
        } // if (!$result)
        $row = pg_fetch_assoc ($result, 0);
        $arrVersion = sscanf ($row['ver'], '%*s %d.%d');
        $version = sprintf ("%d.%d", $arrVersion[0], $arrVersion[1]);
        // Clean up
        $arrVersion = null;
        $row = null;
        pg_free_result ($result);
        $result = null;

        $result = pg_exec($this->dblink, "SELECT oid, relname FROM pg_class
										WHERE relkind = 'r' AND relnamespace = (SELECT oid
										FROM pg_namespace
										WHERE
										     nspname NOT IN ('information_schema','pg_catalog')
										     AND nspname NOT LIKE 'pg_temp%'
										     AND nspname NOT LIKE 'pg_toast%'
										LIMIT 1)
										ORDER BY relname");

        if (!$result) {
            throw new SQLException("Could not list tables", pg_last_error($this->dblink));
        }

        while ($row = pg_fetch_assoc($result)) {
            $this->tables[strtoupper($row['relname'])] = new PgSQLTableInfo($this, $row['relname'], $version, $row['oid']);
        }
    }

    /**
     * PgSQL sequences.
     *
     * @return void
     * @throws SQLException
     */
    protected function initSequences()
    {
        throw new SQLException("Sequences are currently unsupported.");
    }

}

