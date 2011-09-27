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
              
    Document: include/data_manager.php
              
    Function: DataManager_obj

*********************************************************************/

include("../conf/conf.php");

include_once("../conf/db_conf.php");
include_once("../include/idba.php");
include_once("../include/array2sql.php");

class DataManager_obj
{
	var $user;
	var $host;
	var $table;		// Directory for FS backend, table name for DB
	var $dataID;	// File name for FS backend, user's ID for DB backend
	var $data;		// Actually contains
	var $error;
	var $db;
	
	function initialize($user, $host, $table, $backend)
	{
		global $DB_USERS_TABLE;
		
		if(empty($table))
		{
			$this->error = "DB table name or ID is empty\n";
			return false;
		}
		
		$this->db = new idba_obj;
		if(!$this->db->connect()) return false;
		
		$sql    = "SELECT * FROM $DB_USERS_TABLE WHERE login='$user' and host='$host'";
		$result = $this->db->query($sql);

		if( ($result) && ($this->db->num_rows($result) > 0) )
		{
			$dataID = $this->db->result($result, 0, "id");
		}
		else
		{
			$this->error = $error;
		}
		
		if(!$dataID)
		{
			$this->error .= "User not found in database\n";
			return false;
		}
		
		$this->backend = $backend;
		$this->table   = $table;
		$this->dataID  = $dataID;
		$this->data    = array();
		
		return true;
	}
	
	
	function read()
	{
		$data   = array();
		$sql    = "SELECT * FROM " . $this->table . " WHERE owner='" . $this->dataID . "'";
		$result = $this->db->query($sql);
		if(($result) && ($this->db->num_rows($result) > 0))
		{
			while($a = $this->db->fetch_row($result))
			{
				$id        = $a["id"];
				$data[$id] = $a;
			}
		}
		else
		{
			$this->error .= $error;
			return false;
		}
		
		return $data;
	}
	
	
	function save()
	{
		// Everything's done in real time anyway
		return true;
	}
	
	
	function delete($id)
	{
		$sql  = "DELETE FROM ".$this->table;
		$sql .= " WHERE id='".$id."' and owner='".$this->dataID."'";
		return $this->db->query($sql);
	}
	
	
	function insert($array)
	{
		// Get list of fields in table
		$backend_fields = $this->db->list_fields($this->table);
		if(!is_array($backend_fields))
		{
			$this->error .= "Failed to fetch fields\n";
			$this->error .= $error;
			return false;
		}
		
		// Pick out relevant fields
		$insert_data = array();
		while( list($k,$field) = each($backend_fields) )
		{
			if(!empty($array[$field]))
			{
				$insert_data[$field] = $array[$field];
			}
		}
		if(empty($insert_data["owner"])) $insert_data["owner"] = $this->dataID;
		
		// Insert
		$sql = Array2SQL($this->table, $insert_data, "INSERT");
		$backend_result = $this->db->query($sql);
				
		$this->error = $error;
		
		return $backend_result;
	}
	
	function update($id, $array)
	{
		// Get list of fields in table
		$backend_fields = $this->db->list_fields($this->table);
		if(!is_array($backend_fields))
		{
			$this->error .= "Failed to fetch fields\n";
			$this->error .= $error;
			return false;
		}
		
		// Pick out relevant fields
		$insert_data = array();
		while( list($k,$field) = each($backend_fields) )
		{
			if(isset($array[$field]))
				$insert_data[$field] = $array[$field];
		}
		
		// Insert
		$sql  = Array2SQL($this->table, $insert_data, "UPDATE");
		$sql .= " WHERE id='$id' and owner='".$this->dataID."'";
		$this->db->query($sql);
		
		$backend_result = $this->db->query($sql);
		$this->error   .= $this->db->error();
		
		return $backend_result;
	}


	function sort($field, $order)
	{
		$data = array();

		$backend_query = "SELECT * FROM ".$this->table;
		$backend_query.=" WHERE owner='".$this->dataID."'";
		$backend_query.=" ORDER BY $field $order";		
		
		$backend_result = $this->db->query($backend_query);
		
		if(($backend_result) && ($this->db->num_rows($backend_result)>0))
		{
			while($a = $this->db->fetch_row($backend_result))
			{
				$data[] = $a;
			}
		}
		else
		{
			$this->error .= $this->db->error();
			return false;
		}
		
		return $data;
	}


	function getDistinct($field, $order)
	{
		$data = array();

		$backend_query  = "SELECT distinct $field FROM ".$this->table;
		$backend_query .= " WHERE owner='".$this->dataID."'";
		$backend_query .= " ORDER BY $field $order";		
		
		$backend_result = $this->db->query($backend_query);
		
		if(($backend_result) && ($this->db->num_rows($backend_result)>0))
		{
			while($a = $this->db->fetch_row($backend_result))
			{
				$data[] = $a[$field];
			}
		}
		else
		{
			$this->error .= $this->db->error();
			return false;
		}
		
		return $data;
	}


	function search($array)
	{
		// Nothing
	}
	
	function showError()
	{
		echo nl2br($this->error);
	}
}

?>