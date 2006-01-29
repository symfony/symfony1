<?php

/**
 *
 * nanoserv - a sockets daemon toolkit for PHP 5.1+
 * 
 * Copyright (C) 2004-2005 Vincent Negrier aka. sIX <six@aegis-corp.org>
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA 
 *
 * @package nanoserv
 * @subpackage Core
 */

/**
 * nanoserv current version number
 */

define("NS_VERSION", "1.0.1");

/**
 * Base socket class
 *
 * @package nanoserv
 * @subpackage Core
 * @since 0.9
 */
class NS_Socket {

	/**
	 * Internal Socket unique ID
	 * @var int
	 */
	public $id;
	
	/**
	 * Socket stream descriptor
	 * @var resource
	 */
	public $fd;

	/**
	 * Is the socket connected ?
	 * @var bool
	 */
	public $connected = false;
	
	/**
	 * Is the socket blocked ?
	 * @var bool
	 */
	public $blocked = false;
	
	/**
	 * Stream context
	 * @var resource
	 */
	protected $context;
	
	/**
	 * Crypto type
	 * @var int
	 */
	public $crypto_type;
	
	/**
	 * Attached handler
	 * @var NS_Connection_Handler
	 */
	public $handler;
	
	/**
	 * Static instance counter
	 * @var int
	 */
	private static $sck_cnt;
	
	/**
	 * NS_Socket contructor
	 *
	 * @param resource $fd
	 */
	public function __construct($fd = false, $crypto_type=false) {

		if ($fd === false) {
		
			$this->context = stream_context_create();

		} else {

			$this->fd = $fd;
			$this->connected = true;
			$this->Set_Blocking(false);
			$this->Set_Timeout(0);

			if ($crypto_type !== false) $this->crypto_type = $crypto_type;
		
		}
	
		$this->id = ++NS_Socket::$sck_cnt;
	
	}
	
	/**
	 * Get stream options
	 *
	 * @return array
	 * @since 0.9
	 */
	public function Get_Options() {

		if ($this->fd) {

			return stream_context_get_options($this->fd);

		} else {

			return stream_context_get_options($this->context);

		}

	}
	
	/**
	 * Set a stream context option
	 *
	 * @param string $wrapper
	 * @param string $opt
	 * @param mixed $val
	 * @return bool
	 * @since 0.9
	 */
	public function Set_Option($wrapper, $opt, $val) {

		if ($this->fd) {

			return stream_context_set_option($this->fd, $wrapper, $opt, $val);

		} else {

			return stream_context_set_option($this->context, $wrapper, $opt, $val);

		}
	
	}
	
	/**
	 * Set timeout
	 * 
	 * @param int $timeout
	 * @since 0.9
	 */
	protected function Set_Timeout($timeout) {

		stream_set_timeout($this->fd, $timeout);
	
	}
	
	/**
	 * Sets wether the socket is blocking or not
	 *
	 * @param bool $block
	 * @since 0.9
	 */
	protected function Set_Blocking($block) {

		stream_set_blocking($this->fd, $block);

	}

	/**
	 *  Enables or disables ssl/tls crypto on the socket
	 *
	 * @param bool $enable
	 * @param int $type 
	 * @return bool
	 * @since 0.9
	 */
	public function Enable_Crypto($enable, $type=STREAM_CRYPTO_METHOD_SSLv23_SERVER) {

		return @stream_socket_enable_crypto($this->fd, $enable, $type);

	}
	
	/**
	 * Setup crypto if needed
	 *
	 * @since 0.9
	 */
	public function Setup() {

		if (isset($this->crypto_type)) $this->Enable_Crypto(true, $this->crypto_type);
		
	}
	
	/**
	 * Get local socket name
	 *
	 * @return string
	 * @since 0.9
	 */
	public function Get_Name() {

		return stream_socket_get_name($this->fd, false);

	}
	
	/**
	 * Get remote socket name
	 *
	 * @return string
	 * @since 0.9
	 */
	public function Get_Peer_Name() {

		return stream_socket_get_name($this->fd, true);

	}
	
	/**
	 * Read data from the socket and return it
	 *
	 * @param int $length maximum read length
	 * @return string
	 * @since 0.9
	 */
	public function Read($length=16384) {

		return fread($this->fd, $length);

	}

	/**
	 * Read data from a non connected socket and return it
	 *
	 * @param string &$addr contains the message sender address upon return
	 * @param int $length maximum read length
	 * @return string
	 * @since 0.9.61
	 */
	public function Read_From(&$addr, $length=16384) {

		return stream_socket_recvfrom($this->fd, $length, NULL, $addr);

	}
	
