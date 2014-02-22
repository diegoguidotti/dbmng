<?php 

/////////////////////////////////////////////////////////////////////////////
// dbmng_create_form_process
// ======================
/// This function prepare the form process (update, insert, delete, duplicate)
/**
\param $aForm  		Associative array with all the characteristics
\param $aParam  	Associative array with some custom variable used by the renderer
\return           HTML generated code
*/
function dbmng_create_form_process($aForm, $aParam) 
{
	if(isset($_REQUEST['tbln']) && isset($_REQUEST['act']))
		{
			//check if the table correspond to the table requested in the form
			if($aForm['table_name']==$_REQUEST['tbln'])
				{
					// update record
					if($_REQUEST['act']=='do_upd')
						dbmng_update($aForm, $aParam);
					
					// insert record
					if($_REQUEST['act']=='do_ins')
						dbmng_insert($aForm, $aParam);		
					
					// delete record
					if($_REQUEST['act']=='del')
						dbmng_delete($aForm, $aParam);		
	
					// duplicate record
					if($_REQUEST['act']=='dup')
						dbmng_duplicate($aForm, $aParam);

					// print record
					if($_REQUEST['act']=='prt_rec')
						dbmng_print_rec($aForm, $aParam);

					// print table
					if($_REQUEST['act']=='prt_tbl')
						dbmng_print_table($aForm, $aParam);

				}
			else
				{
					//TODO: update error message
					echo t('You have not the right to access to the table you request') .  ' ' . $aForm['table_name'] . ' ' . $_REQUEST['tbln'] . '!';
				}
		}

	if(isset($_REQUEST['tbln']) && isset($_REQUEST['act2']))
		{
			//check if the table correspond to the table requested in the form
			if($aForm['table_name']==$_REQUEST['tbln'])
				{
					// search record
					if($_REQUEST['act2']=='do_search')
						dbmng_search($aForm, $aParam);
				}
		}

}

/////////////////////////////////////////////////////////////////////////////
// dbmng_delete
// ======================
/// This function delete the selected record
/**
\param $aForm  		Associative array with all the characteristics
\param $aParam  	Associative array with some custom variable used by the renderer
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
							if( dbmng_check_is_pk($fld_value) )
								{
									$where .= "$fld = :$fld and ";
									$var = array_merge($var, array(":".$fld => $_REQUEST[$fld] ));
								}
						}

					$where = substr($where, 0, strlen($where)-4);
					//TODO: add also filter fields in delete/update
					$result = dbmng_query("delete from " . $aForm['table_name'] . " WHERE $where ", $var);
				}
		}
}

/////////////////////////////////////////////////////////////////////////////
// dbmng_search
// ======================
/// This function return a subset of data
/**
\param $aForm  		Associative array with all the characteristics
\param $aParam  	Associative array with some custom variable used by the renderer
*/
function dbmng_search($aForm, $aParam) 
{
	if( isset($_REQUEST['act2']) )
		{
			if( $_REQUEST['act2'] == 'do_search' )
				{

				}
		}
}

function dbmng_check_is_pk($fld_value)
{
	$ret=false;
	if(!isset($fld_value['key']))
		$ret = false;
	elseif ($fld_value['key'] == 1 || $fld_value['key'] == 2)
		$ret=true;
		
	return $ret;
}

function dbmng_check_is_autopk($fld_value)
{
	$ret=false;
	if(!isset($fld_value['key']))
		$ret = false;
	elseif ($fld_value['key'] == 1 )
		$ret=true;
		
	return $ret;
}


/////////////////////////////////////////////////////////////////////////////
// dbmng_duplicate
// ======================
/// This function duplicate the selected record
/**
\param $aForm  		Associative array with all the characteristics
\param $aParam  	Associative array with some custom variable used by the renderer
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
}


/////////////////////////////////////////////////////////////////////////////
// dbmng_insert
// ======================
/// This function insert a new record
/**
\param $aForm  		Associative array with all the characteristics
\param $aParam  	Associative array with some custom variable used by the renderer
*/
function dbmng_insert($aForm, $aParam) 
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
					$var = array_merge($var, array(":".$fld => dbmng_value_prepare($fld_value,$fld,$_POST,$aParam)));
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
			
			if( isset($aParam['auto_field']) )
				{
					foreach ( $aParam['auto_field'] as $fld => $fld_value )
						{
							foreach( $fld_value as $f => $v )
								{
									$sWhat.=$fld.", ";
									$sVal.=":$fld, ";
		
									$var = array_merge($var, array(":".$fld =>  $fld_value ));
								}
						}					
				}
		}

	$sWhat = substr($sWhat, 0, strlen($sWhat)-2);
	$sVal  = substr($sVal, 0, strlen($sVal)-2);

	$sql    = "insert into " . $aForm['table_name'] . " (" . $sWhat . ") values (" . $sVal . ")";
	$result = dbmng_query($sql, $var);
	//print_r( $_FILES );
}

