<?php // 
/////////////////////////////////////////////////////////////////////////////
// prepare_hidden_var
// ======================
/// This function prepare the hidden var string
/**
\param $aParam  		parameter array
\return             string
*/
function prepare_hidden_var($aParam)
{
	$hv = "";

	if(isset($aParam['hidden_vars']))
		{
			foreach ( $aParam['hidden_vars'] as $fld => $fld_value )
				{				
					$hv.= ('&amp;' . $fld . '=' . $fld_value);
				}
		}
	return $hv;
}


/////////////////////////////////////////////////////////////////////////////
// layout_get_nullable
// ======================
/// This function add a required attribute 
/**
\param $fld_value		field value
\return             html attribute
*/
function layout_get_nullable($fld_value)
	{
		$ht = "";
		if(isset($fld_value['nullable']) && $fld_value['nullable'] == 0)
				$ht .= "required ";			
		return $ht;
	}


/////////////////////////////////////////////////////////////////////////////
// layout_get_label
// ======================
/// This function get the label to shows in form 
/**
\param $fld					field name
\param $fld_value		field value
\return             html
*/
function layout_get_label($fld, $fld_value)
	{
		$lb = $fld_value['label'];
		if( isset( $fld_value['label_long'] ) )
			$lb =  $fld_value['label_long'];			
		
		$sRequired = "";
		if(isset($fld_value['nullable']) && $fld_value['nullable'] == 0)
			$sRequired = "<span class='dbmng_required'>*</span>";
			
		return "<label for='$fld'>" . t($lb) . $sRequired . "</label>\n";
	}