	/**
	 * Write data to the socket
	 *
	 * write returns the number of bytes written to the socket
	 *
	 * @param string $data
	 * @return int
	 * @since 0.9
	 */
	public function Write($data) {

		$nb = fwrite($this->fd, $data);

		if ($nb != strlen($data)) $this->blocked = true;

		return $nb;
	
	}
	
	/**
	 * Write data to a non connected socket
	 *
	 * @param string $to in the form of "<ip_address>:<port>"
	 * @param string $data
	 * @return int
	 * @since 0.9.61
	 */
	public function Write_To($to, $data) {

		return stream_socket_sendto($this->fd, $data, NULL, $to);
	
	}
	
	/**
	 * Query end of stream status
	 *
	 * @return bool
	 * @since 0.9
	 */
	public function Eof() {

		// somehow this recvfrom trick is needed for feof to work
		stream_socket_recvfrom($this->fd, 1, STREAM_PEEK);

		return (feof($this->fd));

	}
	
	/**
	 * Close the socket
	 * @since 0.9
	 */
	public function Close() {

		@fclose($this->fd);

	}

	/**
	 * NS_Socket destructor
	 */
	public function __destruct() {

		Nanoserv::Free_Write_Buffers($this->id);
		$this->Close();

	}

}

/**
 * Server socket class
 *
 * @package nanoserv
 * @subpackage Core
 * @since 0.9
 */
class NS_Server_Socket extends NS_Socket {

	/**
	 * Listen address (format is 'proto://addr:port')
	 * @var string
	 */
	public $address;

	/**
	 * Real listen address (format is 'proto://addr:port')
	 * @var string
	 */
	private $real_address;

	/**
	 * NS_Socket_Server constructor
	 */
	public function __construct($addr) {

		parent::__construct();
		
		$this->address = $addr;

		$proto = strtolower(strtok($addr, ":"));
		$s = strtok("");

		if ($proto == "udp") {

			$this->real_address = $addr;
		
		} else {
		
			$this->real_address = "tcp:" . $s;

			if ($proto != "tcp") switch ($proto) {

				case "ssl":		$this->crypto_type = STREAM_CRYPTO_METHOD_SSLv23_SERVER;	break;
				case "tls":		$this->crypto_type = STREAM_CRYPTO_METHOD_TLS_SERVER;		break;
				case "sslv2":	$this->crypto_type = STREAM_CRYPTO_METHOD_SSLv2_SERVER;		break;
				case "sslv3":	$this->crypto_type = STREAM_CRYPTO_METHOD_SSLv3_SERVER;		break;

				default:		if (defined($cname = "STREAM_CRYPTO_METHOD_".strtoupper($proto)."_SERVER")) $this->crypto_type = constant($cname);
			
			}

		}
	
	}

	/**
	 * Start listening and accepting connetions
	 *
	 * @return bool
	 * @since 0.9
	 */
	public function Listen($bind_only=false) {

		$errno = $errstr = false;
		
		$this->fd = stream_socket_server($this->real_address, $errno, $errstr, STREAM_SERVER_BIND | ($bind_only ? 0 : STREAM_SERVER_LISTEN), $this->context);

		if ($this->fd === false) return false;

		$this->Set_Blocking(false);
		$this->Set_Timeout(0);
		
		return true;

	}

	/**
	 * Accept connection
	 *
	 * @return resource
	 * @since 0.9
	 */
	public function Accept() {

		return @stream_socket_accept($this->fd, 0);

	}

}


/**
 * Client socket class
 *
 * @package nanoserv
 * @subpackage Core
 * @since 0.9
 */
class NS_Client_Socket extends NS_Socket {

	/**
	 * Connect timeout (seconds)
	 * @var int
	 */
	const CONNECT_TIMEOUT = 10;
	
	/**
	 * Peer address (format is 'proto://addr:port')
	 * @var string
	 */
	public $address;

	/**
	 * Connect timeout (timestamp)
	 * @var int
	 */
	public $connect_timeout;
	
	/**
	 * NS_Socket_Client constructor
	 */
	public function __construct($addr) {

		parent::__construct();
		
		$this->address = $addr;

	}

	/**
	 * Connect to the peer address
	 *
	 * @param int $timeout connection timeout in seconds
	 * @return bool
	 * @since 0.9
	 */
	public function Connect($timeout=false) {

		$errno = $errstr = false;

		$this->fd = stream_socket_client($this->address, $errno, $errstr, 0, STREAM_CLIENT_ASYNC_CONNECT | STREAM_CLIENT_CONNECT, $this->context);

		if ($this->fd === false) return false;

		if ($timeout === false) $timeout = self::CONNECT_TIMEOUT;
		
		$this->connect_timeout = time() + $timeout;
		$this->connected = false;
		$this->Set_Blocking(false);
		$this->Set_Timeout(0);
		
		return true;

	}

}


