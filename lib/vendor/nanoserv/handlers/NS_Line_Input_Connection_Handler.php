<?php

/**
 *
 * nanoserv handlers - Line input connection handler
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
 * Line input connection handler class
 *
 * @package nanoserv
 * @subpackage Handlers
 */
abstract class NS_Line_Input_Connection_Handler extends NS_Connection_Handler {

	/**
	 * Maximum line length
	 */
	const MAX_LENGTH = 16384;
	
	/**
	 * Line separator
	 */
	const EOL_SEPARATOR = "\n";
	
	/**
	 * Line buffer
	 * @var string
	 */
	private $line_buffer = "";

	final public function on_Read($data) {

		$this->line_buffer .= $data;

		while (($p = strrpos($this->line_buffer, self::EOL_SEPARATOR)) !== false) {

			$lines = explode(self::EOL_SEPARATOR, substr($this->line_buffer, 0, $p));
			$this->line_buffer = substr($this->line_buffer, $p + strlen(self::EOL_SEPARATOR));

			foreach ($lines as $line) $this->on_Read_Line(rtrim($line, "\r\n").self::EOL_SEPARATOR);
		
		}
	
		if (strlen($this->line_buffer) > self::MAX_LENGTH) {

			$this->on_Read_Line($this->line_buffer);
			$this->line_buffer = "";
		
		}
	
	}

	/**
	 * Event called on new line of data
	 *
	 * @param string $data
	 */
	public function on_Read_Line($data) {

	}

}

?>