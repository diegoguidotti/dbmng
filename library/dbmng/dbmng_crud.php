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
function dbmng_create_form_process($aForm, $aParam, $actiontype="") 
{
	$ret=null;

	//echo $_REQUEST['tbln'] ." ".$aForm['table_name']."|".$_REQUEST['act']."|";
	//if(isset($_REQUEST['tbln']) && isset($_REQUEST['act']))
	if(isset($_REQUEST['act']))
		{
			//check if the table correspond to the table requested in the form
			//if($aForm['table_name']==$_REQUEST['tbln'])
			if(true)
				{
					// update record
					if($_REQUEST['act']=='do_upd')
						$ret=dbmng_update($aForm, $aParam);
					
					// insert record
					if($_REQUEST['act']=='do_ins'){
						$ret=dbmng_insert($aForm, $aParam);		

						if(isset($aParam['redirect'])){
							header("Location: " . $aParam['redirect']);
							exit();
						}

					}
					
					// delete record
					if($_REQUEST['act']=='del')
						$ret=dbmng_delete($aForm, $aParam);		
	
					// duplicate record
					if($_REQUEST['act']=='dup')
						$ret=dbmng_duplicate($aForm, $aParam);

					// print record
					if($_REQUEST['act']=='prt_rec')
						dbmng_print_rec($aForm, $aParam);

					// print table
					if($_REQUEST['act']=='prt_tbl')
						dbmng_print_table($aForm, $aParam);

					if(isset($ret))
						{
							if(!$ret['ok'])
								{
									if(DBMNG_CMS=='drupal')
										drupal_set_message($ret['error']);
									else
										echo '<div class="message error">'.$ret['error'].'</div>';
								}
								else{

									

								}
						}
				}
			else
				{
					//TODO: update error message
					echo t('You have not the right to access to the table you request') .  ' ' . $aForm['table_name'] . ' ' . $_REQUEST['tbln'] . '!';
				}
		}
		else{
			//echo(t('act variable is missing'));
		}

	return $ret;


	/*
	if( isset($_REQUEST['tbln']) && isset($_REQUEST['act2']) &&  !isset($_REQUEST['act']) )
		{
			//check if the table correspond to the table requested in the form
			if($aForm['table_name']==$_REQUEST['tbln'])
				{
					// search record
					if($_REQUEST['act2']=='do_search')
						dbmng_search($aForm, $aParam);
				}
		}
		*/

}

/////////////////////////////////////////////////////////////////////////////
// dbmng_delete -> replaced by $dbmng->delete()
// ======================
/// This function delete the selected record
/**
\param $aForm  		Associative array with all the characteristics
\param $aParam  	Associative array with some custom variable used by the renderer
\return $result	SQL result
*/
function dbmng_delete($aForm, $aParam) 
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

		if( isset($aParam['filters']) )
				{
					foreach ( $aParam['filters'] as $fld => $fld_value )
						{
								//drupal_set_message("Add ".$fld." ".$fld_value);
								$where .= $fld . " = :$fld and ";
								$var = array_merge($var, array(":".$fld => $fld_value));
						}					
				}


	$where = substr($where, 0, strlen($where)-4);
	//TODO: add also filter fields in delete/update
	$result = dbmng_query("delete from " . $aForm['table_name'] . " WHERE $where ", $var);
	
	if($result['ok']){
		foreach ( $aForm['fields'] as $fld => $fld_value )
			{
				if( isset($fld_value['widget']) )
					{
						if($fld_value['widget']=='select_nm')
							{		
								$table_nm=$fld_value['table_nm'];
								$field_nm=$fld_value['field_nm'];
			
								$sql = "delete from ".$table_nm." where ".$where;
								$res_nm = dbmng_query( $sql, $var);
							}
					}
			}
	}

	return $result;
					
}