/**
 * IPC Socket class
 *
 * @package nanoserv
 * @subpackage Core
 * @since 0.9
 */
class NS_IPC_Socket extends NS_Socket {

	/**
	 * Maximum size of inter process communication packets
	 * @var int
	 */
	const IPC_MAX_PKTSIZE = 1048576;

	/**
	 * pid number of the remote forked process
	 * @var int
	 */
	public $pid;
	
	/**
	 * IPC Socket constructor
	 *
	 * @param resource $fd
	 * @param int $pid
	 */
	public function __construct($fd, $pid=false) {

		parent::__construct($fd);

		$this->pid = $pid;

	}

	/**
	 * Read data from IPC socket
	 *
	 * @return string
	 * @since 0.9
	 */
	public function Read() {

		return parent::Read(self::IPC_MAX_PKTSIZE);
	
	}

	/**
	 * Creates a pair of connected, indistinguishable pipes
	 *
	 * Returns an array of two NS_Socket objects
	 *
	 * @param int $domain
	 * @param int $type
	 * @param int $proto
	 * @return array
	 * @since 0.9
	 */
	static public function Pair($domain=STREAM_PF_UNIX, $type=STREAM_SOCK_DGRAM, $proto=0) {

		list($s1, $s2) = stream_socket_pair($domain, $type, $proto);

		return array(new NS_IPC_Socket($s1), new NS_IPC_Socket($s2));
	
	}
	
	/**
	 * Ask the master process for object data
	 *
	 * @param array $request
	 * @param bool $need_response
	 * @return mixed
	 * @since 0.9
	 */
	public function Ask_Master($request, $need_response=true) {

		$this->Write(serialize($request));

		if (!$need_response) return;
		
		$rfd = array($this->fd);
		$dfd = array();
		
		if (stream_select($rfd, $dfd, $dfd, 600)) return unserialize($this->Read());

	}

}

/**
 * Timer class
 *
 * Do not instanciate NS_Timer but use the Nanoserv::New_Timer() method instead
 *
 * @package nanoserv
 * @subpackage Core
 * @since 0.9
 */
class NS_Timer {

	/**
	 * System time
	 * @var int
	 */
	public $time;

	/**
	 * Timer callback
	 * @var mixed
	 */
	public $callback;

	/**
	 * Timer status
	 * @var bool
	 */
	public $active = true;
	
	/**
	 * NS_Timer constructor
	 *
	 * @param int $time
	 * @param mixed $callback
	 * @since 0.9
	 * @see Nanoserv::New_Timer()
	 */
	public function __construct($time, $callback) {

		$this->time = $time;
		$this->callback = $callback;
	
	}

	/**
	 * Activate timer
	 *
	 * Timers are activated by default, and Activate should only be used after a call do Deactivate()
	 *
	 * @see NS_Timer::Deactivate()
	 */
	public function Activate() {

		$this->active = true;

	}

	/**
	 * Deactivate timer
	 */
	public function Deactivate() {

		$this->active = false;

	}

}


/**
 * Write buffer class
 *
 * @package nanoserv
 * @subpackage Core
 * @since 0.9
 */
class NS_Write_Buffer {

	/**
	 * Attached socket
	 * @var NS_Socket
	 */
	public $socket;
	
	/**
	 * Buffered data
	 * @var string
	 */
	private $data;

	/**
	 * End-of-write Callback
	 * @var mixed
	 */
	private $callback = false;

	/**
	 * NS_Write_Buffer constructor
	 *
	 * @param NS_Socket $socket
	 * @param string $data
	 * @param mixed $callback
	 */
	public function __construct(NS_Socket $socket, $data, $callback=false) {

		$this->socket = $socket;
		$this->data = $data;
		$this->callback = $callback;
	
	}
	
	/**
	 * Fetch data from the internal buffer
	 *
	 * @param int $length
	 * @return string
	 * @since 0.9
	 */
	public function Fetch_Data($length=16384) {

		$s = substr($this->data, 0, $length);
		return $s;
	
	}

	/**
	 * Clear the <var>$length</var> first bytes of the buffer
	 *
	 * @param int $length
	 * @since 0.9
	 */
	public function Clear_Buffer($length) {

		$this->data = substr($this->data, $length);
	
	}

	/**
	 * Get availability of data
	 *
	 * @return bool
	 * @since 0.9
	 */
	public function Waiting_Data() {

		return (strlen($this->data) != 0);
	
	}

