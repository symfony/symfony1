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
 * Custom Doctrine connection adapter for oracle
 *
 * @package     Doctrine
 * @subpackage  Adapter
 * @author      Konsta Vesterinen <kvesteri@cc.hut.fi>
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.phpdoctrine.org
 * @since       1.0
 * @version     $Revision$
 */

class Doctrine_Adapter_Oracle implements Doctrine_Adapter_Interface{
	/**
	 *	execution mode 
	 */
	protected $_executeMode = OCI_COMMIT_ON_SUCCESS;
	
	/**
	 * Resource representing connection to database
	 */
	protected $_connection = false;

	
	protected $_attributes = array(	Doctrine::ATTR_DRIVER_NAME	=> "oci8",
									Doctrine::ATTR_ERRMODE		=> Doctrine::ERRMODE_SILENT, 
									);
	
	/**
	 * User-provided configuration.
	 *
	 * Basic keys are:
	 *
	 * username => (string) Connect to the database as this username.
	 * password => (string) Password associated with the username.
	 * dbname   => Either the name of the local Oracle instance, or the
	 *             name of the entry in tnsnames.ora to which you want to connect.
	 *
	 * @var array
	 */
	protected $_config = array(
 	        'dbname'       => null,
 	        'username'     => null,
 	        'password'     => null,
            'charset'      => null,
	);

    /**
     * Doctrine Oracle adapter constructor
     *
     * <code>
     * $conn = new Doctrine_Adapter_Oracle(array('dbname'=>'db','username'=>'usr','password'=>'pass'));
     * </code>
     *
     * @param string $name 
     * @return void
     */
    public function __construct($config = array()){
    	if ( ! isset($config['password']) || ! isset($config['username'])) {
    		throw new Doctrine_Adapter_Exception('config array must have at least a username and a password');
    	}
    	 
    	$this->_config['username'] = $config['username'];
    	$this->_config['password'] = $config['password'];
    	$this->_config['dbname'] = $config['dbname'];
        $this->_config['charset'] = $config['charset'];
    }

    private function connect(){
    	
    	$this->_connection = @oci_connect($this->_config['username'], $this->_config['password'], $this->_config['dbname'], $this->_config['charset'] );
    	
    	if( $this->_connection === false){
    		throw new Exception(sprintf("Unable to Connect to :'%s' as '%s'", $this->_config['dbname'], $this->_config['username']));
    	}
    }
    /**
     * Prepare a query statement
     *
     * @param string $query Query to prepare
     * @return Doctrine_Adapter_Statement_Oracle $stmt prepared statement
     */
    public function prepare($query){
    	if($this->_connection ===false){
    		$this->connect();
    	}
    	$oci_stmt = $this->parseQuery($query);
    	$stmt = new Doctrine_Adapter_Statement_Oracle($this, $oci_stmt, $this->_executeMode);
        //$stmt->queryString = $query;

        return $stmt;
    }

    /**
     * Execute query and return results as statement object
     *
     * @param string $query 
     * @return Doctrine_Adapter_Statement_Oracle $stmt
     */
    public function query($query){
   		if($this->_connection ===false){
    		$this->connect();
    	}

		$resource = $this->parseQuery($query);
        
        $stmt = new Doctrine_Adapter_Statement_Oracle($this, $resource,$this->_executeMode);
        $stmt->execute();
        
        return $stmt;
    }
	private function parseQuery($query){

		$bind_index = 0;

		/*
		 * Replace ? bind-placeholders with :bind_var_ variables
		 */

		$query = preg_replace("/(\?)/e", '":oci_b_var_". $bind_index++' , $query);
		//print $query.PHP_EOL;
		$resource =  @oci_parse  ( $this->_connection  , $query  );
		
		if( $resource === false){
			//TODO handle error
			print "error in parseQuery";
		}
		
		return $resource;
	}

    /**
     * Quote a value for the dbms
     *
     * @param string $input 
     * @return string $quoted
     */
    public function quote($input)
    {
        return "'" . str_replace("'","''",$input) . "'";
    }

    /**
     * Execute a raw sql statement
     *
     * @param string $statement 
     * @return void
     */
    public function exec($statement)
    {
    	if($this->_connection ===false){
    		$this->connect();
    	}
		$resource = $this->parseQuery($statement);
        
        $stmt = new Doctrine_Adapter_Statement_Oracle($this, $resource, $this->_executeMode);
        $stmt->execute();
        $count = $stmt->rowCount();
        
        return $count;
    }

    /**
     * Get the id of the last inserted record
     *
     * @return integer $id
     */
    public function lastInsertId(){
    	throw new Exception("unsupported");
    }

    /**
     * Begin a transaction
     *
     * @return boolean
     */
    public function beginTransaction()
    {
       $this->_executeMode = OCI_DEFAULT;
       return true;
    }

    /**
     * Commit a transaction
     *
     * @return void
     */
    public function commit(){
    	if($this->_connection ===false){
    		$this->connect();
    	}
        return @oci_commit($this->_connection);
    }

    /**
     * Rollback a transaction
     *
     * @return boolean
     */
    public function rollBack(){
    	if($this->_connection ===false){
    		$this->connect();
    	}
       return @oci_rollback($this->_connection);
    }

	/**
     * Set connection attribute
     *
     * @param integer $attribute
     * @param mixed $value                  the value of given attribute
     * @return boolean                      Returns TRUE on success or FALSE on failure.
     */
    public function setAttribute($attribute, $value){
    	switch($attribute){
    		case Doctrine::ATTR_DRIVER_NAME:
    			//TODO throw an error since driver name can not be changed
    		case Doctrine::ATTR_ERRMODE:
    			break;
    		case Doctrine::ATTR_CASE:
    			if($value == Doctrine::CASE_NATURAL){
    				break;
    			}else{
    				throw new Doctrine_Adapter_Exception("Unsupported Option for ATTR_CASE: $value");
    			}
    		default:
    			throw new Doctrine_Adapter_Exception("Unsupported Attribute: $attribute");
    			return false;
    	}
    	$this->_attributes[$attribute] = $value;
    	return true;
    }
	
	/**
     * Retrieve a statement attribute 
     *
     * @param integer $attribute
     * @see Doctrine::ATTR_* constants
     * @return mixed                        the attribute value
     */
    public function getAttribute($attribute){
    	return $this->_attributes[$attribute];
    }
    
    public function errorCode(){
    	if( is_resource($this->_connection)){
			$error = @oci_error($this->_connection);    		
    	}else{
    		$error = @oci_error();
    	}
    	return $error['code'];
    }

    public function errorInfo(){
    	if( is_resource($this->_connection)){
			$error = @oci_error($this->_connection);    		
    	}else{
    		$error = @oci_error();
    	}
    	return $error['message'];
    }
    
}