/////////////////////////////////////////////////////////////////////////////
// dbmng_search_add_hidden
// ======================
/// This function add hidden var to the generated form
/**
\param $aForm  		Associative array with all the characteristics
\param $aParam  	Associative array with some custom variable used by the renderer
\return $hv			Hidden variable
*/
function dbmng_search_add_hidden($aForm, $aParam, $type) 
{
	$hv = "";
	if(isset($_REQUEST['act2']))
		{
			if($_REQUEST['act2']=='do_search')
				{
					if( $type == "POST" )
						$hv .= "<input type='hidden' name='act2' value='".$_REQUEST['act2']."'\>";
						
					foreach( $aForm['fields'] as $fld => $fld_value )
						{
							if( $fld_value['is_searchable'] == 1 )
								{
									if(isset($_REQUEST[$fld]))
										{
											if( $_REQUEST[$fld] != '' )
												{
													if( $type == "POST" )
														$hv .= "<input type='hidden' name='".$fld."' value='".$_REQUEST[$fld]."'/>";
													else
														$hv .= "&".$fld ."=". $_REQUEST[$fld];
												}
										}
								}
						}
				}
		}
return $hv;
}

/////////////////////////////////////////////////////////////////////////////
// dbmng_check_is_pk -> not used any more
// ======================
/// This function return true if the $fld_value is a primary key
/**
\param $fld_value  		Field name
\return $ret			boolean
*/
function dbmng_check_is_pk($fld_value)
{
	$ret=false;
	if(!isset($fld_value['key']))
		$ret = false;
	elseif ($fld_value['key'] == 1 || $fld_value['key'] == 2)
		$ret=true;
		
	return $ret;
}

