<?php


//use by default the Drupal location for library
if(!defined( 'DBMNG_LIB_PATH'))
	{
		define( 'DBMNG_LIB_PATH', 'sites/all/library/dbmng/' ); 
	}


include_once DBMNG_LIB_PATH."dbmng_extend_functions.php";
include_once DBMNG_LIB_PATH."dbmng_crud.php";
include_once DBMNG_LIB_PATH."layout.php";
include_once DBMNG_LIB_PATH."dbmng_sql_functions.php";



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

		$table             = dbmng_query("select * from dbmng_tables where id_table=".$id_table);
		//print_r( $table );
		$aForm['id_table'] = $id_table;
		$fo                = dbmng_fetch_object($table);
		$aForm['table_name']  = $fo->table_name;
		$aForm['table_label'] = $fo->table_label;

		//TODO: ['primary key shoud be an array to manage multiples key']
		$aFields = array();
		$aPK     = array(); // Array to store information about the primary key
		$fields  = dbmng_query("select * from dbmng_fields where id_table=:id_table order by field_order ASC", array(':id_table'=>intval($id_table)));
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

						$rVoc  = dbmng_query($sql);
						$aFVoc = array();

						$v       = 0;
						foreach($rVoc as $val)
							{
								$keys=array_keys((array)$val);
								$aFVoc[$val->$keys[0]] = $val->$keys[1];
							}
						
						/*
						$sortAux = array();
						foreach($aFVoc as $res)
							$sortAux[] = $res[0];
						
						//array_multisort($sortAux, SORT_ASC, $aFVoc);
						*/
						
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
			$html .= dbmng_data2JSarray($aForm, $aParam);
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
				$result = dbmng_query($sql);
			  
				$html  .= "<div class='dbmng_table' id='dbmng_".$aForm['table_name']."'>";

				if(isset($aForm['table_label']))
					{
					  $tblLbl = (!is_null($aForm['table_label']) ? t($aForm['table_label']) : $aForm['table_name']);
						$html  .= "<h1 class='dbmng_table_label'>" . $tblLbl . "</h1>\n";
					}
				
				$html .= layout_table( $result, $aForm, $aParam );

				$html  .= "<div class='dbmng_record_number'>" . t("Record number") . ": " . dbmng_num_rows($result) . " " . t("recs") . "</div>\n";
				$html  .= '</div>';

			}
			return $html;
	}


