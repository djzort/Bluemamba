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
              
    Document: include/sort2darray.php
              
    Function: Contains function that sorts 2D arrays.

*********************************************************************/

function Sort2DArray($array, $field, $order)
{
	if(!is_array($array))
	{
		return array();
	}
	if(count($array) <= 1)
	{
		return $array;
	}
	
	reset($array);
	while(list($key, $val) = each($array))
	{
		$index_a[$key] = $array[$key][$field];
	}
	
	if(strcasecmp($order, "ASC") == 0)
	{
		asort($index_a);
	}
	elseif(strcasecmp($order, "DESC") == 0)
	{
		arsort($index_a);
	}
	
	$result = array();
	reset($index_a);
	while(list($key, $val) = each($index_a))
	{
		$result[$key] = $array[$key];
	}
	
	return $result;
}

?>