	/**
	 * NS_Write_Buffer destructor
	 */
	public function __destruct() {

		if ($this->callback !== false) call_user_func($this->callback, $this->Waiting_Data());
	
	}

}


/**
 * Base handler class
 *
 * @package nanoserv
 * @subpackage Core
 * @since 0.9
 */
abstract class NS_Handler {

	/**
	 * Attached socket
	 * @var NS_Socket
	 */
	public $socket;

	/**
	 * Set a stream context option
	 *
	 * @param string $wrapper
	 * @param string $opt
	 * @param mixed $val
	 * @return bool
	 * @since 0.9
	 */
	public function Set_Option($wrapper, $opt, $val) {

		return $this->socket->Set_Option($wrapper, $opt, $val);
	
	}

}


/**
 * Datagram listener / handler class
 *
 * @package nanoserv
 * @subpackage Core
 * @since 0.9.61
 */
abstract class NS_Datagram_Handler extends NS_Handler {

	/**
	 * Is the listener active ?
	 * @var bool
	 */
	public $active = false;

	/**
	 * NS_Datagram_Handler constructor
	 *
	 * @param string $addr
	 * @param string $handler_classname
	 * @param mixed $handler_options
	 */
	public function __construct($addr) {

		$this->socket = new NS_Server_Socket($addr);
	
	}
	
	/**
	 * Activate the listener
	 *
	 * @return bool
	 * @since 0.9.61
	 */
	public function Activate() {

		if ($ret = $this->socket->Listen(true)) $this->active = true;
		
		return $ret;
	
	}

	/**
	 * Deactivate the listener
	 * @since 0.9.61
	 */
	public function Deactivate() {

		$this->socket->Close();
		$this->active = false;
	
	}

	/**
	 * Send data over the connection
	 *
	 * @param string $to in the form of "<ip_address>:<port>"
	 * @param string $data
	 * @return int
	 * @since 0.9.61
	 */
	public function Write($to, $data) {

		return $this->socket->Write_To($to, $data);
	
	}

	/**
	 * Event called on data reception
	 *
	 * @param string $from
	 * @param string $data
	 * @since 0.9.61
	 */
	public function on_Read($from, $data) {

	}
	
	/**
	 * NS_Datagram_Handler destructor
	 */
	public function __destruct() {

		$this->Deactivate();

	}
	
}


/**
 * Connection handler class
 *
 * @package nanoserv
 * @subpackage Core
 * @since 0.9
 */
abstract class NS_Connection_Handler extends NS_Handler {

	/**#@+
	 * Cause of connection failure
	 * @var int
	 */
	const FAIL_NORESPONSE = 1;
	const FAIL_TIMEOUT = 2;
	/**#@-*/
	
	/**
	 * Send data over the connection
	 *
	 * @param string $data
	 * @param mixed $callback
	 * @return NS_Write_Buffer
	 * @since 0.9
	 */
	public function Write($data, $callback=false) {

		return Nanoserv::New_Write_Buffer($this->socket, $data, $callback);

	}

	/**
	 * Connect
	 *
	 * @param int $timeout timeout in seconds
	 * @since 0.9
	 */
	public function Connect($timeout=false) {

		return $this->socket->Connect($timeout);
	
	}
	
	/**
	 * Disconnect
	 */
	public function Disconnect() {

		$this->socket->Close();

		Nanoserv::Free_Connection($this);
	
	}
	
	/**
	 * Event called on received connection
	 * @since 0.9
	 */
	public function on_Accept() {

	}

	/**
	 * Event called on established connection
	 * @since 0.9
	 */
	public function on_Connect() {
		
	}

	/**
	 * Event called on failed connection
	 *
	 * @param int $failcode see NS_Connection_Handler::FAIL_* constants
	 * @since 0.9
	 */
	public function on_Connect_Fail($failcode) {
		
	}
	
	/**
	 * Event called on disconnection
	 * @since 0.9
	 */
	public function on_Disconnect() {

	}

	/**
	 * Event called on data reception
	 *
	 * @param string $data
	 * @since 0.9
	 */
	public function on_Read($data) {

	}

}


/**
 * Listener class
 *
 * @package nanoserv
 * @subpackage Core
 * @since 0.9
 */
class NS_Listener {

	/**
	 * Attached socket
	 * @var NS_Server_Socket
	 */
	public $socket;

	/**
	 * Name of the handler class
	 * @var string
	 * @see NS_Connetion_Handler
	 */
	public $handler_classname;

	/**
	 * Handler options
	 *
	 * this is passed as the first constructor parameter of each spawned connection handlers
	 *
	 * @var mixed
	 */
	public $handler_options;