/////////////////////////////////////////////////////////////////////////////
// dbmng_create_form_upload_file
// ======================
/// This function upload the selected file in the server
/**
\param $aForm  		Associative array with all the characteristics
\param $aParam  	Associative array with some custom variable used by the renderer
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
\param $aParam  	Associative array with some custom variable used by the renderer
*/
function dbmng_update($aForm, $aParam) 
{
	$sSet = "";
	$var = array();

	foreach ( $aForm['fields'] as $fld => $fld_value )
		{
			if($fld_value['key'] != 1)
				{
					$sSet .= $fld . " = :$fld, ";

					$var = array_merge($var, array(":".$fld => dbmng_value_prepare($fld_value,$fld,$_POST,$aParam)));
					//$sSet.=dbmng_value_prepare($x_value,$x,$_POST).", ";
				}
		}
	if( isset($aParam) )
		{
			if( isset($aParam['auto_field']) )
				{
					foreach ( $aParam['auto_field'] as $fld => $fld_value )
						{
							foreach( $fld_value as $f => $v )
								{
									if( $f == "U" )
										{
											$sSet .= $fld . " = :$fld, ";
											$var = array_merge($var, array(":".$fld => $v));
										}
								}
						}					
				}
		}
	$sSet = substr($sSet, 0, strlen($sSet)-2);

	$where  = "";
	$aWhere = array();
	foreach ( $aForm['fields'] as $fld => $fld_value )
		{
			if( dbmng_check_is_pk($fld_value) )
				{
					$where .= "$fld = :$fld and ";
					$aWhere = array_merge( $aWhere, array(":".$fld => $_REQUEST[$fld]) );
				}
		}

	$where = substr($where, 0, strlen($where)-4);
	$var   = array_merge($var, $aWhere);

	//TODO: add also filter fields in delete/update
	
	$result = dbmng_query("update " . $aForm['table_name'] . " set $sSet where $where ", $var);
}

// Move outside this file
function dbmng_print_rec($aForm, $aParam)
{
	//require('sites/all/libraries/fpdf/fpdf.php');

	$sql = "select * from " . $aForm['table_name'] . " where " . $aForm['primary_key'][0] . " = :" . $aForm['primary_key'][0]; 
	$var = array(':'.$aForm['primary_key'][0] => $_GET[$aForm['primary_key'][0]]);
	
	$pdf = new PDF();
	// Column headings
	$data = $pdf->LoadData($sql,$var);
	$pdf->SetFont('Arial','',14);
	//$pdf->AddPage();
	//$pdf->BasicTable($aForm, $aParam, $data);
	//$pdf->AddPage();
	//$pdf->ImprovedTable($aForm, $aParam, $data);
	$pdf->AddPage();
	$pdf->FancyTable($aForm, $aParam, $data);
	$pdf->Output('pippo.pdf','D');

/*
	$result = dbmng_query($sql, $var);

	// echo debug_sql_statement($sql, $var);
	$pdf = new FPDF();
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',16);

	$value = "";
	foreach( $result as $record )
		{
			foreach ( $aForm['fields'] as $fld => $fld_value )
				{
					if( layout_view_field_table($fld_value) )
						{
							if(isset($record->$fld))
								{
									$value=$record->$fld; //dbmng_value_prepare_html($fld_value, $record->$fld, $aParam, "table");
								}
							else
								{//TODO: add a comma separated list if widget==multi
									$value.= "&nbsp;";							
								}
							$pdf->Cell(40,10,$fld,0);
							$pdf->Cell(40,10,$value,0);
							$pdf->Ln();
						}
				}
		}
	$pdf->Output('pippo.pdf','D');
*/
}

// Move outside this file
function dbmng_print_table($aForm, $aParam)
{
	$sql = "select * from " . $aForm['table_name']; 
	$var = array();
	
	$pdf = new PDF();
	// Column headings
	$data = $pdf->LoadData($sql,$var);
	$pdf->SetFont('Arial','',14);
	//$pdf->AddPage();
	//$pdf->BasicTable($aForm, $aParam, $data);
	//$pdf->AddPage();
	//$pdf->ImprovedTable($aForm, $aParam, $data);
	$pdf->AddPage();
	$pdf->FancyTable($aForm, $aParam, $data);
	$pdf->Output('pippo.pdf','D');

/*
	$result = dbmng_query($sql, $var);

	// echo debug_sql_statement($sql, $var);
	$pdf = new FPDF();
	$pdf->AddPage();
	$pdf->SetFont('Arial','B',16);

	$value = "";
	foreach( $result as $record )
		{
			foreach ( $aForm['fields'] as $fld => $fld_value )
				{
					if( layout_view_field_table($fld_value) )
						{
							if(isset($record->$fld))
								{
									$value=$record->$fld; //dbmng_value_prepare_html($fld_value, $record->$fld, $aParam, "table");
								}
							else
								{//TODO: add a comma separated list if widget==multi
									$value.= "&nbsp;";							
								}
							$pdf->Cell(40,10,$fld,0);
							$pdf->Cell(40,10,$value,0);
							$pdf->Ln();
						}
				}
		}
	$pdf->Output('pippo.pdf','D');
*/
}
?>