/////////////////////////////////////////////////////////////////////////////
// dbmng_data2JSarray
// ======================
/// This function allow to define a JS array 
/**
\param $aForm  		Associative array with all the characteristics of the table
\param $aParam  	Associative array with some custom variable used by the renderer
\return           html
*/
function dbmng_data2JSarray($aForm, $aParam)
{
	if( !isset($_GET["upd_" . $aForm['table_name']]) &&  !isset($_GET["ins_" . $aForm['table_name']]) )
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
					$result = dbmng_query($sql);
					
					$AName = "_aTblVal";
					$html .= "\n<script type='text/javascript'>\n";
					$html .= "<!--\n";
					$html .= "// $sql\n";
					$html .= "$AName = {'records':[\n";
					
					$sObj  = "";
					foreach( $result as $record )
						{
							$sObj .= "{";
							//get the query results for each field
							foreach ( $aForm['fields'] as $fld => $fld_value )
								{
									$value=dbmng_value_prepare_html($fld_value, $record->$fld);
									if( layout_view_field_table($fld_value) )
										{
											$sObj .= "'" . $fld . "':" . $value . ", ";
										}
				
								}
							$sObj = substr($sObj, 0, strlen($sObj)-2);
							$sObj .= "}, ";
						}
					$sObj = substr($sObj, 0, strlen($sObj)-2);
					
					$html .= $sObj . "\n]};\n";
				}
				$html .= "//-->\n";
				$html .= "</script>\n";
				return $html;
		}
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
					$result    = dbmng_query($sql );		
					$vals      = dbmng_fetch_object($result); //$result->fetchObject();
					//print_r($vals);
				}
				
			$more = "";
			foreach ( $aForm['fields'] as $fld => $fld_value )
				{
					if( isset($fld_value['widget']) )
						{
							if( $fld_value['widget'] == 'file' )
								{
									$more = "enctype='multipart/form-data'";
								}
						}
				}


			$html .= "<div class='dbmng_form' id='dbmng_form_".$aForm['table_name']."' >\n<form method='POST' $more action='?' >\n".$hv."";
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
									elseif( isset($fld_value['default']) && !is_null($fld_value['default'])  )
										{
											$value = $fld_value['default'];
										}
									
									$html.='<div class="dbmng_form_row dbmng_form_field_'.$fld.'">';
									
									$widget='input';
									if(isset($fld_value['widget']))
										$widget=$fld_value['widget'];

									//generate the form label
									$html .= layout_get_label($fld, $fld_value);
									$html.='<div class="dbmbg_form_element">';

									if ($widget==='textarea')
									{
										$html .= layout_form_textarea( $fld, $fld_value, $value );
									}
									else if ($widget==='checkbox')
									{
										$html .= layout_form_checkbox( $fld, $fld_value, $value );
									}
									else if ($widget==='select')
									{
										$html .= layout_form_select( $fld, $fld_value, $value );
									}
									else if ($widget==='date')
									{
										$html .= layout_form_date( $fld, $fld_value, $value );
									}
									else if ($widget==='file')
									{
										$html .= layout_form_file( $fld, $fld_value, $value );
									}
									else //use input by default
									{
                    $more='';
										if(dbmng_is_field_type_numeric($fld_value['type']))
											{
												$more="onkeypress=\"dbmng_validate_numeric(event)\"";		
											}   
										$html .= layout_form_input( $fld, $fld_value, $value, $more );		
									}
									$html.='</div>';
									$html.='</div>';

								}
						}
				}

			if( isset($_GET["upd_" . $aForm['table_name']] ))
				{
					$html .= "<input type='hidden' name='upd_" . $aForm['table_name'] . "' value='" . $_GET["upd_" . $aForm['table_name']] . "' />\n";
					$html .= "<div class='dbmng_form_button'><input  type='submit' value='". t('Update') ."' /></div>\n";
				}
			else
				{
					$html .= "<input type='hidden' name='ins_" . $aForm['table_name'] . "' />\n";
					$html .= "<div class='dbmng_form_button'><input class='dbmng_form_button' type='submit' value='" . t('Insert') . "' /></div>\n";
				}

	    $html .= "</form>\n";
	    $html .= "</div>\n";
		}
		return $html;
}


function dbmng_is_field_type_numeric($sType)
	{
   	return ($sType=="int" || $sType=="bigint" || $sType=="float"  || $sType=="double");
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
	
	$widget='input';
	if(isset($x_value['widget']))
		{
			$widget=$x_value['widget'];
		}
	
  if($widget=='checkbox'){
    if(is_null($sValue))
			$sValue="0";
		else
			$sValue="1";
	}
	
	if( $widget=='file' )
		{
			$dir_upd_file = "docs/";
			if( isset($aParam['file']) )
				$dir_upd_file = $aParam['file'];
				
			$sValue = $dir_upd_file . $_FILES[$x]['name'];

			if( $_FILES[$x]["error"] == 0 )
				{
					$sValue = dbmng_uploadfile($_FILES[$x]['name'], $dir_upd_file, $_FILES[$x]["tmp_name"]);
			  	// move_uploaded_file($_FILES[$x]["tmp_name"], $dir_upd_file . $_FILES[$x]["name"]);
			  }
			else if ($_FILES[$x]["error"] == 4)
				{ //if the file is null use the text in the checkbox
					$sValue = $post[$x.'_tmp_choosebox'];
				}		
		}

	$sVal='';
	$sType=$x_value['type'];

	$sDefault=null;
	if(isset($x_value['default'])){
		$sDefault=$x_value['default'];
	}

	//echo($sType.'|'.$sValue.'|'.is_null($x_value['default']).'|<br/>');
	//Fix: date widget cam not have a default empty value
	if($widget=='date' && $sDefault==''){
		$sDefault=null;
	}

	//if exists a default value use the default values instead of null
	if(strlen($sValue)==0 && is_null($sDefault) )
		{
			$sVal  .= "NULL";
		}
	else
		{
			if(strlen($sValue)==0)
				{
					$sValue=$sDefault;
				}

				if (dbmng_is_field_type_numeric($sType)) {
					$sVal  .= $sValue;							
				}
				else {
					$sVal  .= "'" . $sValue . "'";
				}
		}
  return $sVal;
}