	/**
	 * Is the listener active ?
	 * @var bool
	 */
	public $active = false;
	
	/**
	 * If set the listener will fork() a new process for each accepted connection
	 * @var bool
	 */
	public $forking = false;
	
	/**
	 * NS_Listener constructor
	 *
	 * @param string $addr
	 * @param string $handler_classname
	 * @param mixed $handler_options
	 */
	public function __construct($addr, $handler_classname, $handler_options=false, $forking=false) {

		$this->socket = new NS_Server_Socket($addr);
		$this->handler_classname = $handler_classname;
		$this->handler_options = $handler_options;
		$this->forking = ($forking && is_callable("pcntl_fork"));
	
	}

	/**
	 * Set a stream context option
	 *
	 * @param string $wrapper
	 * @param string $opt
	 * @param mixed $val
	 * @return bool
	 * @since 0.9
	 */
	public function Set_Option($wrapper, $opt, $val) {

		return $this->socket->Set_Option($wrapper, $opt, $val);
	
	}
	
	/**
	 * Sets wether the listener should fork() a new process for each accepted connection
	 *
	 * @param bool $forking
	 * @return bool
	 * @since 0.9
	 */
	public function Set_Forking($forking=true) {

		if ($forking && !is_callable("pcntl_fork")) return false;
		
		$this->forking = $forking;

		return true;
	
	}
	
	/**
	 * Activate the listener
	 *
	 * @return bool
	 * @since 0.9
	 */
	public function Activate() {

		if ($ret = $this->socket->Listen()) $this->active = true;
		
		return $ret;
	
	}

	/**
	 * Deactivate the listener
	 * @since 0.9
	 */
	public function Deactivate() {

		$this->socket->Close();
		$this->active = false;
	
	}

	/**
	 * NS_Listener destructor
	 */
	public function __destruct() {

		$this->Deactivate();

	}

}


/**
 * Shared object class for inter-process communications
 *
 * @package nanoserv
 * @subpackage Core
 * @since 0.9
 */
class NS_Shared_Object {

	/**
	 * caller process pid
	 * @var int
	 */
	static public $caller_pid;
	
	/**
	 * shared object unique identifier
	 * @var int
	 */
	public $_oid;
	
	/**
	 * wrapped object
	 * @var object
	 */
	private $wrapped;

	/**
	 * static instance counter
	 * @var int
	 */
	static public $shared_count = 0;
	
	/**
	 * NS_Shared_Object constructor
	 *
	 * If $o is omited, a new StdClass object will be created and wrapped
	 *
	 * @param object $o
	 */
	public function __construct($o=false) {

		if ($o === false) $o = new StdClass();

		$this->_oid = ++self::$shared_count;
		$this->wrapped = $o;
	
	}
	
	public function __get($k) {

		if (Nanoserv::$child_process) {

			return Nanoserv::$master_pipe->Ask_Master(array("oid" => $this->_oid, "action" => "G", "var" => $k));
			
		} else {
		
			return $this->wrapped->$k;

		}

	}

	public function __set($k, $v) {

		if (Nanoserv::$child_process) {

			Nanoserv::$master_pipe->Ask_Master(array("oid" => $this->_oid, "action" => "S", "var" => $k, "val" => $v), false);
		
		} else {
		
			$this->wrapped->$k = $v;

		}
	
	}

	public function __call($m, $a) {

		if (Nanoserv::$child_process) {

			return Nanoserv::$master_pipe->Ask_Master(array("oid" => $this->_oid, "action" => "C", "func" => $m, "args" => $a));

		} else {
		
			return call_user_func_array(array($this->wrapped, $m), $a);

		}
	
	}

}


/**
 * Server / multiplexer class
 *
 * @package nanoserv
 * @subpackage Core
 * @since 0.9
 */
final class Nanoserv {

	/**
	 * Registered listeners
	 * @var array
	 */
	static private $listeners = array();

	/**
	 * Write buffers
	 * @var array
	 */
	static private $write_buffers = array();
	
	/**
	 * Active connections
	 * @var array
	 */
	static private $connections = array();
	
	/**
	 * Active datagram handlers
	 * @var array
	 */
	static private $dgram_handlers = array();
	
	/**
	 * Shared objects
	 * @var array
	 */
	static private $shared_objects = array();

	/**
	 * Forked process pipes
	 * @var array
	 */
	static private $forked_pipes = array();
	
	/**
	 * Timers
	 * @var array
	 */
	static private $timers = array();
	
	/**
	 * Number of active connection handler processes
	 * @var int
	 */
	static public $nb_forked_processes = 0;
	
