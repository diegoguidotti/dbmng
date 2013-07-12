<?php
include_once "sites/all/library/dbmng/dbmng_extend_functions.php";
include_once "sites/all/library/dbmng/layout.php";
/*
Convention to be used in code:

insert:    "ins_" . $aForm['table_name']
update:    "upd_" . $aForm['table_name']
delete:    "del_" . $aForm['table_name']
duplicate: "dup_" . $aForm['table_name']

Associative array with all the characteristics to manage a table

 		$aForm= array(				
				'id_table' => 'ID',
				'table_name' => 'test',
				'primary_key' => array(),
				'fields' => array(
					array(
							'field_name' => 'name',
							'label' => 'Name',
							'label_long' => "What your name?"
							'type' => 'text',
							'default' => 'default',
							'field_function' => 'name function'
						),
					array(
							'field_name' => 'eta',
							'label' => 'Et&agrave',								
							'type' => 'text'
							'default' => 'default'
							'field_function' => 'name function'
						),
				)
			);
*/


function getVersion()
	{
		return "0.0.1";
	}


/////////////////////////////////////////////////////////////////////////////
// dbmng_get_form_array
// ======================
/// This function allow to get fields information into structured array 
/**
\param $id_table  table id
\return           structured array 
*/
function dbmng_get_form_array($id_table)
	{
		$aForm = array();

		$table = db_query("select * from dbmng_tables where id_table=".$id_table);
		$aForm['id_table']    = $id_table;
		$fo = $table->fetchObject();
		$aForm['table_name']  = $fo->table_name;
		$aForm['table_label'] = $fo->table_label;

		//TODO: ['primary key shoud be an array to manage multiples key']
		$aFields = array();
		$aPK     = array(); // Array to store information about the primary key
		$fields = db_query("select * from dbmng_fields where id_table=" . $id_table . " order by field_order ASC");
		foreach ($fields as $fld)
			{
				if($fld->pk == 1)
				{
					$aPK[] = $fld->field_name;
				}
//				$aForm['primary_key'] = $fld->field_name; 

				$sLabelLong = ( strlen($fld->field_label_long)>0 ? $fld->field_label_long : $fld->field_label );
				$aFields[$fld->field_name] = array('label' => $fld->field_label, 
																					 'type' => $fld->id_field_type, 
																					 'widget' => $fld->field_widget, 
																					 'value' => null, 
																					 'nullable' => $fld->nullable, 
																					 'default' => $fld->default_value,
																					 'field_function' => $fld->field_function,
																					 'label_long' => $sLabelLong,
																					 'skip_in_tbl' => $fld->skip_in_tbl,
																					 'voc_sql' => $fld->voc_sql );
				
				if( $fld->field_widget == 'select' )
					{
						if( !isset($fld->voc_sql) )
							{
								// sql automatically generated throught standard coding tables definition
								$sVoc = str_replace("id_", "", $fld->field_name);
								$sql  = "select * from $sVoc";
							}
						else
							{
								// sql written in dbmng_fields
								$sql  = $fld->voc_sql;							
							}

						$rVoc  = db_query($sql);
						$aFVoc = array();

						$v       = 0;
						foreach($rVoc as $val)
							{
								//print_r($val);
								$keys=array_keys((array)$val);

								//print_r($keys);
								$aFVoc[$val->$keys[0]] = $val->$keys[1];
							}
						
						$sortAux = array();
						foreach($aFVoc as $res)
							$sortAux[] = $res[0];
						
						//array_multisort($sortAux, SORT_ASC, $aFVoc);
						
						$aFields[$fld->field_name]['voc_val'] = $aFVoc;
					}
			}

		$aForm['primary_key'] = $aPK; 
		if(!array_key_exists('primary_key', $aForm))
			{
				$aForm['primary_key'] = array('id_' . $aForm['table_name']);	
			}
		
		/*	
		echo "aPK: ";
		print_r($aPK);
		echo "<br />";
		print_r($aForm['primary_key']);
		echo "<br />";
		*/
		$aForm['fields']=$aFields;
		//print_r($aForm);
		
		return $aForm;
	}


