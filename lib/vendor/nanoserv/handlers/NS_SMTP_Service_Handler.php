<?php

/**
 *
 * nanoserv handlers - SMTP service handler
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA * 
 *
 * @package nanoserv
 * @subpackage Handlers
 */

/**
 * Require the line input connection handler
 */
require "NS_Line_Input_Connection_Handler.php";

/**
 * SMTP Service handler class
 *
 * @package nanoserv
 * @subpackage Handlers
 */
abstract class NS_SMTP_Service_Handler extends NS_Line_Input_Connection_Handler {

	/**
	 * Server string
	 */
	const SERVER_STRING = "";
	
	/**
	 * Hostname
	 * @var string
	 */
	public $hostname;
	
	/**
	 * HELO message
	 * @var string
	 */
	protected $helo_message = "";
	
	/**
	 * Enveloppe sender
	 * @var string
	 */
	protected $env_from = "";

	/**
	 * Enveloppe recipents
	 * @var array
	 */
	protected $env_rcpt = array();

	/**
	 * Mail data buffer
	 * @var string
	 */
	protected $data_buffer = "";
	
	/**
	 * State indicator
	 * @var bool
	 */
	private $indata = false;
	
	/**
	 * SMTP Service Handler constructor
	 */
	public function __construct() {

		$this->hostname = php_uname("n");
	
	}
	
	public function on_Accept() {

		$this->Write("200 ".$this->hostname." SMTP ".(self::SERVER_STRING ? self::SERVER_STRING : "nanoserv/".NS_VERSION)."\n");
	
	}
	
	final public function on_Read_Line($data) {

		if (!$this->indata) {

			$updata = strtoupper($data);

			if (strpos($updata, "HELO") === 0) {

				strtok($data, " ");
				$this->helo_message = trim(strtok(""));

				if (!$this->on_SMTP_HELO($this->helo_message)) {
					
					$this->Disconnect();
					break;
				
				}
				
				$this->Write("250 ".$this->hostname." Hello\n");

			} else if (strpos($updata, "MAIL FROM") === 0) {

				strtok($data, ":");
				$this->env_from = trim(strtok(""));
			
				if (!$this->on_SMTP_MAIL_FROM($this->env_from)) break;

				$this->Write("250 ".$this->env_from."... Sender ok\n");
			
			} else if (strpos($updata, "RCPT TO") === 0) {

				strtok($data, ":");
				$this->env_rcpt[] = $rcpt = trim(strtok(""));

				if (!$this->on_SMTP_RCPT_TO($rcpt)) break;

				$this->Write("250 ".$rcpt."... Recipient ok\n");
			
			} else if (strpos($updata, "DATA") === 0) {

				$this->Write("354 Enter mail, end with '.' on a line by itself\n");
				$this->indata = true;
			
			} else if (strpos($updata, "QUIT") === 0) {

				$this->Write("251 ".$this->hostname." closing connection\n", array($this, "Disconnect"));

			} else {

				if (!$this->on_SMTP_Unhandled(trim($data))) break;

			}
		
		} else {

			if (rtrim($data) !== ".") {
			
				$this->data_buffer .= $data;

			} else {

				if ($this->on_Mail($this->env_from, $this->env_rcpt, $this->data_buffer)) {

					$this->Write("250 Message accepted\n");
				
				} else {

					$this->Write("554 Message rejected\n");

				}
			
				$this->indata = false;
				$this->env_from = "";
				$this->env_rcpt = array();
			
			}
		
		}
	
	}

	/**
	 * Event called on SMTP HELO reception
	 *
	 * Extend this method to return the boolean status of the session (false = disconnect client)
	 *
	 * @param string $data remote HELO message
	 * @return bool
	 */
	public function on_SMTP_HELO($data) {

		return true;
	
	}

	/**
	 * Event called on SMTP MAIL FROM reception
	 *
	 * Extend this method to return the boolean status of the session (false = disconnect client)
	 *
	 * @param string $data remote MAIL FROM message
	 * @return bool
	 */
	public function on_SMTP_MAIL_FROM($data) {

		return true;
	
	}

	/**
	 * Event called on SMTP RCPT TO reception
	 *
	 * Extend this method to return the boolean status of the session (false = disconnect client)
	 *
	 * @param string $data remote RCPT TO message
	 * @return bool
	 */
	public function on_SMTP_RCPT_TO($data) {

		return true;
	
	}
	
	/**
	 * Event called on unknown SMTP command reception
	 *
	 * Extend this method to return the boolean status of the session (false = disconnect client)
	 *
	 * @param string $data entire command line
	 * @return bool
	 */
	public function on_SMTP_Unhandled($data) {

		$this->Write("500 Unknown command : '$data'\n");
		
		return true;
	
	}
	
	/**
	 * Event called on mail reception
	 *
	 * if true is returned, a "message accepted" reply will be sent, and "message rejected" for false
	 *
	 * @param string $env_from
	 * @param array $env_to
	 * @param string $data this includes mail headers and content
	 * @return bool
	 */
	public function on_Mail($env_from, $env_to, $data) {

	}

}

?>