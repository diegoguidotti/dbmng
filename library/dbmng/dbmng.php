<?php
include_once "sites/all/library/dbmng/dbmng_extend_functions.php";
include_once "sites/all/library/dbmng/dbmng_crud.php";
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
// dbmng_crud
// ======================
/// This function create all the CRUD interface
/**
\param $aForm  		Associative array with all the characteristics of the table
\param $aParam  		Associative array with some custom variable used by the renderer
\return           HTML generated code
*/
function dbmng_crud($aForm, $aParam){
			$html  = "";
      $html .= dbmng_create_form_process($aForm, $aParam);
			$html .= dbmng_create_table($aForm, $aParam);
      $html .= dbmng_create_form($aForm, $aParam);
			return $html;
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

				$html  .= "<div class='dbmng_table' id='dbmng_".$aForm['table_name']."'>";

				
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


			$html .= "<div class='dbmng_form' id='dbmng_form_".$aForm['table_name']."' >\n<form method='POST' action='?' >\n".$hv."";
			foreach ( $aForm['fields'] as $fld => $fld_value )
				{

					$custom_function_exists=false;
					if( isset($fld_value['field_function']) )
						{
							if( function_exists($fld_value['field_function']) ) 
								{
									$html .= call_user_func($fld_value['field_function']);
									$custom_function_exists=true;
								}
						}


				if(!$custom_function_exists)
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
									
									$html.='<div class="dbmng_form_row dbmng_form_field_'.$fld.'">';


									if ($fld_value['widget']=='textarea')
									{
										$html .= layout_form_textarea( $fld, $fld_value, $value );
									}
									else if ($fld_value['widget']=='checkbox')
									{
										$html .= layout_form_checkbox( $fld, $fld_value, $value );
									}
									else if ($fld_value['widget']=='select')
									{
										$html .= layout_form_select( $fld, $fld_value, $value );
									}
									else //use input by default
									{
										$html .= layout_form_input( $fld, $fld_value, $value );		
									}
									$html.='</div>';

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
	    $html .= "</div>\n";
		}
		return $html;
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
function dbmng_value_prepare($x_value, $x, $post)
{

  $sValue=null;
	if(isset($post[$x])){
	  $sValue=$post[$x];
	}
	
  if($x_value['widget']=='checkbox'){
    if(is_null($sValue))
			$sValue="0";
		else
			$sValue="1";
	}

	$sVal='';
	$sType=$x_value['type'];

	echo($sType.'|'.$sValue.'|'.is_null($x_value['default']).'|<br/>');

	//if exists a default value use the default values instead of null
	if(strlen($sValue)==0 && is_null($x_value['default']) )
		{
			$sVal  .= "NULL";
		}
	else
		{
			if(strlen($sValue)==0)
				{
					$sValue=$x_value['default'];
				}

				if ($sType=="int" || $sType=="bigint" || $sType=="float"  || $sType=="double") {
					$sVal  .= $sValue;							
				}
				else {
					$sVal  .= "'" . $sValue . "'";
				}
		}
  return $sVal;
}

/////////////////////////////////////////////////////////////////////////////
// dbmng_value_prepare_html
// ======================
/// This function prepare the value obtained by the query to be visualized in the html
/**
\param $field_value  		The associative array with the field meta-variables
\param $value  		      The value obtained by the request
\return             Value
*/
function dbmng_value_prepare_html($fld_value, $value){

		$ret=null;
		if( $fld_value['widget'] == "select" )
			{
				$aVoc = array();
				$aVoc = $fld_value['voc_val'];
				if(isset($aVoc[$value])){
					$ret = $aVoc[$value];
				}
				else{
					$ret = null;
				}
			}
		elseif( $fld_value['widget'] == "checkbox" )
			{
				if($value=="1")
					{
						$ret = '<span class="dbmng_check_true">'.t('true').'<span>';
					}
				elseif($value=="0")
					{
						$ret = '<span class="dbmng_check_false">'.t('false').'<span>';
					}
				else
					{
						$ret = "";
					}
			}
		else
		{
				$ret=$value;
		}

		return $ret;
}


?>