/////////////////////////////////////////////////////////////////////////////
// dbmng_create_table
// ======================
/// This function create a table starting from a structured array
/**
\param $aForm  		Associative array with all the characteristics of the table
\param $aParam  		Associative array with some custom variable used by the renderer
\return           HTML generated code
*/
function dbmng_create_table($aForm, $aParam)
{
  
  // Initialize where clause and hidden variables
	$where = " WHERE 1 ";

		if(isset($aParam))
			{
				if( isset($aParam['filters']) )
				{
					foreach ( $aParam['filters'] as $x => $x_value )
						{				
								$where.=" AND $x = $x_value ";
						}					
				}
			}
		
		$html = "";
		if( !isset($_GET["ins_" . $aForm['table_name']]) && !isset($_GET["upd_" . $aForm['table_name']]) )
			{
			  $sql = 'select * from ' . $aForm['table_name'].' '. $where;
				$result = db_query($sql);
			  
			  $tblLbl = (!is_null($aForm['table_label']) ? t($aForm['table_label']) : $aForm['table_name']);

				$html  .= "<div class='dbmng_div' id='dbmng_".$aForm['table_name']."'>";

				
				$html  .= "<h1 class='dbmng_table_label'>" . $tblLbl . "</h1>\n";
				
				$html .= layout_table( $result, $aForm, $aParam );

				$html  .= "<div class='dbmng_record_number'>" . t("Record number") . ": " . $result->rowCount() . " " . t("recs") . "</div>\n";
				$html  .= '</div>';

			}
			return $html;
	}


/////////////////////////////////////////////////////////////////////////////
// dbmng_create_form
// ======================
/// This function create the form to (insert / update) from a structured array 
/**
\param $aForm  		Associative array with all the characteristics
\param $aParam  		Associative array with some custom variable used by the renderer
\return           HTML generated code
*/
function dbmng_create_form($aForm, $aParam) 
{
	$html      = "";
	$do_update = false;
  //get some hidden variables if exists()
	$hv = '';
	
	if(isset($aParam))
		{
			if(isset($aParam['hidden_vars']))
			{
				foreach ( $aParam['hidden_vars'] as $x => $x_value )
				{				
					$hv.= ("<input type='hidden' name='".$x."' value='".$x_value."' />\n");
				}
			}
		}
	
	if(isset($_GET["upd_" . $aForm['table_name']]))
		{
			$do_update = true;
		}

	if ( isset($_GET["ins_" . $aForm['table_name']]) || $do_update )
		{
      if( $do_update )
		    {
					$id_upd    = $_GET["upd_" . $aForm['table_name']];

					$sql       = "select * from " . $aForm['table_name'] . " where " . $aForm['primary_key'][0] . "=" . intval($id_upd);
					$result    = db_query($sql );		
					$vals      = $result->fetchObject();
				}


			$html .= "<form method='POST' action='?' >\n".$hv."";
			foreach ( $aForm['fields'] as $fld => $fld_value )
				{
					if( isset($fld_value['field_function']) )
						{
							if( function_exists($fld_value['field_function']) )
								$html .= call_user_func($fld_value['field_function']);
						}
					else
						{
							if( $aForm['primary_key'][0] != $fld )
								{
									
									$value= null;
									if($do_update)
										{
											$value = $vals->$fld;
										}
									elseif( !is_null($fld_value['default'])  )
										{
											$value = $fld_value['default'];
										}
									
									if ($fld_value['widget']=='textarea')
									{
										$html .= layout_form_textarea( $fld, $fld_value, $value );
									}
									else if ($fld_value['widget']=='select')
									{
										$html .= layout_form_select( $fld, $fld_value, $value );
									}
									else //use input by default
									{
										$html .= layout_form_input( $fld, $fld_value, $value );		
									}

									$html.="<br />\n";
								}
						}
				}

			if( isset($_GET["upd_" . $aForm['table_name']] ))
				{
					$html .= "<input type='hidden' name='upd_" . $aForm['table_name'] . "' value='" . $_GET["upd_" . $aForm['table_name']] . "' />\n";
					$html .= "<input type='submit' value='". t('Update') ."' />\n";
				}
			else
				{
					$html .= "<input type='hidden' name='ins_" . $aForm['table_name'] . "' />\n";
					$html .= "<input type='submit' value='" . t('Insert') . "' />\n";
				}

	    $html .= "</form>\n";
		}
		return $html;
}


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
		dbmng_create_form_update($aForm, $aParam);
		// insert record
		dbmng_create_form_insert($aForm, $aParam);		
		// delete record
		dbmng_create_form_delete($aForm, $aParam);		
		// duplicate record
		dbmng_create_form_duplicate($aForm, $aParam);
	}

/////////////////////////////////////////////////////////////////////////////
// dbmng_create_form_delete
// ======================
/// This function delete the selected record
/**
\param $aForm  		Associative array with all the characteristics
*/
function dbmng_create_form_delete($aForm, $aParam) 
{
	if(isset($_REQUEST["del_" . $aForm['table_name']]))
		{
			$id_del = intval($_REQUEST["del_" . $aForm['table_name']]);
			$result = db_query("delete from " . $aForm['table_name'] . " WHERE " . $aForm['primary_key'][0] . " = " . $id_del);
		}
}


