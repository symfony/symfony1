<?php

/*
*  $Id: DBMSSQLSRV.php 800 2007-11-09 22:45:59Z justin $
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
* <http://propel.phpdb.org>.
*/

/**
 * This is used to connect to a MSSQL database.  For now, this class
 * simply extends the adaptor for Sybase.
 *
 * @author     Hans Lellelid <hans@xmpl.org> (Propel)
 * @version    $Revision: 800 $
 * @package    propel.adapter
 */
class DBMSSQLSRV extends DBSybase {

    /**
    * Simulated Limit/Offset
    * This rewrites the $sql query to apply the offset and limit.
    * @see        DBAdapter::applyLimit()
    * @author     Justin Carlson <justin.carlson@gmail.com>
    */
    public function applyLimit(&$sql, $offset, $limit)
    {
        // make sure offset and limit are numeric
        if(!is_numeric($offset) || !is_numeric($limit)){
            throw new Exception("DBMSSQLSRV ::applyLimit() expects a number for argument 2 and 3");
        }

        // obtain the original select statement
        preg_match('/\A(.*)select(.*)from/si',$sql,$select_segment);
        if(count($select_segment)>0)
        {
            $original_select = $select_segment[0];
        } else {
            throw new Exception("DBMSSQLSRV ::applyLimit() could not locate the select statement at the start of the query. ");
        }
        $modified_select = substr_replace($original_select, null, stristr($original_select,'select') , 6 );

        // obtain the original order by clause, or create one if there isn't one
        preg_match('/order by(.*)\Z/si',$sql,$order_segment);
        if(count($order_segment)>0)
        {
            $order_by = $order_segment[0];
        } else {

            // no order by clause, if there are columns we can attempt to sort by the columns in the select statement
            $select_items = split(',',$modified_select);
            if(count($select_items)>0)
            {
                $item_number = 0;
                $order_by = null;
                while($order_by === null && $item_number<count($select_items))
                {
                    if($select_items[$item_number]!='*' && !strstr($select_items[$item_number],'('))
                    {
                        $order_by = 'order by ' . $select_items[0] . ' asc';
                    }
                    $item_number++;
                }
            }
            if($order_by === null)
            {
                throw new Exception("DBMSSQLSRV ::applyLimit() could not locate the order by statement at the end of your query or any columns at the start of your query. ");
            } else {
                $sql.= ' ' . $order_by;
            }

        }

        // remove the original select statement
        $sql = str_replace($original_select , null, $sql);

        /* modify the sort order by for paging */
        $inverted_order = '';
        $order_columns = split(',',str_ireplace('order by ','',$order_by));
        $original_order_by = $order_by;
        $order_by = '';
        foreach($order_columns as $column)
        {
            // strip "table." from order by columns
            $column = array_reverse(split("\.",$column));
            $column = $column[0];

            // commas if we have multiple sort columns
            if(strlen($inverted_order)>0){
                $order_by.= ', ';
                $inverted_order.=', ';
            }

            // put together order for paging wrapper
            if(stristr($column,' desc'))
            {
                $order_by .= $column;
                $inverted_order .= str_ireplace(' desc',' asc',$column);
            } elseif(stristr($column,' asc')) {
                $order_by .= $column;
                $inverted_order .= str_ireplace(' asc',' desc',$column);
            } else {
                $order_by .= $column;
                $inverted_order .= $column .' desc';
            }
        }
        $order_by = 'order by ' . $order_by;
        $inverted_order = 'order by ' . $inverted_order;

        // build the query
        $offset = ($limit+$offset);
        $modified_sql = 'select * from (';
        $modified_sql.= 'select top '.$limit.' * from (';
        $modified_sql.= 'select top '.$offset.' '.$modified_select.$sql;
        $modified_sql.= ') deriveda '.$inverted_order.') derivedb '.$order_by;
        $sql = $modified_sql;

    }

    public function random($seed=NULL)
    {
        return 'NEWID()';
    }

}
