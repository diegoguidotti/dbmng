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
				'primary_key' => 'id_test',
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


function getVersion(){
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
function dbmng_get_form_array($id_table){
		$aForm = array();

		$table = db_query("select * from dbmng_tables where id_table=".$id_table);
		$aForm['id_table']    = $id_table; // $table->fetchObject()->id_table;
		$fo = $table->fetchObject();
		$aForm['table_name']  = $fo->table_name;
		$aForm['table_label'] = $fo->table_label;

		//TODO: ['primary key shoud be an array to manage multiples key']
		$aFields=array();
		$fields = db_query("select * from dbmng_fields where id_table=".$id_table." order by field_order ASC");
		foreach ($fields as $fld)
		{
			if($fld->pk == 1)
				$aForm['primary_key'] = $fld->field_name; 
				//if(strpos($fld->field_name, "id_") !== false )
				//	$aForm['primary_key'] = "id_test";

			
			

			$aFields[$fld->field_name] = array('label' => $fld->field_label, 
																				 'type' => $fld->id_field_type, 
																				 'value' => null, 
																				 'nullable' => $fld->nullable, 
																				 'field_function' => $fld->field_function);
			if(($fld->default_value)!=null){
					$aFields[$fld->field_name]['default']=$fld->default_value;
			}
		}


		if(!array_key_exists('primary_key', $aForm)){
					$aForm['primary_key']='id_'.$aForm['table_name'];	
		}
		$aForm['fields']=$aFields;
		print_r($aFields);
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
function dbmng_create_table($aForm, $aParam){
	  $sql = 'select * from ' . $aForm['table_name'];
		$result = db_query($sql);
    
		$html = "<h1>" . $aForm['table_name'] . "</h1>\n";

		//get some hidden variables if exists()
		$hv='';
		if(isset($aParam)){
			if(isset($aParam['hidden_vars'])){
				foreach ( $aParam['hidden_vars'] as $x => $x_value ){				
					$hv.= ('&amp;'.$x.'='.$x_value);
				}
			}
		}

		
		$html .= "<table>";
		$html .= "<tr>";
		foreach ( $aForm['fields'] as $x => $x_value )
		{
			$html .= "<th>" . $x_value['label'] . "</th>";
		}
		$html .= "<th>" . t('functions') . "</th></tr>\n";
		
		foreach ($result as $record) {
			// table value
			$html .= "<tr>";
			
			//get the query results for each field
			foreach ( $aForm['fields'] as $x => $x_value )
			{
				$html.= "<td>".$record->$x."</td>";
			}
			
			// available functionalities
			$html .= "<td>";
				$html .= "<a href='?del_" . $aForm['table_name'] . "=" . $record->$aForm['primary_key'] .$hv."'>" . t('Delete') . "</a>" . "&nbsp;";
				$html .= "<a href='?upd_" . $aForm['table_name'] . "=" . $record->$aForm['primary_key'] .$hv."'>" . t('Update') . "</a>" . "&nbsp;";
				$html .= "<a href='?dup_" . $aForm['table_name'] . "=" . $record->$aForm['primary_key'] .$hv."'>" . t('Duplicate') . "</a>" . "&nbsp;";
			$html .= "</td>\n";
			
			$html .= "</tr>";
		}
    $html .= "</table>\n";
		
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
	$html="";
	$do_update=false;
   //get some hidden variables if exists()
		$hv='';
		if(isset($aParam)){
			if(isset($aParam['hidden_vars'])){
				foreach ( $aParam['hidden_vars'] as $x => $x_value ){				
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

					$sql       = "select * from " . $aForm['table_name'] . " where " . $aForm['primary_key'] . "=" . intval($id_upd);
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
							if( $aForm['primary_key'] != $x )
								{
									$html .= "<label for='$x'>" . t($x_value['label']) . "</label>";
									$html .= "<input name='" . $x . "' ";
									$html .= "id='$x' ";
									
									if( isset($x_value['default']) && !isset($_GET["upd_" . $aForm['table_name']]) )
										$html .= "value='" . $x_value['default']. "'";
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
			$result = db_query("delete from " . $aForm['table_name'] . " WHERE " . $aForm['primary_key'] . " = " . $id_del);
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
			if($x !== $aForm['primary_key'])
				$sWhat .= $x . ", ";
		}
	$sWhat = substr($sWhat, 0, strlen($sWhat)-2);
	
	if(isset($_REQUEST["dup_" . $aForm['table_name']]))
		{
			$id_dup = intval($_REQUEST["dup_" . $aForm['table_name']]);
			$result = db_query("insert into " . $aForm['table_name'] . " (" . $sWhat . ") select " . $sWhat . " from " . $aForm['table_name'] . " where " . $aForm['primary_key'] . " = " . $id_dup);
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
					if($x !== $aForm['primary_key'])
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
					if($x !== $aForm['primary_key'])
						{
							$sSet .= $x . " = ";

							$sSet.=dbmng_value_prepare($x_value,$_POST[$x]);
						}
				}
		
			$sSet = substr($sSet, 0, strlen($sSet)-2);
	
			$id_upd = intval($_REQUEST["upd_" . $aForm['table_name']]);
			$sql    = "update " . $aForm['table_name'] . " set " . $sSet . " where " . $aForm['primary_key'] . " = " . $id_upd;
			$result = db_query($sql);
		}
}

function dbmng_value_prepare($x_value, $sValue){
	$sVal='';
	$sType=$x_value['type'];

	$df=null;
	if(isset($x_value['default'])){
		$df=$x_value['default'];
	}

	echo($sType.'|'.$sValue.'|'.($df==null).'|<br/>');
	if(strlen($sValue)==0 && $df==null)
	{
			$sVal  .= "NULL, ";
	}
	else{
		if(strlen($sValue)==0){
			$sValue=$df;
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