	/**
	 * Are we master or child process ?
	 * @var bool
	 */
	static public $child_process = false;
	
	/**
	 * Forked server handled connection
	 * @var NS_Connection_Handler
	 */
	static private $forked_connection;
	
	/**
	 * Forked server pipe to the master process
	 * @var NS_Socket
	 */
	static public $master_pipe;
	
	/**
	 * Class Nanoserv should not be instanciated but used statically
	 */
	private function __construct() {

	}
	
	/**
	 * Register a new listener
	 *
	 * For consistency New_Listener() will also wrap Nanoserv::New_Datagram_Handler() if the given addr is of type "udp"
	 *
	 * @param string $addr
	 * @param string $handler_classname
	 * @param mixed $handler_options
	 * @return NS_Listener
	 * @see NS_Listener
	 * @see NS_Datagram_Handler
	 * @since 0.9
	 */
	static public function New_Listener($addr, $handler_classname, $handler_options=false) {

		if (strtolower(strtok($addr, ":")) == "udp") {

			$l = self::New_Datagram_Handler($addr, $handler_classname);
		
		} else {
		
			$l = new NS_Listener($addr, $handler_classname, $handler_options);
			self::$listeners[] = $l;

		}
		
		return $l;

	}

	/**
	 * Deactivate and free a previously registered listener
	 *
	 * For consistency Free_Listener() will also wrap Nanoserv::Free_Datagram_Handler() if the given object is an instance of NS_Datagram_Handler
	 *
	 * @param NS_Listener $l
	 * @return bool
	 * @see NS_Listener
	 * @see NS_Datagram_Handler
	 * @since 0.9
	 */
	static public function Free_Listener($l) {

		if ($l instanceof NS_Listener) {
		
			foreach (self::$listeners as $k => $v) if ($v === $l) {

				unset(self::$listeners[$k]);
				return true;
			
			}

		} else if ($l instanceof NS_Datagram_Handler) {

			return self::Free_Datagram_Handler($l);
		
		}
		
		return false;
	
	}

	/**
	 * Register a new write buffer
	 *
	 * This method is used by NS_Connection_Handler::Write() and should not be 
	 * called unless you really know what you are doing
	 *
	 * @param NS_Socket $socket
	 * @param string $data
	 * @param mixed $callback
	 * @return NS_Write_Buffer
	 * @see NS_Connection_Handler::Write()
	 * @since 0.9
	 */
	static public function New_Write_Buffer(NS_Socket $socket, $data, $callback=false) {

		if (strlen($data) == 0) return true;
		
		$wb = new NS_Write_Buffer($socket, $data, $callback);
		self::$write_buffers[$socket->id][] = $wb;

		return $wb;
	
	}

	/**
	 * Free a registered write buffer
	 *
	 * @param int $sid socket id
	 * @since 0.9
	 */
	static public function Free_Write_Buffers($sid) {

		unset(self::$write_buffers[$sid]);
	
	}
	
	/**
	 * Register a new outgoing connection
	 * 
	 * @param string $addr
	 * @param string $handler_classname
	 * @param mixed $handler_options
	 * @return NS_Connection_Handler
	 * @see NS_Connection_Handler
	 * @since 0.9
	 */
	static public function New_Connection($addr, $handler_classname, $handler_options=false) {

		$sck = new NS_Client_Socket($addr);
		$h = new $handler_classname($handler_options);

		$h->socket = $sck;

		self::$connections[$sck->id] = $h;
		
		return $h;
	
	}
	
	/**
	 * Free an allocated connections
	 *
	 * @param NS_Connection_Handler $h
	 * @return bool
	 * @since 0.9
	 */
	static public function Free_Connection(NS_Connection_Handler $h) {

		unset(self::$connections[$h->socket->id]);
		self::Free_Write_Buffers($h->socket->id);

		if (self::$child_process && (self::$forked_connection === $h)) exit();

		return true;
	
	}

	/**
	 * Register a new datagram (udp) handler
	 *
	 * @param string $addr
	 * @param string $handler_classname
	 * @return NS_Datagram_Handler
	 * @see NS_Datagram_Handler
	 * @since 0.9.61
	 */
	static public function New_Datagram_Handler($addr, $handler_classname) {

		$h = new $handler_classname($addr);
		self::$dgram_handlers[$h->socket->id] = $h;

		return $h;
	
	}
	
	/**
	 * Deactivate and free a datagram handler
	 *
	 * @param NS_Datagram_Handler $h
	 * @return bool
	 * @since 0.9.61
	 */
	static public function Free_Datagram_Handler(NS_Datagram_Handler $h) {

		unset(self::$dgram_handlers[$h->socket->id]);

		return true;

	}
	