/////////////////////////////////////////////////////////////////////////////
// layout_form_date
// ======================
/// This function allow to add the data widget to the form
/**
\param $fld					field name
\param $fld_value		field value
\param $value				stored value
\return             html
*/
function layout_form_date( $fld, $fld_value, $value )
{

	$datetime_str='';

	//format the date string 
	if(!is_null($value) && $value!=''){
		$datetime = DateTime::createFromFormat('Y-m-d', $value);
		$datetime_str= $datetime->format('d-m-Y');
	}

	//add a new input field for the datapicker ui
	$html  = "<input type='text' name='".$fld."_tmp' id='".$fld."_tmp' value='".$datetime_str."' />";
	//keep hidden the "real" input form
	$html .= "<input type='hidden' name='$fld' id='".$fld."' ";
	$html .= " value= '$value' ";	
	$html .= layout_get_nullable($fld_value);	
	$html .= " />\n";
	$html .='<script>  jQuery(function() { jQuery( "#'.$fld.'_tmp" ).datepicker({altField: \'#'.$fld.'\', dateFormat:\'dd-mm-yy\' , altFormat: \'yy-mm-dd\'});  });  </script>';
	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_form_input
// ======================
/// This function allow to add the widget input (html tag)
/**
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\param $more 				allow to insert other attributes
\return             html
*/
function layout_form_input( $fld, $fld_value, $value, $more='' )
{
	$html  = "<input type='text' name='$fld' id='$fld' $more";
	$html .= " value= '$value' ";	
	$html .= layout_get_nullable($fld_value);	
	$html .= " />\n";
	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_form_password
// ======================
/// This function allow to add the widget password 
/**
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\param $more 				allow to insert other attributes
\return             html
*/
function layout_form_password( $fld, $fld_value, $value, $more='' )
{
	$html  = "<input type='password' name='$fld' id='$fld' $more";
	$html .= " value= '$value' ";	
	$html .= layout_get_nullable($fld_value);	
	$html .= " />\n";
	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_form_textarea
// ======================
/// This function allow to add the widget textarea (html tag) 
/**
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\return             html
*/
function layout_form_textarea( $fld, $fld_value, $value )
{		
	$html  = "<textarea  name='$fld' id='$fld'  ".layout_get_nullable($fld_value)." >";
	$html .= $value;	
	$html .= "</textarea>\n";
	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_form_file
// ======================
/// This function allow to add the widget file (html tag) 
/**
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\return             html
*/
function layout_form_file( $fld, $fld_value, $value )
{		
  $html  = "<span id='".$fld."_link_container'>".dbmng_file_create_link($value).'</span><br/>';
	$html .= "<div class='dbmng_file_hide_this'><input type='file' name='$fld' id='$fld' ></div>";

	$html .= '<input class="dbmng_file_text" type="text" name="'.$fld.'_tmp_choosebox" id="'.$fld.'_tmp_choosebox" value="'.$value.'" />';
	$html .= '<a href="#" id="'.$fld.'_tmp_choose">'.t('Choose').'</a>';
	$html .= '<a href="#" id="'.$fld.'_tmp_remove">'.t('Remove').'</a>';

	$html .= "<script type=\"text/javascript\">dbmng_style_fileform('".$fld."');</script>";


	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_form_checkbox
// ======================
/// This function allow to add the widget checkbox (html tag) 
/**
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\return             html
*/
function layout_form_checkbox( $fld, $fld_value, $value )
{
	$html = "<input class='dbmng_checkbox' type='checkbox' name='$fld' id='$fld' ";
	if($value==1 || ($value<>0 &&  $fld_value['default']=="1"))
    {
			$html .= " checked='true' ";
		}	
	 
  //the field will never reply with a null value (true or false)
	//if setted as a non_nullable it will accept only true values
	//$html .= layout_get_nullable($fld_value);	
	$html .= " />\n";

	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_form_select
// ======================
/// This function allow to add the widget select (html tag) 
/**
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\return             html
*/
function layout_form_select( $fld, $fld_value, $value )
{
	$do_update = false;
	if( !is_null($value) )
		$do_update = true;
	
	if( $fld_value['widget'] == "select" )
		{
			$aVoc = array();
			$aVoc = $fld_value['voc_val'];
		} 
	$html = "<select  name='$fld' id='$fld'  ".layout_get_nullable($fld_value)." >\n";
	$html .= "<option/> \n";	
	$nLen  = count($aVoc);
	
	foreach ( $aVoc as $vocKey => $vocValue )
	{
		$s = "";
		if($do_update && $value==$vocKey){
			$s = " selected='true' ";
		}

		$html .= "<option $s value='" . $vocKey . "'>" . $vocValue . "</option> \n";	
	}
	$html .= "</select>\n";
	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_table_head
// ======================
/// This function allow to add the table head (<thead> and <th> attributes)
/**
\param $aField			field name
\return             html
*/
function layout_table_head($aField)
{
	$html  = ""; 
	$html .= "<thead>\n";
	$html .= "<tr>\n";
	foreach ( $aField as $fld => $fld_value )
		{
			
			if( layout_view_field_table($fld_value) )
				$html .= "<th class='dbmng_field_$fld'>" . t($fld_value['label']) . "</th>\n";
		}
	$html .= "<th class='dbmng_functions'>" . t('actions') . "</th>\n";
	$html .= "</tr>\n";
	$html .= "</thead>\n";
	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_table_footer
// ======================
/// This function allow to add the table head (<tfoot> attribute)
/**
\param $aField			field name
\return             html
*/
function layout_table_footer($aField)
{
	$html  = "";
	$html .= "<tfoot>\n";
/*
	$html .= "<tr>\n";
	foreach ( $aField as $fld => $fld_value )
		{
			if( layout_view_field_table($fld_value) )
				$html .= "<td><input type='text' name='$fld' id='$fld' placeholder='" . t("Search") . " " . t($fld_value['label']) . "' /></td>\n";
		}
	$html .= "<td>" . t("Clear filtering") . "</td>";
	$html .= "</tr>\n";
*/
	$html .= "</tfoot>\n";
	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_view_field_table
// ======================
/// This function allow to add the table head (<tfoot> attribute)
/**
\param $fld_value  	field value
\return             html
*/
function layout_view_field_table($fld_value){
	$ret=true;	
	if( isset($fld_value['skip_in_tbl']) ) {
		if($fld_value['skip_in_tbl'] == 1){
			$ret=false;
		}
	}
	return $ret;
}


/////////////////////////////////////////////////////////////////////////////
// layout_table_action
// ======================
/// This function allow to add the record actions
/**
\param $aForm  			metadata table array 
\param $aParam  	  parameter array
\param $id_record  	id record
\return             html
*/
function layout_table_action( $aForm, $aParam, $id_record )
{
	$nDel = 1;	$nUpd=1; 	$nDup=1; 
	// get user function parameters
	if( isset($aParam['user_function']) )
	{
	  $nUpd = (isset($aParam['user_function']['upd']) ? $aParam['user_function']['upd'] : 1 );
	  $nDel = (isset($aParam['user_function']['del']) ? $aParam['user_function']['del'] : 1 );
	  $nDup = (isset($aParam['user_function']['dup']) ? $aParam['user_function']['dup'] : 1 );				
	}
	
	$html = "";
	$hv   = prepare_hidden_var($aParam);
	if( $nDel == 1 )
		{
			$jsc = "return confirm('".t('Are you sure?')."')";
			//$html .= '<a class="dbmng_delete_button" onclick="'.$jsc.'" href="?del_' . $aForm['table_name'] . '=' . $id_record .$hv.'">' . t('Delete') . '</a>' . "&nbsp;";
			$html .= '<a class="dbmng_delete_button" onclick="'.$jsc.'" href="?act=del&tbl=' . $aForm['table_name'] . '&' . $id_record .$hv.'">' . t('Delete') . '</a>' . "&nbsp;";
		}
	if( $nUpd == 1 ) 
		$html .= "<a class='dbmng_update_button' href='?upd_" . $aForm['table_name'] . "=" . $id_record .$hv."'>" . t('Update') . "</a>" . "&nbsp;";
	if( $nDup == 1 )
		$html .= "<a class='dbmng_duplicate_button' href='?dup_" . $aForm['table_name'] . "=" . $id_record .$hv."'>" . t('Duplicate') . "</a>" . "&nbsp;";

	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_table_custom_function
// ======================
/// This function allow to add costum function
/**
\param $aParam  	  parameter array
\param $id_record  	id record
\return             html
*/
function layout_table_custom_function($aParam, $id_record)
{
	$hv = prepare_hidden_var($aParam);

	$html = "";
	if(isset($aParam['custom_function']))
	{
		foreach($aParam['custom_function'] as $aCustom )								
		{	
			$html.="<a href='?$aCustom[custom_variable]=$id_record$hv'>$aCustom[custom_label]</a>";
		}
	}
	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_table_insert
// ======================
/// This function allow to add the link "Insert new data"
/**
\param $aForm  			metadata table array 
\param $aParam  	  parameter array
\return             html
*/
function layout_table_insert($aForm, $aParam)
{
  // Initialization of user function variable
  $nIns = 1;
	if( isset($aParam['user_function']) )
	  $nIns = (isset($aParam['user_function']['ins']) ? $aParam['user_function']['ins'] : 1 );

	$hv = prepare_hidden_var($aParam);
	
	$html = "";
	if( $nIns == 1)
		$html .= "<a class='dbmng_insert_button' href='?ins_" . $aForm['table_name'] . $hv. "'>" . t('Insert new data') . "</a><br />\n";
	
	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_table_body
// ======================
/// This function allow to add the link "Insert new data"
/**
\param $result 			A prepared statement object, already executed 
\param $aForm  			metadata table array 
\param $aParam  	  parameter array
\return             html
*/
function layout_table_body( $result, $aForm, $aParam )
{
	$html ="";
	// write BODY content 
	$html .= "<tbody>\n";
	foreach ($result as $record) 
		{
			// table value
			$html .= "<tr>";
			
			//get the query results for each field
			foreach ( $aForm['fields'] as $fld => $fld_value )
				{

					//echo '!'.$record->$fld.'!'.$fld.'<br/>';
					$value=dbmng_value_prepare_html($fld_value, $record->$fld);
					if( layout_view_field_table($fld_value) )
						{
							$html.= "<td class='dbmng_field_$fld'>".$value."</td>";
						}

				}

			// available functionalities
			$html .= "<td class='dbmng_functions'>";

			$pkfield = "";
			$id_record = "";
			foreach ( $aForm['fields'] as $fld => $fld_value )
				{
				if($fld_value['key'] == 1 || $fld_value['key'] == 2 )
					{
						$pkfield = $fld;
						$id_record .= $fld . "=" . $record->$fld . "&";
					}
				}
			// $id_record = $record->$aForm['primary_key'][0];
			//$id_record = $record->$pkfield;

			$html .= layout_table_action( $aForm, $aParam, $id_record );
			$html .= layout_table_custom_function($aParam, $id_record);

			$html .= "</td>\n";
			
			$html .= "</tr>\n";
		}
	$html .= "</tbody>\n";

  return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_table
// ======================
/// This function allow to add the link "Insert new data"
/**
\param $result 			A prepared statement object, already executed 
\param $aForm  			metadata table array 
\param $aParam  	  parameter array
\return             html
*/
function layout_table( $result, $aForm, $aParam )
{
	$id_tbl  = "";
	$class   = "";
	$add_js  = "";
	if( isset($aParam['tbl_sorter']) )
	{
		$id_tbl  = "id='" . $aForm['table_name'] . "'";
		$class   = "class='tablesorter'";
		
		$nColumn = 0;
		foreach ( $aForm['fields'] as $fld => $fld_value )
			{
				if( layout_view_field_table($fld_value) )
					$nColumn++;
			}
		$add_js  = "<script type=\"text/javascript\">dbmng_tablesorter('".$aForm['table_name']."',".$nColumn.");</script>\n";
	}

	$html = "";
	$html .= "<table $id_tbl $class>\n";
	
	$html .= layout_table_head( $aForm['fields'] );
	
	// write FOOTER row
	if( dbmng_num_rows($result) > 1 )
	{
		if( isset($aParam['tbl_footer']) )
		{
			if( $aParam['tbl_footer'] == 1 )
			{
				$html .= layout_table_footer( $aForm['fields'] );
			}
		}
	}		
	$html .= layout_table_body($result, $aForm, $aParam);
  $html .= "</table>\n";
	
	$html .= layout_table_insert($aForm, $aParam);
	$html .= $add_js;
	
	return $html;
}
?>
