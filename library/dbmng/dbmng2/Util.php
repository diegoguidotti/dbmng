<?php

namespace Dbmng;


class Util
{

	/////////////////////////////////////////////////////////////////////////////
	// var_equal
	// ======================
	/// This function return true if a key exist in one arrey and the value is equal to the val
	/// parameter
	/**
	\param $array  		  array to be searched
	\param $type_var  	name of the key
	\param $val  				value
	\return $ret				boolean
	*/
	function var_equal($array, $type_var, $val)
	{
		$ret=false;
		if(isset($array[$type_var]))
			{
				if($array[$type_var]==$val)
					{
						$ret=true;
					}
			}
		return $ret;
	}
	
	
}
