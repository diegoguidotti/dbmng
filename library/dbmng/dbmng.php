<?php


//use by default the Drupal location for library
if(!defined( 'DBMNG_LIB_PATH'))
	{
		define( 'DBMNG_LIB_PATH', 'sites/all/libraries/dbmng/' ); 
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
		return "0.0.3";
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
		if( gettype($id_table) != "integer" && gettype($id_table) != "double" )
			{
				// TODO: manage if the table is not insert in the dbmng_tables
				$tbln = dbmng_query("select id_table from dbmng_tables where table_name=:table_name", array(':table_name' => $id_table));
				foreach($tbln as $t)
					{
						$id_table = $t->id_table;
					}
			}
		
		$table = dbmng_query("select * from dbmng_tables where id_table=:id_table", array(':id_table' => intval($id_table)));
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
				if($fld->pk == 1 || $fld->pk == 2 )
					{
						// $aPK[] = array($fld->pk, $fld->field_name);
						$aPK[] = $fld->field_name;
					}
					//$aForm['primary_key'] = $fld->field_name; 

				$sLabelLong = ( strlen($fld->field_label_long)>0 ? $fld->field_label_long : $fld->field_label );
				$aFields[$fld->field_name] = array('label' => $fld->field_label, 
																					 'type' => $fld->id_field_type, 
																					 'widget' => $fld->field_widget, 
																					 'value' => null, 
																					 'nullable' => $fld->nullable, 
																					 'default' => $fld->default_value,
																					 'is_searchable' => $fld->is_searchable,
																					 'key' => $fld->pk, //MM [26-07-13]
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

						//TODO: review the safety of this query
						$rVoc  = dbmng_query($sql, array());
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
		
		$aForm['fields']=$aFields;
		//print_r($aForm);
		
		return $aForm;
	}


/////////////////////////////////////////////////////////////////////////////
// dbmng_crud
// ======================
/// This function create all the CRUD interface in plain HTML
/**
\param $aForm  		Associative array with all the characteristics of the table
\param $aParam  		Associative array with some custom variable used by the renderer
\return           HTML generated code
*/
function dbmng_crud($aForm, $aParam=null)
{
   //echo($_REQUEST["act"]." ".$view_table." ".$do_update);
	$html  = "";
  $html .= dbmng_create_form_process($aForm, $aParam);

	//show table if there is no act or it's working on update or duplicate
	$view_table = true;
	$do_update = 0; //false;

	if( isset($_REQUEST["act"]) )
		{
			if($_REQUEST["act"]=='ins' || $_REQUEST["act"]=='upd' || $_REQUEST["act"]=='search' || $_REQUEST["act"]=='do_search' )
				{
					$view_table = false;
					if($_REQUEST["act"]=='upd')
						$do_update = 1; //true;
					
					if($_REQUEST["act"]=='search' || $_REQUEST["act"]=='do_search' )
						{
							$do_update = 2;
							$view_table = true;
						}
				}
			else
				{
					$view_table = true;
				}
		}

	//echo($_REQUEST["act"]."|".$view_table."|".$do_update);

	if($view_table)
		{
			if( $do_update == 2 )
				{
					$html .= dbmng_create_form($aForm, $aParam, $do_update);
				}
			$html .= dbmng_create_table($aForm, $aParam);
		}
	else
    $html .= dbmng_create_form($aForm, $aParam, $do_update);

	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// dbmng_get_data
// ======================
/// execute the query returning all the records filtered and ordered according to aForm and aParam
/**
\param $aForm  		Associative array with all the characteristics of the table
\param $aParam  		Associative array with some custom variable used by the renderer
\return           HTML generated code
*/
function dbmng_get_data($aForm, $aParam) 
{
	$var=array();
	// Initialize where clause and hidden variables
	$where = " WHERE 1=1 ";
	if(isset($aParam))
		{
			if( isset($aParam['filters']) )
				{
					foreach ( $aParam['filters'] as $x => $x_value )
						{				
								$where.=" AND $x = :$x ";
								$var = array_merge($var, array(":$x" => $x_value));
						}					
				}
		}

	if(isset($_REQUEST['act']))
		{
			if($_REQUEST['act']=='do_search')
				{
					foreach( $aForm['fields'] as $fld => $fld_value )
						{
							if( $fld_value['is_searchable'] == 1 )
								{
									if(isset($_REQUEST[$fld]))
										{
											if( $_REQUEST[$fld] != '' )
												{
													if( $fld_value['widget'] == 'input' )
														{
															$where.=" AND $fld like :$fld ";														
															$var = array_merge($var, array(":$fld" => '%'.$_REQUEST[$fld].'%'));
														}
													else
														{
															$where.=" AND $fld = :$fld ";														
															$var = array_merge($var, array(":$fld" => $_REQUEST[$fld]));
														}
												}
										}
								}
						}
				}
		}

	$order_by = '';
	if( isset($aParam['tbl_order']) )
		$order_by = 'order by ' . $aParam['tbl_order'];
		
  $sql = 'select * from ' . $aForm['table_name'].' '. $where . ' ' . $order_by;

	$result = dbmng_query($sql, $var);

	return $result;
}


/////////////////////////////////////////////////////////////////////////////
// dbmng_create_table
// ======================
/// This function create a table starting from a structured array
/**
\param $aForm  		Associative array with all the characteristics of the table
\param $aParam  	Associative array with some custom variable used by the renderer
\return           HTML generated code
*/
function dbmng_create_table($aForm, $aParam)
{
	$html = "";
	//execute the query returning all the records filtered and ordered according to aForm and aParam
	$result= dbmng_get_data($aForm, $aParam);			  

	$html  .= "<div class='dbmng_table' id='dbmng_".$aForm['table_name']."'>";

	if(isset($aForm['table_label']))
		{
		  $tblLbl = (!is_null($aForm['table_label']) ? t($aForm['table_label']) : $aForm['table_name']);
			$html  .= "<h1 class='dbmng_table_label'>" . $tblLbl . "</h1>\n";
		}
	
	$html .= layout_table( $result, $aForm, $aParam );

	$html  .= "<div class='dbmng_record_number'>" . t("Record number") . ": " . dbmng_num_rows($result) . " " . t("recs") . "</div>\n";
	$html  .= '</div>';
	
	return $html;
}

/////////////////////////////////////////////////////////////////////////////
// dbmng_crud_js
// ======================
/// This function create all the CRUD interface javascript generated
/**
\param $aForm  		Associative array with all the characteristics of the table
\param $aParam  	Associative array with some custom variable used by the renderer
\return           HTML generated code
*/
function dbmng_crud_js($aForm, $aParam)
{
	$html  = "";
  $html .= dbmng_create_form_process($aForm, $aParam);
	$html .= "<div id='table_container'></div>\n";

	$html .= "\n<script type='text/javascript'>\n";
	$html .= "var data=".dbmng_get_js_array($aForm, $aParam).";\n";
	$html .= "var aForm=".json_encode($aForm).";\n";
	$html .= "var aParam=".json_encode($aParam).";\n";
	$html .= "jQuery('#table_container').html(dbmng_create_table(data, aForm, aParam));\n";
	$html .= "</script>\n";
	return $html;
}



/////////////////////////////////////////////////////////////////////////////
// dbmng_get_js_array
// ======================
/// This function allow to define a JS array 
/**
\param $aForm  		Associative array with all the characteristics of the table
\param $aParam  	Associative array with some custom variable used by the renderer
\return           html
*/
function dbmng_get_js_array($aForm, $aParam)
{
		
	$html = "";

	$result = dbmng_get_data($aForm, $aParam);			
	$html .= "{'records':[\n";
		
	$sObj  = "";
	foreach( $result as $record )
		{
			$sObj .= "{";
			//get the query results for each field
			foreach ( $aForm['fields'] as $fld => $fld_value )
				{
					//Probably it is better to use the raw data instead of the formatted ones
					//$value=dbmng_value_prepare_html($fld_value, $record->$fld);
					$value=$record->$fld;

					//important! use json_encode to escape special characters
					$sObj .= "'" . $fld . "': " . json_encode($value) . ", ";
						
				}
			$sObj = substr($sObj, 0, strlen($sObj)-2);
			$sObj .= "}, ";
		}
	$sObj = substr($sObj, 0, strlen($sObj)-2);
	
	$html .= $sObj . "\n]}\n";
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
function dbmng_create_form($aForm, $aParam, $do_update) 
{
	$html      = "";
	
	//create the $val array storing all the record data
  if( $do_update == 1 )
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
			
			if($aForm['table_name']==$_REQUEST['tbln'])
				{
						$where = substr($where, 0, strlen($where)-4);			
	
						$result = dbmng_query("select * FROM " . $aForm['table_name'] . " WHERE $where ", $var);
						$vals   = dbmng_fetch_object($result); //$result->fetchObject();
				}
				//print_r($vals);
		}
						
	//if exists at least 1 file widget add enctype to form
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

	//add the hidden fields to the form
	$hv =  prepare_hidden_var_form($aParam);

	//render the form
	$class = "dbmng_form";
	if( $do_update == 2 )
		$class .= "_search";
			
	$html .= "<div class='$class' id='dbmng_form_".$aForm['table_name']."' >\n<form method='POST' $more action='?' >\n".$hv."";
	foreach ( $aForm['fields'] as $fld => $fld_value )
		{
			//render the form field
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
					$value= null;
					if($do_update == 1)
						{
							if(isset($vals->$fld))
								$value = $vals->$fld;
						}
					elseif( $do_update == 2 )
						{
							if(isset($_REQUEST[$fld]))
								{
									$value = $_REQUEST[$fld];
								}
							//print_r($_REQUEST);
					 		//$value = 
						}
					elseif( isset($fld_value['default']) && !is_null($fld_value['default'])  )
						{
							$value = $fld_value['default'];
						}
					// if( $aForm['primary_key'][0] != $fld ) // $aForm['primary_key'][0][0] != 1 && $aForm['primary_key'][0][1] != $fld
					if( !dbmng_check_is_autopk($fld_value) ) // 1 means: Auto-increment primary key (must be removed from the form.
						{										
							$widget='input';
							if(isset($fld_value['widget']))
								$widget=$fld_value['widget'];

							//$is_searchable = true;

							$is_searchable = false;
							if(isset($fld_value['is_searchable']))
								{
									if( $fld_value['is_searchable']==1 )
										$is_searchable = true;
								}
															
							// Do not show input or seletc field for PK
							if($do_update == 1 && dbmng_check_is_pk($fld_value))
								{
									$html .= dbmng_value_prepare_html( $fld_value, $value );
									$html .= layout_form_hidden( $fld, $value );
								}
							else
								{
									if( $_REQUEST['act'] == 'ins' || $_REQUEST['act'] == 'upd' || $is_searchable )
										{
											$html.='<div class="dbmng_form_row dbmng_form_field_'.$fld.'">';
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
											else if ($widget==='password')
												{
													$html .= layout_form_password( $fld, $fld_value, $value );
												}
											else if ($widget==='multi')
												{
													$html .= layout_form_multi( $fld, $fld_value, $value );
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
					else
						{
							$html .= "<input type='hidden' name='$fld' id='$fld' value='$value' />\n";
						}
				} //End of fields
		} //End of form

	if( $do_update == 1 )
		{
			$html .= "<input type='hidden' name='act' value='do_upd' />\n";
			$html .= "<input type='hidden' name='tbln' value='" . $aForm['table_name'] . "' />\n";
			$html .= "<div class='dbmng_form_button'><input  type='submit' value='". t('Update') ."' /></div>\n";
		}
	elseif( $do_update == 0 )
		{
			$html .= "<input type='hidden' name='act' value='do_ins' />\n";
			$html .= "<input type='hidden' name='tbln' value='" . $aForm['table_name'] . "' />\n";
			$html .= "<div class='dbmng_form_button'><input class='dbmng_form_button' type='submit' value='" . t('Insert') . "' /></div>\n";
		}
	elseif( $do_update == 2 )
		{
			$html .= "<input type='hidden' name='act' value='do_search' />\n";
			$html .= "<input type='hidden' name='tbln' value='" . $aForm['table_name'] . "' />\n";
			$html .= "<div class='dbmng_form_button'><input class='dbmng_form_button' type='submit' value='" . t('Search') . "' /></div>\n";
			//$html .= "<div class='dbmng_form_button'><input class='dbmng_form_button' type='reset' value='" . t('Reset') . "' /></div>\n";
		}

  $html .= "</form>\n";
  $html .= "</div>\n";

	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// dbmng_is_field_type_numeric
// ======================
/// This function return true if a type is numeric one
/**
\param $sType  		type of data: int, bigint, float, double
\return           boolean
*/
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
	if(isset($post[$x]))
		{
		  $sValue=$post[$x];
		}
	
	$widget='input';
	if(isset($x_value['widget']))
		{
			$widget=$x_value['widget'];
		}
	
  if($widget=='checkbox')
	  {
	    if(is_null($sValue))
				$sValue=0;
			else
				$sValue=1;
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

	$sVal=null;
	$sType=$x_value['type'];

	$sDefault=null;
	if(isset($x_value['default']))
		{
			$sDefault=$x_value['default'];
		}

	//echo($sType.'|'.$sValue.'|'.is_null($x_value['default']).'|<br/>');
	//Fix: date widget cam not have a default empty value
	if($widget=='date' && $sDefault=='')
		{
			$sDefault=null;
		}

	//if exists a default value use the default values instead of null
	if( strlen($sValue)==0 && is_null($sDefault) )
		{
			$sVal  = null;
		}
	else
		{
			if(strlen($sValue)==0)
				{
					$sValue=$sDefault;
				}

				if (dbmng_is_field_type_numeric($sType)) 
					{
						if($sType=="int" || $sType=="bigint")
							$sVal  = intval($sValue);
						else if($sType=="float" || $sType=="double")
							$sVal  = doubleval($sValue);
						else 
							$sVal  = doubleval($sValue);							
					}
				else 
					{
						$sVal  = $sValue;
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
function dbmng_is_picture($fn)
{
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


/////////////////////////////////////////////////////////////////////////////
// dbmng_file_create_link
// ======================
/// This function allow to create an hypertext link with the uploaded file
/**
\param 		$value  value
\return           html
*/
function dbmng_file_create_link($value)
{
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
function dbmng_value_prepare_html($fld_value, $value)
{
	$ret=null;

	$widget='input';
	if(isset($fld_value['widget']))
		{
			$widget=$fld_value['widget'];
		}	

	if( $widget == "select" )
		{
			$aVoc = array();
			$aVoc = $fld_value['voc_val'];
			if(isset($aVoc[$value]))
				{
					$ret = $aVoc[$value];
				}
			else
				{
					$ret = null;
				}
		}
	elseif($widget == "file" )
		{
			$ret=dbmng_file_create_link($value);
		}
	elseif($widget == "password" )
		{
			$ret="****";
		}
  elseif($widget == "date" )
		{
			if(!is_null($value) && $value!='')
				{
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


//add the js e css for drupal
function dbmng_add_drupal_libraries()
	{
		drupal_add_css( "sites/all/modules/dbmng_module/dbmng_module.css" );
		drupal_add_css( "sites/all/libraries/dbmng/assets/dbmng.css" );
		drupal_add_js ( "sites/all/libraries/dbmng/assets/dbmng.js" );
		drupal_add_js ( "sites/all/libraries/dbmng/assets/jquery.tablesorter.js" );
		drupal_add_library('system','ui.datepicker');
	}



?>
