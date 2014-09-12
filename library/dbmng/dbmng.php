<?php


//use by default the Drupal location for library.
if(!defined( 'DBMNG_LIB_PATH'))
	{
		define( 'DBMNG_LIB_PATH', 'sites/all/libraries/' ); 
	}

include_once DBMNG_LIB_PATH."dbmng/dbmng_cfg.php";
include_once DBMNG_LIB_PATH."dbmng/dbmng_extend_functions.php";
include_once DBMNG_LIB_PATH."dbmng/dbmng_crud.php";
include_once DBMNG_LIB_PATH."dbmng/layout.php";
include_once DBMNG_LIB_PATH."dbmng/dbmng_sql_functions.php";
include_once DBMNG_LIB_PATH."dbmng/dbmng_sql_functions_obj.php";
include_once DBMNG_LIB_PATH."dbmng/dbmng_resize_functions.php";

if(file_exists(DBMNG_LIB_PATH.'fpdf/fpdf.php')){
	include_once DBMNG_LIB_PATH."dbmng/dbmng_pdf.php";
}


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
		
		$aForm['id_table'] = $id_table;
		//if()
		if(dbmng_num_rows($table)>0){
			$fo                = dbmng_fetch_object($table);
			$aForm['table_name']  = $fo->table_name;
			$aForm['table_label'] = $fo->table_label;
		}
		

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
				$isSearcheable = ( isset($fld->is_searchable) ? ($fld->is_searchable) : "0" );
				$sWidget = ( isset($fld->field_widget) ? $fld->field_widget : "input" );

				$aArray=array('label' => $fld->field_label, 
					 'type' => $fld->id_field_type, 
					 'widget' => $sWidget, 
					 'value' => null, 
					 'nullable' => $fld->nullable, 
					 'readonly' => $fld->readonly,
					 'default' => $fld->default_value,
					 'is_searchable' => $isSearcheable,
					 'key' => $fld->pk, //MM [26-07-13]
					 'field_function' => $fld->field_function,
					 'label_long' => $sLabelLong,
					 'skip_in_tbl' => $fld->skip_in_tbl,
					 'voc_sql' => $fld->voc_sql );
				
				if(isset($fld->param))
					{
						$param = $fld->param;
						$param = str_replace("'",'"',$param);
						
						//echo $param;

						$js = json_decode($param);
						if(isset($js)){
							foreach($js as $key => $val){
								$aArray[$key]=$val;
							}
						}
					}

				$aFields[$fld->field_name] = $aArray;
				
				if( $fld->field_widget == 'select' || $fld->field_widget == 'select_nm' )
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
				
				if( $fld->field_widget == 'multiselect')
					{
						// sql written in dbmng_fields
						$sql  = $fld->voc_sql;							

						//TODO: review the safety of this query
						$rVoc  = dbmng_query($sql, array());
						$aFVoc = array();

						foreach($rVoc as $val)
							{
								$keys=array_keys((array)$val);
								if( !isset($aFVoc[$val->$keys[0]]) )
									$aFVoc[$val->$keys[0]] = array("key" => $val->$keys[0], "value" => $val->$keys[1]);
								if( !isset($aFVoc[$val->$keys[0]]["vals"][$val->$keys[2]]) )
									$aFVoc[$val->$keys[0]]["vals"][$val->$keys[2]] = array("key" => $val->$keys[2], "value" => $val->$keys[3]);
								if( !isset($aFVoc[$val->$keys[0]]["vals"][$val->$keys[2]]["vals"][$val->$keys[4]]) ) 
									$aFVoc[$val->$keys[0]]["vals"][$val->$keys[2]]["vals"][$val->$keys[4]] = array("key" => $val->$keys[4], "value" => $val->$keys[5]);
							}

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
	//echo '<pre>';
	//print_r($_POST);
	//echo '</pre>';
	$ret = dbmng_check_aForm($aForm, $aParam);

	$html='';


	if($ret['ok']){

		//echo($_REQUEST["act"]." ".$view_table." ".$do_update);
		$html  = "";
		$ret_form_process = dbmng_create_form_process($aForm, $aParam);

		//show table if there is no act or it's working on update or duplicate
		$view_table = true;
		$do_update = 0; //false;

		if( isset($_REQUEST["act"]) )
			{
				if($_REQUEST["act"]=='ins' || $_REQUEST["act"]=='upd')
					{
						$view_table = false;
						if($_REQUEST["act"]=='upd')
							$do_update = 1; //true;
					}
				else
					{
						$view_table = true;
					}
			}


		if(isset($ret_form_process))
			{
				if(!$ret_form_process['ok'])
					{
						$html.='<div class="message error">'.$ret_form_process['error'].'</div>';
						if($_REQUEST["act"]=='do_ins' || $_REQUEST["act"]=='do_upd')
							{
								$view_table=false;
								$do_update = 3;
								//if($_REQUEST["act"]=='upd' || $_REQUEST["act"]=='ins')
								//	$do_update = 3; //true;
							}
					}
		}

		$activate_search=false;

		foreach( $aForm['fields'] as $fld => $fld_value )
			{
				if(isset($fld_value['is_searchable'])){
					if($fld_value['is_searchable'] == 1){
						$activate_search=true;
						break;
					}
				}
			}
		

/*
		if( isset($_REQUEST["act2"]) && !isset($_REQUEST["act"]) )
			{
				//dbmng_create_form_process($aForm, $aParam, "search");
				if( $_REQUEST["act2"]=='do_search' ) //$_REQUEST["act2"]=='search' || 
					{
						$do_update = 2;
						$view_table = true;
					}
				else
					{
						$view_table = true;
					}
			}
*/
	/*
		if( isset($_REQUEST["act"]) )
			echo "<br/>act: ". $_REQUEST["act"];
		if( isset($_REQUEST["act2"]) )
			echo "<br/>act2: ". $_REQUEST["act2"];
	
		echo "<br/>viewtable: ". $view_table;
		echo "<br/>do_update: ". $do_update;
	*/

		if($view_table)
			{
				if( $activate_search )
					{
						$do_update_search=2;
						$html .= dbmng_create_form($aForm, $aParam, $do_update_search, "search");
					}
					$html .= dbmng_create_table($aForm, $aParam);
			}
		else
			{
				//echo "do_upd: ". $do_update;
				$html .= dbmng_create_form($aForm, $aParam, $do_update);
			}

	}
	else{
		$html.='<div class="messages error">'.$ret['err_msg'].'</div>';
	}

	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// dbmng_check_aForm
// ======================
/// This function checks if aForm is valid
/**
\param $aForm  		Associative array with all the characteristics of the table
\param $aParam  		Associative array with some custom variable used by the renderer
\return           HTML generated code
*/
function dbmng_check_aForm($aForm, $aParam)
{
	$ok=false;
	$err_msg='No processed';

	$tn = 0;
	if( isset($aForm['id_table']) )
		$tn=$aForm['id_table'];
		
	//print_r($aForm);

	if( !isset($aForm['table_name']) ){		
		$err_msg='Table '.$tn.' is missing. Check if table exists in dbmng_table';
	}
	else if( $aForm['table_name']=='' ){		
		$err_msg='Table '.$tn.' is missing. Check if table exists in dbmng_table';
	}
	else if( !isset($aForm['fields']) ){		
		$err_msg='There are no fields for '.$tn.'. Check dbmng_fields table';
	}
	else if( count($aForm['fields'])==0 ){		
		$err_msg='There are no fields for '.$tn.'. Check dbmng_fields table';
	}
	else if( !isset($aForm['primary_key']) ){		
		$err_msg='There are no primary_keys for '.$tn.'. Check dbmng_fields table';
	}
	else if( count($aForm['primary_key'])==0 ){		
		$err_msg='There are no primary_keys for '.$tn.'. Check dbmng_fields table';
	}
	else{
		$ok=true;
	}

	$ret=Array();
	$ret['ok']=$ok;
	$ret['err_msg']=$err_msg;

	return $ret;

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

	//print_r ($_POST);

	if(isset($_REQUEST['act2']))
		{
			if($_REQUEST['act2']=='do_search')
				{
					foreach( $aForm['fields'] as $fld => $fld_value )
						{
							if( $fld_value['is_searchable'] == 1 )
								{
									if(isset($_REQUEST["search_".$fld]))
										{
											if( $_REQUEST["search_".$fld] != '' )
												{
													if( $fld_value['widget'] == 'input' )
														{
															$where.=" AND $fld like :$fld ";														
															$var = array_merge($var, array(":$fld" => '%'.$_REQUEST["search_".$fld].'%'));
														}
													else
														{
															$where.=" AND $fld = :$fld ";														
															$var = array_merge($var, array(":$fld" => $_REQUEST["search_".$fld]));
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
	       
	$limit = "";
	//if( isset($aParam['tbl_navigation']) )
	//	$limit = 'limit 0, ' . $aParam['tbl_navigation'];
	
	//print_r ($aForm);

	if( isset($aForm['table_view']) )
	  $sql = 'select * from ' . $aForm['table_view'].' '. $where . ' ' . $order_by . ' ' . $limit;
	else
	  $sql = 'select * from ' . $aForm['table_name'].' '. $where . ' ' . $order_by . ' ' . $limit;
	
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
	foreach ( $aForm['fields'] as $fld => $fld_value )
		{
			if( isset($fld_value['widget']) ) 
				{
					if( $fld_value['widget'] == 'select_nm' )
						{
							$table = $fld_value['table_nm'];
							$sql = "select * from $table";
							$result = dbmng_query($sql, array());
							
							$aNM = array();
							foreach( $result as $record )
								{
									$keys=array_keys((array)$record);
									$aNM[$record->$keys[1]][] = $record->$keys[2];
								}
							$aForm['fields'][$fld]['voc_nm'] = $aNM;
						}
				}
		}
	
	//echo "<pre>";
	//print_r($aForm);
	//echo "</pre>";

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
	$html .= "var aForm=".json_encode($aForm).";\n";
	$html .= "var aParam=".json_encode($aParam).";\n";
	
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
	$html .= '{"records":[';
		
	$sObj  = "";
	foreach( $result as $record )
		{
			$sObj .= "{";
			//get the query results for each field
			foreach ( $aForm['fields'] as $fld => $fld_value )
				{					
					$value=$record->$fld;

					//important! use json_encode to escape special characters
					$sObj .= '"' . $fld . '": ' . json_encode($value) . ", ";
						
				}
			$sObj = substr($sObj, 0, strlen($sObj)-2);
			$sObj .= "}, ";
		}
	$sObj = substr($sObj, 0, strlen($sObj)-2);
	
	$html .= $sObj . "] ";

	$html .= ', "aForm":'.json_encode($aForm);

	$html .= "}";
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
function dbmng_create_form($aForm, $aParam, $do_update, $actiontype="") 
{
	$nmvals = Array();
	$html      = "";
	
	$btn_name = t("Insert");
	if( isset($aParam['ui']['btn_name']) )
		{
			$btn_name = t($aParam['ui']['btn_name']);
		}
	
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
			
			$where = substr($where, 0, strlen($where)-4);			

			$result = dbmng_query("select * FROM " . $aForm['table_name'] . " WHERE $where ", $var);
			$vals   = dbmng_fetch_object($result); //$result->fetchObject();

			foreach ( $aForm['fields'] as $fld => $fld_value )
				{
					if( isset($fld_value['widget']) )
						{
							if( $fld_value['widget']=='select_nm' )
								{
									$result = dbmng_query("select ".$fld_value['field_nm']." FROM " . $fld_value['table_nm'] . " WHERE $where ", $var);
									$tx="";
									
									if( $do_update == 1 )
										{
											foreach ($result as $record)
												{							
													$tx.=($record->$fld_value['field_nm']).'|';
												}
										}
									$nmvals[$fld]=$tx;
								}
						}
				}
		}

	//if exists at least 1 file widget add enctype to form
	$more = "";
	foreach ( $aForm['fields'] as $fld => $fld_value )
		{
			if( isset($fld_value['widget']) )
				{
					if( $fld_value['widget'] == 'file' || $fld_value['widget'] == 'picture' )
						{
							$more = "enctype='multipart/form-data'";
						}
				}
		}

	//add the hidden fields to the form
	$hv =  prepare_hidden_var_form($aParam);

	//render the form
	$class = "dbmng_form";
	if( $do_update == 2 && $actiontype=="search")
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
					elseif( $do_update == 2 && $actiontype=="search")
						{
							if(isset($_REQUEST["search_".$fld]))
								{
									$value = $_REQUEST["search_".$fld];
								}
							//print_r($_REQUEST);
					 		//$value = 
						}
					elseif($do_update == 3)
						{
							if(isset($_POST[$fld]))
								{
									$value = $_POST[$fld];
									
									if( isset($fld_value['widget']) ) 
										if( $fld_value['widget'] == 'select_nm' )
											{
												$tx = "";
												foreach( $_POST[$fld] as $v )
													$tx.=$v."|";
	
												$nmvals[$fld]=$tx;
											}
							}
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
									$html .= dbmng_value_prepare_html( $fld_value, $value, $aParam, "form" );
									$html .= layout_form_hidden( $fld, $value );
								}
							else
								{
									$bViewFld = true;
									//if( isset($_REQUEST['act2']) && $_REQUEST['act2'] == "do_search" && !isset($_REQUEST['act']) && !$is_searchable )
									if( $actiontype == "search" && !$is_searchable )
										$bViewFld = false;

									if( $bViewFld ) //$_REQUEST['act'] == 'ins' || $_REQUEST['act'] == 'upd' || $is_searchable )
										{
											$html.='<div class="dbmng_form_row dbmng_form_field_'.$fld.'">&nbsp;';
											if( !isset($aParam['hide_label']) )
												{
													$html .= layout_get_label($fld, $fld_value);
												}
											$html.='<div class="dbmbg_form_element">&nbsp;';

											$aInput = Array();
											$aInput['fld'] = $fld;
											$aInput['fld_value'] = $fld_value;
											$aInput['value'] = $value;
											$aInput['actiontype'] = $actiontype;
											$aInput['aParam'] = $aParam;
											//echo "<pre>";
											//print_r($fld_value);
											//echo "</pre>";

											if ($widget==='textarea')
												{
													$html .= layout_form_textarea( $aInput );
												}
											else if ($widget==='html')
												{
													$html .= layout_form_html( $aInput );
												}
											else if ($widget==='checkbox')
												{
													$html .= layout_form_checkbox( $aInput );
												}
											else if ($widget==='select')
												{
													$html .= layout_form_select( $aInput );
												}
											else if ($widget==='multiselect')
												{
													$html .= layout_form_multiselect( $aInput );
												}
											else if ($widget==='select_nm')
												{
													//during insert there are no record associated; nmvalue is not created we added an empty array
													if(isset($nmvals[$fld]))
														$aInput['value'] = $nmvals[$fld];
													else{
														$aInput['value'] = "";
													}
										
													$html .= layout_form_select_nm( $aInput ); //$fld, $fld_value, $nmvals[$fld] );
												}
											else if ($widget==='date')
												{
													$html .= layout_form_date( $aInput );
												}
											else if ($widget==='file')
												{
													$html .= layout_form_file( $aInput );
												}
											else if ($widget==='picture')
												{
													$html .= layout_form_picture( $aInput );
												}
											else if ($widget==='password')
												{
													$html .= layout_form_password( $aInput );
												}
											else if ($widget==='multi')
												{
													$html .= layout_form_multi( $aInput );
												}
											else //use input by default
												{
													$more='';
													if(dbmng_is_field_type_numeric($fld_value['type']))
														{
															$more="onkeypress=\"dbmng_validate_numeric(event)\"";		
														}  
													$aInput['more'] = $more;
														
													$html .= layout_form_input($aInput);// ( $fld, $fld_value, $value, $more );		
												}
											$html.='</div>';
											
											$html.='</div>';
											$html.='<div class="dbmng_separator">&nbsp</div>';
										}
								}
						}
					else
						{
							$html .= "<input type='hidden' name='$fld' id='$fld' value='$value' />\n";
						}
				} //End of fields
		} //End of form
	
	$hv = '';//dbmng_search_add_hidden($aForm, $aParam, "POST");
	if( isset($aParam['captcha']) )
		$html .= layout_form_captcha();
	
	if( $do_update == 1 )
		{
		
			$html .= "<input type='hidden' name='act' value='do_upd' />\n";
			$html .= "<input type='hidden' name='tbln' value='" . $aForm['table_name'] . "' />\n";
			$html .= "<div class='dbmng_form_button'><input  type='submit' value='". t('Update') ."' /></div>\n";
		}
	elseif( $do_update == 0 || $do_update == 3 )
		{
			$html .= "<input type='hidden' name='act' value='do_ins' />\n";
			$html .= "<input type='hidden' name='tbln' value='" . $aForm['table_name'] . "' />\n";
			$html .= "<div class='dbmng_form_button'><input class='dbmng_form_button' type='submit' value='" . $btn_name . "' /></div>\n";
		}
	elseif( $do_update == 2 && $actiontype == "search")
		{
			$html .= "<input type='hidden' name='act2' value='do_search' />\n";
			$html .= "<input type='hidden' name='tbln' value='" . $aForm['table_name'] . "' />\n";
			$html .= "<div class='dbmng_form_button'><input class='dbmng_form_button' type='submit' value='" . t('Search') . "' /></div>\n";
			//$html .= "<div class='dbmng_form_button'><input class='dbmng_form_button' type='reset' value='" . t('Reset') . "' /></div>\n";
		}

	$html .= $hv;
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
function dbmng_value_prepare($x_value, $x, $post, $aParam)
{

	$widget='input';

	

  $sValue=null;
	if(isset($post[$x]))
		{
		  $sValue=$post[$x];
		}
	
	if(isset($x_value['widget']))
		{
			$widget=$x_value['widget'];
		}

  if($widget=='multiselect')
	  {
			if(isset($post[$x."_res3"]))
				{
				  $sValue=$post[$x."_res3"];
				}
		}

 if($widget=='select_nm')
	  {
			$sValue='';
			if(isset($post[$x])){
				foreach ( $post[$x] as $vocKey => $vocValue )
					{ 
					
						$sValue.=$vocValue.'|';
						//echo '<br/>'.$vocKey.' '.	$vocValue.' '.$sValue.'<br/>';
					}
			}
			if(strlen($sValue)>0){
				$sValue = substr($sValue, 0, strlen($sValue)-1);		
			}
			//echo '<br/><b>'.$sValue.'</b><br/>';
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
					$sValue = $_FILES[$x]['name'];

			  }
			else if ($_FILES[$x]["error"] == 4)
				{ //if the file is null use the text in the checkbox
					$sValue = $post[$x.'_tmp_choosebox'];
				}		
		}

	if( $widget=='picture' )
		{
			$dir_upd_file = "docs";
			if( isset($aParam['picture']) )
				$dir_upd_file = $aParam['picture'];
				
			// echo "aParam dir:" . $dir_upd_file . "<br/>";
			// echo "File: " . $_FILES[$x]['name'] . "<br/>";
			$sValue = $dir_upd_file . $_FILES[$x]['name'];

			if( $_FILES[$x]["error"] == 0 )
				{
					$sValue = dbmng_uploadfile($_FILES[$x]['name'], $dir_upd_file, $_FILES[$x]["tmp_name"]);

					if( dbmng_is_picture($_FILES[$x]) )
						{
							//echo "picture:" . $sValue;
							if( isset($aParam['picture_version']['nrm']) )
								{
									$thumb=new thumbnail($sValue); 
									$thumb->size_auto($aParam['picture_size']['nrm']);	
									$thumb->save($aParam['picture_version']['nrm'] . $_FILES[$x]['name'] );
								}
							if( isset($aParam['picture_version']['big']) )
								{
									$thumb=new thumbnail($sValue); 
									$thumb->size_auto($aParam['picture_size']['big']);	
									$thumb->save($aParam['picture_version']['big'] . $_FILES[$x]['name'] );
								}
							if( isset($aParam['picture_version']['prw']) )
								{
									$thumb=new thumbnail($sValue); 
									$thumb->size_auto($aParam['picture_size']['prw']);	
									$thumb->save($aParam['picture_version']['prw'] . $_FILES[$x]['name'] );
								}
							if( isset($aParam['picture_version']['ext']) )
								{
									$thumb=new thumbnail($sValue); 
									$thumb->size_auto($aParam['picture_size']['ext']);	
									$thumb->save($aParam['picture_version']['ext'] . $_FILES[$x]['name'] );
								}
						}
					$sValue = $_FILES[$x]['name'];

			  }
			else if ($_FILES[$x]["error"] == 4)
				{ //if the file is null use the text in the checkbox
					$sValue = $post[$x.'_tmp_choosebox'];
				}		
		}


	//echo '<br/>Prepare '.$x." |".$sValue."|<br/>";

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
					//echo 'set '.$x.' def to '+	$sDefault;
				}

				if (dbmng_is_field_type_numeric($sType)) 
					{

						if ($widget=='select_nm'){
							$sVal  = ($sValue);
						}
						else if($sType=="int" || $sType=="bigint")
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

	//$sVal=null;
	//echo '<br/>Prepare '.$x." |".$sValue."|-|".$sVal."|".($sVal==null)."<br/>";
	//echo "<br/>value:|".strlen($sVal)."|<br/>";
	if( strlen($sVal) == 0 )
		$sVal = null;
		
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
	
	//$allowedExts = array("gif", "jpeg", "jpg", "png");
	//$temp = explode(".", $_FILES[$fn]["name"]);
	//$extension = end($temp);
	if ((($fn["type"] == "image/gif")
	|| ($fn["type"] == "image/jpeg")
	|| ($fn["type"] == "image/jpg")
	|| ($fn["type"] == "image/pjpeg")
	|| ($fn["type"] == "image/x-png")
	|| ($fn["type"] == "image/png"))
	)//&& in_array($extension, $allowedExts))
		{
			$is_picture = true;
		}

	return $is_picture;
}


/////////////////////////////////////////////////////////////////////////////
// dbmng_file_create_link
// ======================
/// This function allow to create an hypertext link with the uploaded file
/**
\param 		$value  value
\param 		$aParam  parameter array
\return   html
*/
function dbmng_file_create_link($value, $aParam)
{
  $ret="";
	//echo realpath('.').'/'.$value;
	if(!is_null($value) && $value!='')
		{
			// $link= base_path() . $aParam['file_version']['prw'] . $value;
			$dir_upd_file  = "docs/";
			if( isset($aParam['file']) )
				$dir_upd_file = $aParam['file'];

			//$link = base_path() . $aParam['file'] . $value;
			$link = base_path() . $dir_upd_file . $value;

			//if(in_array( substr(strrchr($value, '.'), 1), $allowedExts ))
			if( preg_match('/\.(gif|jpe?g|png)$/i',strtolower($value)) )
				{
					//$link = str_replace("raw/", "prw/", $link);
					$ret="<a class='dbmng_image_link' target='_NEW' href='".$link."'><img src='".$link."' /></a>\n";	//class='dbmng_image_thumb'				
				}
			else
				{
					$ret="<a class='dbmng_file_link' target='_NEW' href='".$link."'>".t("download")."</a>\n";					
				}
			$ret .= "&nbsp;";			
		}
	return $ret;
}


/////////////////////////////////////////////////////////////////////////////
// dbmng_picture_create_link
// ======================
/// This function allow to create an hypertext link with the uploaded picture
/**
\param 		$value  value
\param 		$aParam  parameter array
\return   html
*/
function dbmng_picture_create_link($value, $aParam, $layout_type)
{
  $ret="";
	$thumb='';

	//echo realpath('.').'/'.$value;
	if(!is_null($value) && $value!='')
		{
			if( isset($aParam['picture_version']['prw']) )
				$thumb = $aParam['picture_version']['prw'];
			elseif( isset($aParam['picture_version']['ext']) )
				$thumb = $aParam['picture_version']['ext'];

			if( isset($layout_type) )
				{
					if( $layout_type == "table" )
						{
							if( isset($aParam['picture_version']['prw']) )
								$thumb = $aParam['picture_version']['prw'];
							elseif( isset($aParam['picture_version']['ext']) )
								$thumb = $aParam['picture_version']['ext'];
						}
					elseif( $layout_type == "form" )
						{
							if( isset($aParam['picture_version']['nrm']) )
								$thumb = $aParam['picture_version']['nrm'];
							elseif( isset($aParam['picture_version']['big']) )
								$thumb = $aParam['picture_version']['big'];
						}
				}
				
			$link= base_path() . $thumb . $value;

			//if(in_array( substr(strrchr($value, '.'), 1), $allowedExts ))
			if( preg_match('/\.(gif|jpe?g|png)$/i',strtolower($value)) )
				{
					$ret="<a class='dbmng_image_link' target='_NEW' href='".$link."'><img src='".$link."' /></a>\n";	//class='dbmng_image_thumb'				
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
function dbmng_value_prepare_html($fld_value, $value, $aParam, $layout_type)
{
	$ret=null;

	$widget='input';
	if(isset($fld_value['widget']))
		{
			//echo $fld_value['widget']."<br/>";
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
	elseif( $widget == "select_nm" )
		{
			$aVocval = $fld_value['voc_val'];
			$val = "";
			if( isset($value) )
				{
					foreach( $value as $ind)
						{
							if(isset($aVocval[$ind])){
								$val.= $aVocval[$ind]. " | ";
							}
						} 
				}
			$ret = $val;
		}
	elseif( $widget == "multiselect" )
		{
			$aVoc = array();
			$aVoc = $fld_value['voc_val'];

			foreach($aVoc as $voc1)
				{
					foreach($aVoc[$voc1["key"]]["vals"] as $voc2)
						{
							foreach($aVoc[$voc1["key"]]["vals"][$voc2["key"]]["vals"] as $voc3)
								{
									if( $voc3["key"] == $value )
										{
											$ret = $voc1["value"] . " | " . $voc2["value"] . " | " . $voc3["value"];
										}
								}
						}
				}
		}	
	elseif($widget == "file" )
		{
			$ret=dbmng_file_create_link($value, $aParam);
		}
	elseif($widget == "picture" )
		{
			$ret=dbmng_picture_create_link($value, $aParam, $layout_type);
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
					$ret = "<input class='dbmng_checkbox' type='checkbox' checked disabled/>";
				}
			elseif($value=="0")
				{
					$ret = '<span class="dbmng_check_false">'.t('false').'<span>';
					$ret = "<input class='dbmng_checkbox' type='checkbox' disabled/>";
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

/////////////////////////////////////////////////////////////////////////////
// dbmng_get_table_structure
// ======================
/// This function add javascript and css to drupal CMS
function dbmng_add_drupal_libraries()
	{
		drupal_add_css( "sites/all/modules/dbmng_module/dbmng_module.css" );
		drupal_add_css( "sites/all/libraries/dbmng/assets/dbmng.css" );
		drupal_add_js ( "sites/all/libraries/dbmng/assets/dbmng.js" );
		drupal_add_js ( "sites/all/libraries/dbmng/assets/dbmng_obj.js" );
		drupal_add_js ( "sites/all/libraries/dbmng/assets/jquery.tablesorter.js" );
		drupal_add_library('system','ui.datepicker');
	}

/////////////////////////////////////////////////////////////////////////////
// dbmng_get_table_structure
// ======================
/// This function allow to store in the meta-database the structure of a specific
/// table identified by its id_table
/**
\param $id_table  		The id of the table in the meta-database
*/
function dbmng_get_table_structure($id_table)
{
	$sql = "select table_name from dbmng_tables where id_table = :id_table";
	
	//$fields = dbmng_query($sql, array(':id_table' => $_REQUEST['id_table']));
	$fields = dbmng_query($sql, array(':id_table' => $id_table));
	foreach($fields as $f)
		{
			$tn = $f->table_name;
		}
	
	$sql = "select * from information_schema.columns where table_name = :table_name and table_schema = :table_schema";
	$fields = dbmng_query($sql, array(':table_name' => $tn, ':table_schema' => DBMNG_DB_NAME) );
	
	foreach($fields as $f)
		{
			/* identify the primary key */
			$pk = 0;
			if( strlen($f->COLUMN_KEY) != 0 )
				{
					if( strlen(trim($f->EXTRA)) != 0 )
						{
							if( $f->EXTRA == 'auto_increment' )
								$pk = 1;
							else
								$pk = 2;
						}
				}
			
			/* Map type into crud type */
			$sType ="";
			switch( $f->DATA_TYPE )
				{
					case "int":
					case "bigint":
						$sType = "int";
						break;
					case "float":
					case "double":
						$sType = "double";
						break;
					case "date";
						$sType = "date";
						break;
					default:
						$sType = "varchar";
				}
			
			/* Assign the 'basic' widget */
			$widget = "";
			$voc_sql = null;
			switch( $f->DATA_TYPE )
				{
					case "int":
						if( strpos($f->COLUMN_NAME, "id_" ) !== false && $pk == 0 )
							{
								$widget = "select";
							}
						else
							{
								$widget = "input";
							}
						break;
					case "float":
						$widget = "input";
						break;
					case "date";
						$widget = "date";
						break;
					default:
						$widget = "input";
				}
			
			/* identify if a fields accept or no to be empty */
			$nullable = 0;
			if( $f->IS_NULLABLE == "YES" )
				$nullable = 1;
			
			/* Prepare insert sql command */
			$sql  = "insert into dbmng_fields( id_table, id_field_type, field_widget, field_name, nullable, field_label, field_order, pk, is_searchable ) ";
			$sql .= "values( :id_table, :id_field_type, :field_widget, :field_name, :nullable, :field_label, :field_order, :pk, :is_searchable );";
			$var = array(':id_table' => $id_table, 
									 ':id_field_type' => $sType, 
									 ':field_widget' => $widget, 
									 ':field_name' => $f->COLUMN_NAME,
									 ':nullable' => $nullable,
									 ':field_label' => ucfirst(str_replace("_", " ", $f->COLUMN_NAME)),
									 ':field_order' => $f->ORDINAL_POSITION*10,
									 ':pk' => $pk,
									 ':is_searchable' => 0);
			$result = dbmng_query($sql, $var);
			
			//echo debug_sql_statement($sql, $var) . "<br/>";			
		}
}



/////////////////////////////////////////////////////////////////////////////
// req_equal
// ======================
/// This function return true if an element value is equal to the val
/// parameter
/**
\param $type_var  		$_REQUEST element
\param $val  				$_REQUEST value
\return $ret				boolean
*/
function req_equal($type_var, $val){
	$ret=false;
	//echo $type_var;
	if(isset($_REQUEST[$type_var])){
		//echo $_REQUEST[$type_var];
		if($_REQUEST[$type_var]==$val){
			$ret=true;
		}
	}
	//echo $ret;
	return $ret;
}
?>