/////////////////////////////////////////////////////////////////////////////
// dbmng_create_form_duplicate
// ======================
/// This function duplicate the selected record
/**
\param $aForm  		Associative array with all the characteristics
*/
function dbmng_create_form_duplicate($aForm, $aParam) 
{
	$sWhat = "";
	foreach ( $aForm['fields'] as $x => $x_value )
		{
			if($x !== $aForm['primary_key'][0])
				$sWhat .= $x . ", ";
		}

	if( isset($aParam) )
	{
		if( isset($aParam['filters']) )
			{
					foreach ( $aParam['filters'] as $x => $x_value )
						{				
							$sWhat.=$x.", ";
						}					
			}
	}

	$sWhat = substr($sWhat, 0, strlen($sWhat)-2);
	
	if(isset($_REQUEST["dup_" . $aForm['table_name']]))
		{
			$id_dup = intval($_REQUEST["dup_" . $aForm['table_name']]);
			$result = db_query("insert into " . $aForm['table_name'] . " (" . $sWhat . ") select " . $sWhat . " from " . $aForm['table_name'] . " where " . $aForm['primary_key'][0] . " = " . $id_dup);
		}
}


/////////////////////////////////////////////////////////////////////////////
// dbmng_create_form_insert
// ======================
/// This function insert a new record
/**
\param $aForm  		Associative array with all the characteristics
*/
function dbmng_create_form_insert($aForm, $aParam) 
{
	if(isset($_POST["ins_" . $aForm['table_name']]))
		{
			$sWhat = "";
			$sVal  = "";
			foreach ( $aForm['fields'] as $x => $x_value )
				{
					if($x !== $aForm['primary_key'][0])
						{
							$sWhat .= $x . ", ";
							
							$sVal.=dbmng_value_prepare($x_value,$_POST[$x]);							
						}
				}

			if( isset($aParam) )
				{
					if( isset($aParam['filters']) )
						{
							foreach ( $aParam['filters'] as $x => $x_value )
								{				
									$sWhat.=$x.", ";
									$sVal.=$x_value.", ";
								}					
						}
				}

			$sWhat = substr($sWhat, 0, strlen($sWhat)-2);
			$sVal  = substr($sVal, 0, strlen($sVal)-2);
	
			$sql    = "insert into " . $aForm['table_name'] . " (" . $sWhat . ") values (" . $sVal . ")";
			$result = db_query($sql);
		}
}

/////////////////////////////////////////////////////////////////////////////
// dbmng_create_form_update
// ======================
/// This function update an existing record
/**
\param $aForm  		Associative array with all the characteristics
*/
function dbmng_create_form_update($aForm, $aParam) 
{
	if(isset($_POST["upd_" . $aForm['table_name']]))
		{
			$sSet = "";
			foreach ( $aForm['fields'] as $x => $x_value )
				{
					if($x !== $aForm['primary_key'][0])
						{
							$sSet .= $x . " = ";

							$sSet.=dbmng_value_prepare($x_value,$_POST[$x]);
						}
				}
		
			$sSet = substr($sSet, 0, strlen($sSet)-2);
	
			$id_upd = intval($_REQUEST["upd_" . $aForm['table_name']]);
			$sql    = "update " . $aForm['table_name'] . " set " . $sSet . " where " . $aForm['primary_key'][0] . " = " . $id_upd;
			$result = db_query($sql);
		}
}

/////////////////////////////////////////////////////////////////////////////
// dbmng_value_prepare
// ======================
/// This function prepare the value from the POST request to insert it in the database
/**
\param $x_value  		The associative array with the field meta-variables
\param $sValue  		The value obtained by the request
\return             Value
*/
function dbmng_value_prepare($x_value, $sValue)
{
	$sVal='';
	$sType=$x_value['type'];

	//echo($sType.'|'.$sValue.'|'.is_null($x_value['default']).'|<br/>');

	//if exists a default value use the default values instead of null
	if(strlen($sValue)==0 && is_null($x_value['default']) )
		{
			$sVal  .= "NULL, ";
		}
	else
		{
			if(strlen($sValue)==0)
				{
					$sValue=$x_value['default'];
				}

			switch ($sType)
				{
					case ($sType=="int" || $sType=="bigint" || $sType=="float"  || $sType=="double") :
						$sVal  .= $sValue . ", ";							
						break;
				
					default:
						$sVal  .= "'" . $sValue . "', ";
				}
		}
  return $sVal;
}


?>
