<?php 

/////////////////////////////////////////////////////////////////////////////
// dbmng_create_form_process
// ======================
/// This function prepare the form process (update, insert, delete, duplicate)
/**
\param $aForm  		Associative array with all the characteristics
\param $aParam  		Associative array with some custom variable used by the renderer
\return           HTML generated code
*/
function dbmng_create_form_process($aForm, $aParam) 
	{
		// update record
		dbmng_update($aForm, $aParam);
		// insert record
		dbmng_insert($aForm, $aParam);		
		// delete record
		dbmng_delete($aForm, $aParam);		
		// duplicate record
		dbmng_duplicate($aForm, $aParam);
	}

/////////////////////////////////////////////////////////////////////////////
// dbmng_delete
// ======================
/// This function delete the selected record
/**
\param $aForm  		Associative array with all the characteristics
*/
function dbmng_delete($aForm, $aParam) 
{
	if( isset($_REQUEST['act']) )
		{
			if( $_REQUEST['act'] == 'del' )
				{
					$where = "";
					$var = array();
					foreach ( $aForm['fields'] as $fld => $fld_value )
						{
							if( $fld_value['key'] == 1 || $fld_value['key'] == 2 )
								{
									$where .= "$fld = :$fld and ";
									$var = array_merge($var, array(":".$fld => $_REQUEST[$fld] ));
								}
						}
					$where = substr($where, 0, strlen($where)-4);
					$result = dbmng_query("delete from " . $aForm['table_name'] . " WHERE $where ", $var);
				}
		}
	/*
	if(isset($_REQUEST["del_" . $aForm['table_name']]))
		{
			$id_del = intval($_REQUEST["del_" . $aForm['table_name']]);
			
			//$pkfield=$aForm['primary_key'][0];
			foreach ( $aForm['fields'] as $fld => $fld_value )
				{
					if($fld_value['key'] == 1 || $fld_value['key'] == 2 )
						{
							$pkfield = $fld;
						}
				}

			$result = dbmng_query("delete from " . $aForm['table_name'] . " WHERE $pkfield = :$pkfield ", array( ":$pkfield" => intval($id_del)));
		}
	*/
}



/////////////////////////////////////////////////////////////////////////////
// dbmng_duplicate
// ======================
/// This function duplicate the selected record
/**
\param $aForm  		Associative array with all the characteristics
*/
function dbmng_duplicate($aForm, $aParam) 
{
	$sWhat = "";
	foreach ( $aForm['fields'] as $fld => $fld_value )
		{
			// if($fld !== $aForm['primary_key'][0])
			if($fld_value['key'] != 1)
				{
					$sWhat .= $fld . ", ";
				}
		}

	if( isset($aParam) )
	{
		if( isset($aParam['filters']) )
			{
					foreach ( $aParam['filters'] as $fld => $fld_value )
						{				
							$sWhat.=$fld.", ";
						}					
			}
	}

	$sWhat = substr($sWhat, 0, strlen($sWhat)-2);
	
	if( isset($_REQUEST['act']) )
		{
			if( $_REQUEST['act'] == 'dup' )
				{
					$where = "";
					$var = array();
					foreach ( $aForm['fields'] as $fld => $fld_value )
						{
							if( $fld_value['key'] == 1 || $fld_value['key'] == 2 )
								{
									$where .= "$fld = :$fld and ";
									$var = array_merge($var, array(":".$fld => $_REQUEST[$fld] ));
								}
						}
					$where = substr($where, 0, strlen($where)-4);
					// $result = dbmng_query("delete from " . $aForm['table_name'] . " WHERE $where ", $var);
					$result = dbmng_query("insert into " . $aForm['table_name'] . " (" . $sWhat . ") select " . $sWhat . " from " . $aForm['table_name'] . " where $where ", $var);
				}
		}
	/*
	if(isset($_REQUEST["dup_" . $aForm['table_name']]))
		{
			$id_dup  = intval($_REQUEST["dup_" . $aForm['table_name']]);
               
			$pkfield = "";
			foreach ( $aForm['fields'] as $fld => $fld_value )
				{
					if($fld_value['key'] == 1)
						{
							$pkfield = $fld;
						}
				}
			$var     = array(":".$pkfield =>  $id_dup );
			$result  = dbmng_query("insert into " . $aForm['table_name'] . " (" . $sWhat . ") select " . $sWhat . " from " . $aForm['table_name'] . " where " . $pkfield . " = :" . $pkfield, $var);
		}
	*/
}


