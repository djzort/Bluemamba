<?
/*********************************************************************

	BlueMamba is a software package created by X6 Industries, Inc.
	Copyright © 2006-2008 X6 Industries, Inc., All Rights Reserved

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    Author:   Travis Schanafelt  >>  travis@bluemamba.org

    Modified: 09/18/2008

    Document: include/idba.php
              
    Function: Database access functions for abstraction.

*********************************************************************/

include_once("../conf/db_conf.php");

class idba_obj
{
	var $conn;
	function connect()
	{
		global $DB_HOST, $DB_USER, $DB_PASSWORD, $DB_PERSISTENT, $DB_NAME;
		
		if($this->conn>0) return true;
		
		$this->conn = false;
		$mysql_conn = false;
		
		if($DB_PERSISTENT) $mysql_conn = mysql_pconnect($DB_HOST, $DB_USER, $DB_PASSWORD);	
		if(!$mysql_conn)   $mysql_conn = mysql_connect($DB_HOST, $DB_USER, $DB_PASSWORD);
		
		if($mysql_conn)
		{
			if(mysql_select_db($DB_NAME))
			{
				$this->conn = $mysql_conn;
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}


	function query($sql)
	{
		if($this->conn)
		{
			$result = @mysql_query($sql, $this->conn);
			return $result;
		}
		else
		{
			return false;
		}
	}


	function num_rows($result)
	{
		return mysql_num_rows($result);
	}


	function fetch_row($result)
	{
		return mysql_fetch_assoc($result);
	}


	function result($result, $row, $field)
	{
		return mysql_result($result, $row, $field);
	}


	function list_fields($table)
	{
		global $DB_NAME;
		$result = false;
		
		if($this->conn)
		{
			$fields  = mysql_list_fields($DB_NAME, $table, $this->conn);
			$columns = mysql_num_fields($fields);

			for($i = 0; $i < $columns; $i++)
			{
    			$result[$i] = mysql_field_name($fields, $i);
			}	 	
		}
		return $result;
	}


	function insert_id()
	{
		return mysql_insert_id($this->conn);
	}


	function error()
	{
		return mysql_error();
	}
}
?>