/////////////////////////////////////////////////////////////////////////////
// dbmng_uploadfile
// ======================
/// This function allow to rename a file if exist adding timestamp
/**
\param $origin  			the filename
\param $dest    			the destination directory
\param $tmp_name 			the temporary filename
\return           		the new path
*/
function dbmng_uploadfile($origin, $dest, $tmp_name)
	{
		$fullpath = $dest . $origin;
	  if (file_exists( $fullpath ))
			{
				$additional = time();
				$info = pathinfo($fullpath);
				$fullpath = $info['dirname'] . '/' . $info['filename'] . '_' . $additional . '.' . $info['extension'];
			}
			
		move_uploaded_file($tmp_name, $fullpath);
		return $fullpath;
	}


/////////////////////////////////////////////////////////////////////////////
// dbmng_is_picture
// ======================
/// This function allow to know if a file is a picture
/**
\param $fn  			the filename
\return           boolean
*/
function dbmng_is_picture($fn){
	$is_picture = false;
	
	$allowedExts = array("gif", "jpeg", "jpg", "png");
	$temp = explode(".", $_FILES[$fn]["name"]);
	$extension = end($temp);
	if ((($_FILES[$fn]["type"] == "image/gif")
	|| ($_FILES[$fn]["type"] == "image/jpeg")
	|| ($_FILES[$fn]["type"] == "image/jpg")
	|| ($_FILES[$fn]["type"] == "image/pjpeg")
	|| ($_FILES[$fn]["type"] == "image/x-png")
	|| ($_FILES[$fn]["type"] == "image/png"))
	&& in_array($extension, $allowedExts))
		{
			$is_picture = false;
		}
	return $is_picture;
}


function dbmng_file_create_link($value){

  $ret="";
	//echo realpath('.').'/'.$value;
	if(!is_null($value) && $value!='')
		{
			$link= base_path() . ''. $value;

			//if(in_array( substr(strrchr($value, '.'), 1), $allowedExts ))
			if( preg_match('/\.(gif|jpe?g|png)$/i',strtolower($value)) )
				{					
					$ret="<a class='dbmng_image_link' target='_NEW' href='".$link."'><img class='dbmng_image_thumb' src='".$link."' /></a>\n";					
				}
			else
				{
					$ret="<a class='dbmng_file_link' target='_NEW' href='". base_path() . "" . $value."'>".t("download")."</a>\n";					
				}
			$ret .= "&nbsp;";			
		}
	return $ret;
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

		$widget='input';
		if(isset($fld_value['widget'])){
			$widget=$fld_value['widget'];
		}		
		

		if( $widget == "select" )
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
		elseif($widget == "file" )
			{
				$ret=dbmng_file_create_link($value);
			}
    elseif($widget == "date" )
			{
				if(!is_null($value) && $value!=''){
					$datetime = DateTime::createFromFormat('Y-m-d', $value);
					$ret = $datetime->format('d-m-Y');
				}
			}
		elseif($widget == "checkbox" )
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