/////////////////////////////////////////////////////////////////////////////
// dbmng_check_is_autopk -> not used any more
// ======================
/// This function return true if the $fld_value is an autoincrement primary key
/**
\param $fld_value  		Field name
\return $ret			boolean
*/
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
\result $return	SQL result
*/
function dbmng_duplicate($aForm, $aParam) 
{
	$sWhat = "";
	$bSelectNM = false;
	foreach ( $aForm['fields'] as $fld => $fld_value )
		{
			// if($fld !== $aForm['primary_key'][0])
			if($fld_value['key'] != 1)
				{
					if($fld_value['widget']!='select_nm')
						{		
							$sWhat .= $fld . ", ";
						}
					else
						{
							$bSelectNM = true;
						}
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
	// $result = dbmng_query("delete from " . $aForm['table_name'] . " WHERE $where ", $var);
	$result = dbmng_query("insert into " . $aForm['table_name'] . " (" . $sWhat . ") select " . $sWhat . " from " . $aForm['table_name'] . " where $where ", $var);
	
	if( $result['ok'] && $bSelectNM )
		{
			$id_key=$result['inserted_id'];

			$aWhere = array();
			$aWhere = array_merge($aWhere, $var);
			$whereFields='';

			foreach ( $aForm['fields'] as $fld => $fld_value )
				{									
					if( dbmng_check_is_pk($fld_value) )
						{
							$whereFields .= "$fld, ";
						}
				}
			
			foreach ( $aForm['fields'] as $fld => $fld_value )
				{
					if($fld_value['widget']=='select_nm')
						{		
							$table_nm=$fld_value['table_nm'];
							$field_nm=$fld_value['field_nm'];
							$sql = "insert into ".$table_nm." (".$whereFields." ".$field_nm.") select ".$id_key.", ".$field_nm." from ".$table_nm." where ".$where;
							$res = dbmng_query( $sql, $aWhere);
						}
				}
		}

	return $result ;

}


/////////////////////////////////////////////////////////////////////////////
// dbmng_insert -> replaced by $dbmng->insert($aVars) or by $dbmng->insert($dbmng->processRequest($_REQUEST))
// ======================
/// This function insert a new record
/**
\param $aForm  		Associative array with all the characteristics
\param $aParam  	Associative array with some custom variable used by the renderer
\result $result	SQL result
*/
function dbmng_insert($aForm, $aParam) 
{
	$sWhat = "";
	$sVal  = "";

	$var = array();
	$bSelectNM = false;
	foreach ( $aForm['fields'] as $fld => $fld_value )
		{
			$readonly=false;
			if(isset($fld_value['readonly'])){
				$readonly=$fld_value['readonly'];
			}
			
			//if($fld !== $aForm['primary_key'][0])
			if($fld_value['key'] != 1)
				{
						if(!var_equal($fld_value, 'widget','select_nm') && !$readonly )
							{		
								$sWhat .= $fld . ", ";
								$sVal.=":$fld ,";	
								$var = array_merge($var, array(":".$fld => dbmng_value_prepare($fld_value,$fld,$_POST,$aParam)));
							}
						else
							{
								$bSelectNM = true;
							}
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
							if( is_array($fld_value) )
								{
									foreach( $fld_value as $f => $v )
										{
											$sWhat.=$fld.", ";
											$sVal.=":$fld, ";
				
											$var = array_merge($var, array(":".$fld =>  $fld_value ));
										}
								}
							else	
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

	//echo debug_sql_statement($sql, $var);
	$result = dbmng_query($sql, $var);


	if($result['ok']){
		if( $bSelectNM )
			$res = dbmng_insert_nm($aForm, $aParam, $result['inserted_id']);
	}

	return $result;
	
}


/////////////////////////////////////////////////////////////////////////////
// dbmng_insert_nm -> replaced by $dbmng->insert($aVars, id_key) 
// ======================
/// This function insert a new record in one-to-many table
/**
\param $aForm  		Associative array with all the characteristics
\param $aParam  	Associative array with some custom variable used by the renderer
\param $id_key  	primary key of "one" table
\result $result	SQL result
*/
function dbmng_insert_nm($aForm, $aParam, $id_key)
{
	$aWhere = array();
	$whereFields='';
	$whereFieldsV='';
	
	foreach ( $aForm['fields'] as $fld => $fld_value )
		{									
			if( dbmng_check_is_pk($fld_value) )
				{
					$whereFields .= "$fld, ";
					$whereFieldsV  .= ":$fld, ";

					if( isset($id_key) )
						{
							$aWhere = array_merge( $aWhere, array(":".$fld => $id_key) );
						}
					else
						{
							$aWhere = array_merge( $aWhere, array(":".$fld => $_REQUEST[$fld]) );
						}
				}
		}

	foreach ( $aForm['fields'] as $fld => $fld_value )
		{
			if($fld_value['widget']=='select_nm')
				{		
					$table_nm=$fld_value['table_nm'];
					$field_nm=$fld_value['field_nm'];
					
					$where_del   = substr($whereFields,0,strlen($whereFields)-2);
					$where_del_v = substr($whereFieldsV,0,strlen($whereFieldsV)-2);
					
					dbmng_query("delete from ".$table_nm." WHERE ". $where_del ."=".$where_del_v, $aWhere);
					//echo "<br/>".debug_sql_statement("delete from ".$table_nm." WHERE ". $where_del ."=".$where_del_v, $aWhere);
					//print_r ($_POST);

					//echo ("<br/>!|".dbmng_value_prepare($fld_value,$fld,$_POST,$aParam)."|!</br>");

					$vals= explode('|',dbmng_value_prepare($fld_value,$fld,$_POST,$aParam));
					//print_r($vals);
					
					foreach ( $vals as $k => $v )
						{	
							$aVals = array_merge( $aWhere, array(":".$field_nm => intval($v) ) );
							
							$sql = "insert into ".$table_nm." (".$whereFields." ".$field_nm.") values (".$whereFieldsV." :".$field_nm.")";
							//echo "<br/>".$sql." ".$k." ".$v;

							if( false ) //to be further investigated
								{
									echo "pre";
									$sql = debug_sql_statement($sql,$aVals);
									echo "post";
									$result = dbmng_query( $sql, array());
								}
							else
								{
									$result = dbmng_query( $sql, $aVals);
								}
	
							if(isset($result['error'])){
								print_r ($result);
							}
						}

				}
		}
	return $result;
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
// dbmng_update -> replaced by $dbmng->update($aVars) 
// ======================
/// This function update an existing record
/**
\param $aForm  		Associative array with all the characteristics
\param $aParam  	Associative array with some custom variable used by the renderer
\result $result	SQL result
*/
function dbmng_update($aForm, $aParam) 
{



	$sSet = "";
	$var = array();

	$bSelectNM = false;
	
	foreach ( $aForm['fields'] as $fld => $fld_value )
		{

			$readonly=false;
			if(isset($fld_value['readonly'])){
				$readonly=$fld_value['readonly'];
			}

			if($fld_value['key'] != 1 && $fld_value['key'] != 2 ) 
				{
					if( !$readonly ) //$fld_value['readonly'] != 1 )
						{
							if( isset($fld_value['widget']) )
								{
									if($fld_value['widget']!='select_nm')
										{		
											$sSet .= $fld . " = :$fld, ";
				
											$var = array_merge($var, array(":".$fld => dbmng_value_prepare($fld_value,$fld,$_POST,$aParam)));
											//$sSet.=dbmng_value_prepare($x_value,$x,$_POST).", ";
										}
									else
										{
											$bSelectNM = true;
										}
								}
							else
								{
									$sSet .= $fld . " = :$fld, ";

									$var = array_merge($var, array(":".$fld => dbmng_value_prepare($fld_value,$fld,$_POST,$aParam)));
									//$sSet.=dbmng_value_prepare($x_value,$x,$_POST).", ";
								}
						}
				}
		}
	if( isset($aParam) )
		{
			if( isset($aParam['auto_field']) )
				{
					foreach ( $aParam['auto_field'] as $fld => $fld_value )
						{
							if( is_array($fld_value) )
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
							else	
								{
									$sSet .= $fld . " = :$fld, ";
									$var = array_merge($var, array(":".$fld => $v));
								}
						}					
				}
		}
	$sSet = substr($sSet, 0, strlen($sSet)-2);

	$where  = "";
	$whereFields  = "";
	$whereFieldsV  = "";
	$aWhere = array();

	foreach ( $aForm['fields'] as $fld => $fld_value )
		{									
			if( dbmng_check_is_pk($fld_value) )
				{
					$where .= "$fld = :$fld and ";
					$whereFields .= "$fld, ";
					$whereFieldsV  .= ":$fld, ";

					$val=$_REQUEST[$fld];
					if($fld_value['type']=='date'){
						if($val==''){
							$val=null;
						}
					}
					
					$aWhere = array_merge( $aWhere, array(":".$fld => $val) );
				}
		}

		if( isset($aParam['filters']) )
				{
					foreach ( $aParam['filters'] as $fld => $fld_value )
						{
								//drupal_set_message("Add ".$fld." ".$fld_value);
								$where .= $fld . " = :$fld and ";
								$aWhere = array_merge($aWhere, array(":".$fld => $fld_value));
						}					
				}


	$where = substr($where, 0, strlen($where)-4);		
	$var   = array_merge($var, $aWhere);

	//TODO: add also filter fields in delete/update
	
	$result = dbmng_query("update " . $aForm['table_name'] . " set $sSet where $where ", $var);
	//echo debug_sql_statement("update " . $aForm['table_name'] . " set $sSet where $where ", $var);
	if($result['ok'] && $bSelectNM )
		$res = dbmng_insert_nm($aForm, $aParam, null);

/*
	if( false && $bSelectNM )
		{
			foreach ( $aForm['fields'] as $fld => $fld_value )
				{
					if($fld_value['widget']=='select_nm')
						{		
							$table_nm=$fld_value['table_nm'];
							$field_nm=$fld_value['field_nm'];
			
							dbmng_query(" delete from ".$table_nm." WHERE ". $where, $aWhere);
			
							$vals= explode('|',dbmng_value_prepare($fld_value,$fld,$_POST,$aParam));
							foreach ( $vals as $k => $v )
								{	
									$aVals = array_merge( $aWhere, array(":".$field_nm => intval($v) ) );
									//echo debug_sql_statement(" insert into ".$table_nm." (".$whereFields." ".$field_nm.") VALUES (".$whereFieldsV." :".$field_nm.") ",$aVals).'<br/>';
				
									dbmng_query("insert into ".$table_nm." (".$whereFields." ".$field_nm.") values (".$whereFieldsV." :".$field_nm.") ",$aVals);
								}
						}
				}
		}
*/


	return $result;

	//if(isset($result['error'])){
	//	print_r ($result['error']);
	//}

}

//MM -> check probaby to be removed
// Move outside this file
function dbmng_print_rec($aForm, $aParam)
{
	$sql = "select * from " . $aForm['table_name'] . " where " . $aForm['primary_key'][0] . " = :" . $aForm['primary_key'][0]; 
	$var = array(':'.$aForm['primary_key'][0] => $_GET[$aForm['primary_key'][0]]);
	
	$pdf = new PDF();
	// Column headings
	$data = $pdf->LoadData($sql,$var);
	$pdf->SetFont('Arial','',14);

	$pdf->AddPage();
	$pdf->printCV($aForm, $aParam, $data);
	$pdf->Output($aForm['table_name'].'.pdf','D');

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

//MM -> check probaby to be removed
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
