<?php

/**
 *
 * nanoserv handlers - HTTP service handler
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
 * HTTP Service handler class
 *
 * @package nanoserv
 * @subpackage Handlers
 */
abstract class NS_HTTP_Service_Handler extends NS_Connection_Handler {

	/**
	 * Server string
	 */
	const SERVER_STRING = "";

	/**
	 * Max request length
	 */
	const MAX_REQUEST_LENGTH = 1048576;
	
	/**
	 * Default content type
	 */
	const DEFAULT_CONTENT_TYPE = "text/html";
	
	/**
	 * Response status codes and strings
	 */
	private $STATUS_CODES = array(100 => "100 Continue",
			200 => "OK",
			201 => "Created",
			204 => "No Content",
			206 => "Partial Content",
			300 => "Multiple Choices",
			301 => "Moved Permanently",
			302 => "Found",
			303 => "See Other",
			304 => "Not Modified",
			307 => "Temporary Redirect",
			400 => "Bad Request",
			401 => "Unauthorized",
			403 => "Forbidden",
			404 => "Not Found",
			405 => "Method Not Allowed",
			406 => "Not Acceptable",
			408 => "Request Timeout",
			410 => "Gone",
			413 => "Request Entity Too Large",
			414 => "Request URI Too Long",
			415 => "Unsupported Media Type",
			416 => "Requested Range Not Satisfiable",
			417 => "Expectation Failed",
			500 => "Internal Server Error",
			501 => "Method Not Implemented",
			503 => "Service Unavailable",
			506 => "Variant Also Negotiates");
	
	/**
	 * Request headers
	 * @var array
	 */
	protected $request_headers = array();
	
	/**
	 * Request method
	 * @var string
	 */
	protected $request_method = "";
	
	/**
	 * Request protocol
	 * @var string
	 */
	protected $request_protocol = "";
	
	/**
	 * Request content (raw POST data)
	 * @var string
	 */
	protected $request_content = "";
	
	/**
	 * Request buffer
	 * @var string
	 */
	private $request_buffer = "";
	
	/**
	 * Response headers
	 * @var array
	 */
	private $response_headers = array();
	
	/**
	 * Response content type
	 * @var string
	 */
	private $response_content_type = false;
	
	/**
	 * Response status code
	 * @var int
	 */
	private $response_status = 200;
	
	final public function on_Read($data) {

		$this->request_buffer .= $data;

		while ($this->request_buffer) {
		
			if (($p = strpos($this->request_buffer, "\n\n")) !== false) {

				$hdrs = substr($this->request_buffer, 0, $p);
				$cnt = substr($this->request_buffer, $p + 2);
			
			} else if (($p = strpos($this->request_buffer, "\r\n\r\n")) !== false) {

				$hdrs = substr($this->request_buffer, 0, $p);
				$cnt = substr($this->request_buffer, $p + 4);
			
			} else {

				if (strlen($this->request_buffer) > self::MAX_REQUEST_LENGTH) {

					$this->Set_Response_Status(414);
					$this->Send_Response("Request too large");
				
				} else {
				
					return;

				}

			}

			$hdrs = explode("\n", $hdrs);

			$tmp = array();

			$sl = true;
			
			foreach ($hdrs as $hdr) {
				
				if (isset($sl)) {

					$this->request_method = strtoupper(trim(strtok(trim($hdr), " ")));
					$url = trim(strtok(" "));
					$this->request_protocol = trim(strtok(""));
					
					unset($sl);

				}
				
				$k = strtoupper(trim(strtok(trim($hdr), ":")));
				$v = trim(strtok(""));

				$tmp[$k] = $v;
			
			}

			$this->request_headers = $tmp;
		
			if ((isset($this->request_headers["CONTENT-LENGTH"])) && ($cl = $this->request_headers["CONTENT-LENGTH"])) {

				if ($c1 > self::MAX_REQUEST_LENGTH) {

					$this->Set_Response_Status(413);
					$this->Send_Response("Request too large");
				
				} else if (strlen($cnt) < $cl) {
				
					return;

				}

				$this->request_content = substr($cnt, 0, $cl);
				$this->request_buffer = ltrim(substr($cnt, $cl));

			} else {
			
				$this->request_content = "";
				$this->request_buffer = $cnt;

			}

			if (substr($this->request_protocol, 0, 4) !== "HTTP") {

				$this->Set_Response_Status(400);
				$this->Send_Response("Bad Request");
			
			} else {

				$this->Send_Response($this->on_Request($url));

			}

		}
	
	}

	/**
	 * Event called on HTTP request
	 *
	 * the string returned by on_Request() will be sent back as the HTTP response
	 *
	 * @param string $url
	 * @return string
	 */
	public function on_Request($url) {

	}

	/**
	 * Add HTTP header to the response
	 *
	 * @param string $header
	 */
	public function Add_Header($header) {

		$this->response_headers[] = $header;
	
	}
	
	/**
	 * Set response content type
	 *
	 * @param string $content_type
	 */
	public function Set_Content_Type($content_type) {

		$this->response_content_type = $content_type;

	}
	
	/**
	 * Set HTTP response status code
	 *
	 * 200 = OK, 403 = Forbidden, 404 = Not found, ...
	 *
	 * @param int $code
	 */
	public function Set_Response_Status($code=200) {

		$this->response_status = $code;
	
	}
	
	/**
	 * Send HTTP response back to client
	 *
	 * This method is only invoked by the on_Read() handler
	 *
	 * @param string $data
	 */
	private function Send_Response($data) {

		$keep = (strtoupper(trim($this->request_headers["CONNECTION"])) == "KEEP-ALIVE");

		$resp  = "HTTP/1.0 ".(int)$this->response_status." ".$this->STATUS_CODES[$this->response_status]."\r\n";
		$resp .= "Date: ".gmdate("D, d M Y H:i:s T")."\r\n";
		$resp .= "Server: ".(self::SERVER_STRING ? self::SERVER_STRING : "nanoserv/".NS_VERSION)."\r\n";
		$resp .= "Content-Type: ".(($this->response_content_type !== false) ? $this->response_content_type : self::DEFAULT_CONTENT_TYPE)."\r\n";
		$resp .= "Content-Length: ".strlen($data)."\r\n";
		if ($this->response_headers) $resp .= implode("\r\n", $this->response_headers)."\r\n";
		$resp .= "Connection: ".($keep ? "Keep-Alive" : "Close")."\r\n";
		$resp .= "\r\n";
		$resp .= $data;
		
		$this->response_content_type = self::DEFAULT_CONTENT_TYPE;
		$this->response_headers = array();
		$this->response_status = 200;

		$this->Write($resp, ($keep ? false : array($this, "Disconnect")));

	}

}

?>
