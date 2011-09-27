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
              
    Document: include/stack.php
              
    Function: A simple array based stack class.

*********************************************************************/

class stack
{
	var $a;
	var $index;
	
	function stack()
	{
		$this->a = array();
		$this->index = 0;
	}
	
	function push($val)
	{
		array_unshift($this->a, $val);
	}

	function pop(
	){
		$val = $this->a[0];
		array_shift($this->a);
		return $val;
	}
	
	function top()
	{
		return $this->a[0];
	}
	
	function reset()
	{
		$this->index = 0;
	}
	
	function end()
	{
		return (count($a) - 1);
	}
	
	function next()
	{
		if ($this->index == count($this->a)) 
		{
			return false;
		}
		else
		{
			$val = $this->a[$this->index];
			$this->index++;
			return $val;
		}
	}
	
	function clean()
	{
		$this->a = array();
	}
	
	function dump()
	{
		return implode(",", $this->a);
	}
}
?>