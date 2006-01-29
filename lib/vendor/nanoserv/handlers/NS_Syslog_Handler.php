<?php

/**
 *
 * nanoserv handlers - Syslog protocol handler
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
 * Syslog protocol handler handler class
 *
 * @package nanoserv
 * @subpackage Handlers
 */
abstract class NS_Syslog_Handler extends NS_Datagram_Handler {

	/**
	 * Get facility name from numerical code
	 *
	 * @param int $code
	 * @return string
	 */
	static public function Code_To_Facility($code) {

		switch ($code) {

			case 0:		return "kern";
			case 1:		return "user";
			case 2:		return "mail";
			case 3:		return "daemon";
			case 4:		return "auth";
			case 5:		return "syslog";
			case 6:		return "lpr";
			case 7:		return "news";
			case 8:		return "uucp";
			case 9:		return "cron";
			case 10:	return "auth";
			case 11:	return "ftp";
			case 12:	return "ntp";
			case 15:	return "cron";
			case 16:	return "local0";
			case 17:	return "local1";
			case 18:	return "local2";
			case 19:	return "local3";
			case 20:	return "local4";
			case 21:	return "local5";
			case 22:	return "local6";
			case 23:	return "local7";
		
		}
	
	}
	
	/**
	 * Get facility numerical code from name
	 *
	 * @param string $name
	 * @return int
	 */
	static public function Facility_To_Code($name) {

		switch (strtolower($name)) {

			case "kern":	return 0;
			case "user":	return 1;
			case "mail":	return 2;
			case "daemon":	return 3;
			case "auth":	return 4;
			case "syslog":	return 5;
			case "lpr":		return 6;
			case "news":	return 7;
			case "uucp":	return 8;
			case "cron":	return 9;
			case "ftp":		return 11;
			case "ntp":		return 12;
			case "local0":	return 16;
			case "local1":	return 17;
			case "local2":	return 18;
			case "local3":	return 19;
			case "local4":	return 20;
			case "local5":	return 21;
			case "local6":	return 22;
			case "local7":	return 23;
		
		}
	
	}
	
	final public function on_Read($from, $data) {

		$host = strtok($from, ":");

		if (($data{0} !== "<") || (($p = strpos($data, ">")) === false)) return;

		$pri = (int)substr($data, 1, $p - 1);
		$msg = substr($data, $p + 1);
		
		$this->on_Event($host, $pri >> 3, $pri & 7, $msg);

	}

	/**
	 * Event called on new syslog event
	 *
	 * @param string $host
	 * @param int $facility
	 * @param int $severity
	 * @param string $message
	 */
	public function on_Event($host, $facility, $severity, $message) {

	}

}

?>