	/**
	 * Register a new shared object
	 *
	 * shared objects allow forked processes to use objects stored on the master process
	 * if $o is ommited, a new StdClass empty object is created
	 *
	 * @param object $o
	 * @return NS_Shared_Object
	 * @since 0.9
	 */
	static public function New_Shared_Object($o=false) {

		$shr = new NS_Shared_Object($o);

		self::$shared_objects[$shr->_oid] = $shr;

		return $shr;
	
	}
	
	/**
	 * Free a shared object
	 *
	 * @param NS_Shared_Object $o
	 * @since 0.9
	 */
	static public function Free_Shared_Object(NS_Shared_Object $o) {

		unset(self::$shared_objects[$o->_oid]);
	
	}
	
	/**
	 * Register a new timer callback
	 *
	 * @param int $delay specified in seconds
	 * @param mixed $callback may be "function" or array($obj, "method")
	 * @return NS_Timer
	 * @since 0.9
	 */
	static public function New_Timer($delay, $callback) {

		$time_t = time() + $delay;
		
		$t = new NS_Timer($time_t, $callback);
		
		self::$timers[$time_t][] = $t;

		ksort(self::$timers);
		
		return $t;
	
	}
	
	/**
	 * Get all registered NS_Connection_Handler objects
	 *
	 * Note: connections created by fork()ing listeners can not be retreived this way
	 *
	 * @param bool $include_pending_connect
	 * @return array
	 * @since 0.9
	 */
	static public function Get_Connections($include_pending_connect=false) {

		$ret = array();
		
		foreach (self::$connections as $c) if ($c->socket->connected || $include_pending_connect) $ret[] = $c;

		return $ret;
	
	}
	
	/**
	 * Get all registered NS_Listener objects
	 *
	 * @param bool $include_inactive
	 * @return array
	 * @since 0.9
	 */
	static public function Get_Listeners($include_inactive=false) {

		$ret = array();
		
		foreach (self::$listeners as $l) if ($l->active || $include_inactive) $ret[] = $l;

		return $ret;
	
	}
	
	/**
	 * Fork and setup IPC sockets
	 *
	 * @return int the pid of the created process, 0 if child process
	 * @since 0.9.63
	 */
	static public function Fork() {

		if ($has_shared = (NS_Shared_Object::$shared_count > 0)) {

			list($s1, $s2) = NS_IPC_Socket::Pair();
		
		}
		
		$pid = pcntl_fork();

		if ($pid === 0) {

			self::$child_process = true;

			if ($has_shared) {
			
				self::$master_pipe = $s2;

			}
			
		} else if ($pid > 0) {

			++self::$nb_forked_processes;

			if ($has_shared) { 

				$s1->pid = $pid;
				self::$forked_pipes[$pid] = $s1;
			
			}
		
		}

		return $pid;
	
	}
	