/////////////////////////////////////////////////////////////////////////////
// dbmng_insert
// ======================
/// This function insert a new record
/**
\param $aForm  		Associative array with all the characteristics
*/
function dbmng_insert($aForm, $aParam) 
{
	if(isset($_POST["ins_" . $aForm['table_name']]))
		{
			$sWhat = "";
			$sVal  = "";

			$var = array();
			foreach ( $aForm['fields'] as $fld => $fld_value )
				{
					//if($fld !== $aForm['primary_key'][0])
					if($fld_value['key'] != 1)
						{
							$sWhat .= $fld . ", ";
							$sVal.=":$fld ,";	
							$var = array_merge($var, array(":".$fld => dbmng_value_prepare($fld_value,$fld,$_POST)));
						}
				}

			if( isset($aParam) )
				{
					if( isset($aParam['filters']) )
						{
							foreach ( $aParam['filters'] as $fld => $fld_value )
								{				
									$sWhat.=$fld.", ";
									$sVal.=":$fld, ";

									$var = array_merge($var, array(":".$fld =>  $fld_value ));


								}					
						}
				}

			$sWhat = substr($sWhat, 0, strlen($sWhat)-2);
			$sVal  = substr($sVal, 0, strlen($sVal)-2);
	
			$sql    = "insert into " . $aForm['table_name'] . " (" . $sWhat . ") values (" . $sVal . ")";
			$result = dbmng_query($sql, $var);
			//print_r( $_FILES );
		}
}

/////////////////////////////////////////////////////////////////////////////
// dbmng_create_form_upload_file
// ======================
/// This function upload the selected file in the server
/**
\param $aForm  		Associative array with all the characteristics
*/
function dbmng_create_form_upload_file($aForm, $aParam) 
{
	foreach ( $aForm['fields'] as $fld => $fld_value )
		{
			if( $fld_value['widget'] == 'file' )
				{
					if( $_FILES["file"]["error"] == 0 )
					  {
						  if (file_exists("upload/" . $_FILES[$fld]["name"]))
						  	{
						  		// $html .= $_FILES["file"]["name"] . " already exists. ";
						  	}
						  else
						  	{
								  move_uploaded_file($_FILES[$fld]["tmp_name"], "upload/" . $_FILES[$fld]["name"]);
								  // $html .= "Stored in: " . "upload/" . $_FILES["file"]["name"];
						  	}
					  }
				}
		}
}

/////////////////////////////////////////////////////////////////////////////
// dbmng_update
// ======================
/// This function update an existing record
/**
\param $aForm  		Associative array with all the characteristics
*/
function dbmng_update($aForm, $aParam) 
{
	if(isset($_POST["upd_" . $aForm['table_name']]))
		{
			$sSet = "";
			$var = array();

			foreach ( $aForm['fields'] as $fld => $fld_value )
				{
					if($fld_value['key'] != 1)
						{
							$sSet .= $fld . " = :$fld, ";

							$var = array_merge($var, array(":".$fld => dbmng_value_prepare($fld_value,$fld,$_POST)));
							//$sSet.=dbmng_value_prepare($x_value,$x,$_POST).", ";
						}
				}
		
			$sSet = substr($sSet, 0, strlen($sSet)-2);
	

			// $pk=$aForm['primary_key'][0] ;
			foreach ( $aForm['fields'] as $fld => $fld_value )
				{
					if($fld_value['key'] == 1 || $fld_value['key'] == 2 )
						{
							$pk = $fld;
						}
				}

			if( dbmng_is_field_type_numeric($aForm['fields'][$pk]['type']) )  
				{
					$id_upd = intval($_REQUEST["upd_" . $aForm['table_name']]);
					$var = array_merge($var, array(":$pk" => ($id_upd)));
				}
			else
				{
					$id_upd = $_REQUEST["upd_" . $aForm['table_name']];
					$var = array_merge($var, array(":$pk" => $id_upd));
				}
			
			$sql    = "update " . $aForm['table_name'] . " set " . $sSet . " where " . $pk . " = :$pk " ;
			$result = dbmng_query($sql, $var);
		}
}

?>

