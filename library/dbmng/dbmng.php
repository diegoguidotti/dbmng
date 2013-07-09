<?php
include_once "sites/all/library/dbmng/dbmng_extend_functions.php";

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
							'label' => 'Nome',
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
		$fields = db_query("select * from dbmng_fields where id_table=".$id_table." order by field_order ASC");
		foreach ($fields as $fld)
			{
				if($fld->pk == 1)
				{
					$aPK[] = $fld->field_name;
				}
//				$aForm['primary_key'] = $fld->field_name; 
	
				$aFields[$fld->field_name] = array('label' => $fld->field_label, 
																					 'type' => $fld->id_field_type, 
																					 'value' => null, 
																					 'nullable' => $fld->nullable, 
																					 'default' => $fld->default_value,
																					 'field_function' => $fld->field_function);
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
	  $nIns = 0;
	  $nUpd = 0;
	  $nDel = 0;
	  $nDup = 0;
	  
	  $sql = 'select * from ' . $aForm['table_name'];
		$result = db_query($sql);
	  
		$html = "<h1>" . $aForm['table_name'] . "</h1>\n";
	
		//get some hidden variables if exists()
		$hv='';
		if(isset($aParam))
			{
				if(isset($aParam['hidden_vars']))
					{
						foreach ( $aParam['hidden_vars'] as $x => $x_value )
							{				
								$hv.= ('&amp;'.$x.'='.$x_value);
							}
					}
				
				if( isset($aParam['user_function']) )
				{
				  $nIns = $aParam['user_function']['ins'];
				  $nUpd = $aParam['user_function']['upd'];
				  $nDel = (isset($aParam['user_function']['del']) == true) ? $aParam['user_function']['del'] : 0;
				  $nDup = $aParam['user_function']['dup'];				
				}
			}
	
		
		$html .= "<table>";
		$html .= "<tr>";
		foreach ( $aForm['fields'] as $x => $x_value )
			{
				$html .= "<th>" . $x_value['label'] . "</th>";
			}
		$html .= "<th>" . t('functions') . "</th></tr>\n";
		
		foreach ($result as $record) 
			{
				// table value
				$html .= "<tr>";
				
				//get the query results for each field
				foreach ( $aForm['fields'] as $x => $x_value )
					{
						$html.= "<td>".$record->$x."</td>";
					}
				
				// available functionalities
				$html .= "<td>";
					if( $nDel == 1 )
						$html .= "<a href='?del_" . $aForm['table_name'] . "=" . $record->$aForm['primary_key'][0] .$hv."'>" . t('Delete') . "</a>" . "&nbsp;";
					if( $nUpd == 1 ) 
						$html .= "<a href='?upd_" . $aForm['table_name'] . "=" . $record->$aForm['primary_key'][0] .$hv."'>" . t('Update') . "</a>" . "&nbsp;";
					if( $nDup == 1 )
						$html .= "<a href='?dup_" . $aForm['table_name'] . "=" . $record->$aForm['primary_key'][0] .$hv."'>" . t('Duplicate') . "</a>" . "&nbsp;";
				$html .= "</td>\n";
				
				$html .= "</tr>";
			}
	  $html .= "</table>\n";
		
		if( $nIns == 1)
			$html .= "<a href='?ins_" . $aForm['table_name'] . $hv. "'>" . t('Insert new data') . "</a><br />";
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
			$do_update=true;
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
			foreach ( $aForm['fields'] as $x => $x_value )
				{
					if( isset($x_value['field_function']) )
						{
							if( function_exists($x_value['field_function']) )
								$html .= call_user_func($x_value['field_function']);
						}
					else
						{
							if( $aForm['primary_key'][0] != $x )
								{
									$html .= "<label for='$x'>" . t($x_value['label']) . "</label>";
									$html .= "<input name='" . $x . "' ";
									$html .= "id='$x' ";
									
									if( !is_null($x_value['default']) && !isset($_GET["upd_" . $aForm['table_name']]) )
										$html .= "value='" . $x_value['default']. "' ";
									else{
										;
									}
		
									$html .= "type='text' ";
									
									if($x_value['nullable'] == 1)
										$html .= "required ";						
		
									if($do_update)
										{
											$html .= "value='" . $vals->$x . "' ";
										}
				
									$html .= "><br />\n";
								}
							else
								{
									// [MM] can be deleted if the type is hidden
									//$html .= "<label for='$x'>" . t($x_value['label']) . "</label>";
									//$html .= "<input name='" . $x . "' ";
									//$html .= "id='$x' ";
									//if( !is_null($x_value['default']) && !isset($_GET["upd_" . $aForm['table_name']]) )
									//	$html .= "value='" . $x_value['default']. "'";
		              //
									//$html .= "type='hidden' ";
									//$html .= "disabled ";
								}
						}

					//if( $x_value['value'] == null )
				  //	$html .= "value='" . $x_value['default'] . "' ";
					
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
// dbmng_create_form_delete
// ======================
/// This function delete the selected record
/**
\param $aForm  		Associative array with all the characteristics
*/
function dbmng_create_form_delete($aForm) 
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
function dbmng_create_form_duplicate($aForm) 
{
	$sWhat = "";
	foreach ( $aForm['fields'] as $x => $x_value )
		{
			if($x !== $aForm['primary_key'][0])
				$sWhat .= $x . ", ";
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
function dbmng_create_form_insert($aForm) 
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
function dbmng_create_form_update($aForm) 
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
					case "varchar":
						$sVal  .= "'" . $sValue . "', ";
						break;
				
					default:
						$sVal  .= $sValue . ", ";							
				}
		}
  return $sVal;
}


?>