	/**
	 * Enter main loop
	 *
	 * @param int $loops if omited nanoserv will enter an endless loop
	 * @since 0.9
	 */
	static public function Run($loops=false) {

		$tmp = 0;
		
		while ($loops !== 0) {
		
			$t = time();

			// Timers

			foreach (self::$timers as $tmr_t => $tmr_a) {

				if ($tmr_t > $t) break;

				foreach ($tmr_a as $tmr) if ($tmr->active) call_user_func($tmr->callback);

				unset(self::$timers[$tmr_t]);

			}
			
			// Write buffers to non blocked sockets

			foreach (self::$write_buffers as $write_buffers) {

				if (!$write_buffers || $write_buffers[0]->socket->blocked || !$write_buffers[0]->socket->connected) continue;

				foreach ($write_buffers as $wb) {

					while ($wb->Waiting_Data() && !$wb->socket->blocked) {
							
						$wb->Clear_Buffer($wb->socket->Write($wb->Fetch_Data()));

						if (!$wb->Waiting_Data()) {
								
							array_shift(Nanoserv::$write_buffers[$wb->socket->id]);
							if (!self::$write_buffers[$wb->socket->id]) self::Free_Write_Buffers($wb->socket->id);

						}

					}
				
				}

			}
		
			$write_buffers = $l = $c = $wbs = $wb = false;
			
			// Prepare socket arrays

			$fd_lookup_r = $fd_lookup_w = $rfd = $wfd = $efd = array();

			foreach (self::$listeners as $l) if ($l->active) {

				$rfd[] = $l->socket->fd;
				$fd_lookup_r[(int)$l->socket->fd] = $l;
			
			}

			foreach (self::$connections as $c) {

				if ($c->socket->connected) {
				
					$rfd[] = $c->socket->fd;
					$fd_lookup_r[(int)$c->socket->fd] = $c;

				} else {

					if ($c->socket->connect_timeout < $t) {

						// Connection timeout
					
						$c->on_Connect_Fail(NS_Connection_Handler::FAIL_TIMEOUT);
						self::Free_Connection($c);
					
					} else {
					
						$wfd[] = $c->socket->fd;
						$fd_lookup_w[(int)$c->socket->fd] = $c;

					}
				
				}

			}

			foreach (self::$dgram_handlers as $l) if ($l->active) {

				$rfd[] = $l->socket->fd;
				$fd_lookup_r[(int)$l->socket->fd] = $l;
			
			}
			
			foreach (self::$write_buffers as $wbs) if ($wbs[0]->socket->blocked) {

				$wfd[] = $wbs[0]->socket->fd;
				$fd_lookup_w[(int)$wbs[0]->socket->fd] = $wbs[0]->socket;
			
			}

			foreach (self::$forked_pipes as $fp) {

				$rfd[] = $fp->fd;
				$fd_lookup_r[(int)$fp->fd] = $fp;
			
			}
			
			// Main select
			
			$handler = false;
			
			if (stream_select($rfd, $wfd, $efd, 1)) {

				foreach ($rfd as $act_rfd) {

					$handler = $fd_lookup_r[(int)$act_rfd];

					if ($handler instanceof NS_Connection_Handler) {

						$data = $handler->socket->Read();

						if (strlen($data) === 0) {

							// Disconnected socket
							
							$handler->on_Disconnect();
							self::Free_Connection($handler);

						} else {

							// Data available
							
							$handler->on_Read($data);
						
						}
					
					} else if ($handler instanceof NS_Datagram_Handler) {
						
						$from = "";
						$data = $handler->socket->Read_From($from);

						$handler->on_Read($from, $data);
					
					} else if ($handler instanceof NS_Listener) {

						while ($fd = $handler->socket->Accept()) {

							// New connection accepted
							
							$sck = new NS_Socket($fd, $handler->socket->crypto_type);

							$hnd = new $handler->handler_classname($handler->handler_options);
							$hnd->socket = $sck;

							if ($handler->forking) {

								if (self::Fork() === 0) {

									$sck->Setup();
									
									self::$write_buffers = self::$listeners = array();
									self::$connections = array($sck->id => $hnd);
									self::$forked_connection = $hnd;

									$hnd->on_Accept();

									$handler = $hnd = $sck = $l = $c = $wbs = $wb = $fd_lookup_r = $fd_lookup_w = $loops = false;

									break;
									
								} 

							} else {
							
								$sck->Setup();
								
								self::$connections[$sck->id] = $hnd;

								$hnd->on_Accept();

							}
						
						}
						
						$sck = $hnd = false;
					
					} else if ($handler instanceof NS_IPC_Socket) {

						while ($ipcm = $handler->Read()) {
						
							if ((!$ipcq = unserialize($ipcm)) || (!is_object($o = self::$shared_objects[$ipcq["oid"]]))) continue;

							NS_Shared_Object::$caller_pid = $handler->pid;
							
							switch ($ipcq["action"]) {

								case "G":
								$handler->Write(serialize($o->$ipcq["var"]));
								break;

								case "S":
								$o->$ipcq["var"] = $ipcq["val"];
								break;

								case "C":
								$handler->Write(serialize(call_user_func_array(array($o, $ipcq["func"]), $ipcq["args"])));
								break;
							
							}

						}
					
						$o = $ipcq = $ipcm = false;
					
					}

				}

				foreach ($wfd as $act_wfd) {
					
					$handler = $fd_lookup_w[$act_wfd];

					if ($handler->socket->connected) {

						// Unblock buffered write
						
						if ($handler->socket->Eof()) {

							$handler->on_Disconnect();
							self::Free_Connection($handler);
						
						} else {
						
							$fd_lookup_w[$act_wfd]->blocked = false;

						}

					} else {
					
						// Pending connect

						if ($handler->socket->Eof()) {

							$handler->on_Connect_Fail(NS_Connection_Handler::FAIL_NORESPONSE);
							self::Free_Connection($handler);
						
						} else {

							$handler->socket->connected = true;
							$handler->on_Connect();

						}

					}
				
				}
				
			}

			if (self::$nb_forked_processes && !self::$child_process) while ((($pid = pcntl_wait($tmp, WNOHANG)) > 0) && self::$nb_forked_processes--) unset(self::$forked_pipes[$pid]);
			
			if ($loops !== false) --$loops;
		
		}
	
	}

}

?>
