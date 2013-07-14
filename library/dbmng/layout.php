<?php // 
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

function layout_get_nullable($fld_value)
	{
		$ht = "";
		if(isset($fld_value['nullable']) && $fld_value['nullable'] == 0)
				$ht .= "required ";			
		return $ht;
	}


function layout_get_label($fld, $fld_value)
	{
		$lb = $fld_value['label'];
		if( isset( $fld_value['label_long'] ) )
				$lb =  $fld_value['label_long'];			

		return "<label for='$fld'>" . t($lb) . "</label>\n";
	}

function layout_form_date( $fld, $fld_value, $value )
{

	$datetime_str='';

	//format the date string 
	if(!is_null($value) && $value!=''){
		$datetime = DateTime::createFromFormat('Y-m-d', $value);
		$datetime_str= $datetime->format('d-m-Y');
	}

	//add a new input field for the datapicker ui
	$html  = "<input type='text' name='$fld'_tmp id='".$fld."_tmp' value='".$datetime_str."' />";
	//keep hidden the "real" input form
	$html .= "<input type='hidden' name='$fld' id='".$fld."' ";
	$html .= " value= '$value' ";	
	$html .= layout_get_nullable($fld_value);	
	$html .= " />\n";
	$html .='<script>  jQuery(function() { jQuery( "#'.$fld.'_tmp" ).datepicker({altField: \'#'.$fld.'\', dateFormat:\'dd-mm-yy\' , altFormat: \'yy-mm-dd\'});  });  </script>';
	return $html;
}


//This is a comment
function layout_form_input( $fld, $fld_value, $value, $more='' )
{
	$html  = "<input type='text' name='$fld' id='$fld' $more";
	$html .= " value= '$value' ";	
	$html .= layout_get_nullable($fld_value);	
	$html .= " />\n";
	return $html;
}


	

function layout_form_textarea( $fld, $fld_value, $value )
{		
	$html  = "<textarea  name='$fld' id='$fld'  ".layout_get_nullable($fld_value)." >";
	$html .= $value;	
	$html .= "</textarea>\n";
	return $html;
}


function layout_form_checkbox( $fld, $fld_value, $value )
{
	$html = "<input class='dbmng_checkbox' type='checkbox' name='$fld' id='$fld' ";
	if($value==1 || ($value<>0 &&  $fld_value['default']=="1"))
    {
			$html .= " checked='true' ";
		}	
	 
	$html .= layout_get_nullable($fld_value);	
	$html .= " />\n";

	return $html;
}

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

function layout_table_footer($aField)
{
	$html  = "";
	$html .= "<tfoot>\n";
	$html .= "<tr>\n";
	foreach ( $aField as $fld => $fld_value )
		{
			if( layout_view_field_table($fld_value) )
				$html .= "<td><input type='text' name='$fld' id='$fld' placeholder='" . t("Search") . " " . t($fld_value['label']) . "' /></td>\n";
		}
	$html .= "<td>" . t("Clear filtering") . "</td>";
	$html .= "</tr>\n";
	$html .= "</tfoot>\n";
	return $html;
}

function layout_view_field_table($fld_value){
	$ret=true;	
	if( isset($fld_value['skip_in_tbl']) ) {
		if($fld_value['skip_in_tbl'] == 1){
			$ret=false;
		}
	}
	return $ret;
}


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
		$html .= "<a href='?del_" . $aForm['table_name'] . "=" . $id_record .$hv."'>" . t('Delete') . "</a>" . "&nbsp;";
	if( $nUpd == 1 ) 
		$html .= "<a href='?upd_" . $aForm['table_name'] . "=" . $id_record .$hv."'>" . t('Update') . "</a>" . "&nbsp;";
	if( $nDup == 1 )
		$html .= "<a href='?dup_" . $aForm['table_name'] . "=" . $id_record .$hv."'>" . t('Duplicate') . "</a>" . "&nbsp;";

	return $html;
}

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

function layout_table_insert($aForm, $aParam)
{
  // Initialization of user function variable
  $nIns = 1;
	if( isset($aParam['user_function']) )
	  $nIns = (isset($aParam['user_function']['ins']) ? $aParam['user_function']['ins'] : 1 );

	$hv = prepare_hidden_var($aParam);
	
	$html = "";
	if( $nIns == 1)
		$html .= "<a href='?ins_" . $aForm['table_name'] . $hv. "'>" . t('Insert new data') . "</a><br />";
	
	return $html;
}

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

					$value=dbmng_value_prepare_html($fld_value, $record->$fld);
					if( layout_view_field_table($fld_value) )
						{
							$html.= "<td class='dbmng_field_$fld'>".$value."</td>";
						}

				}

			// available functionalities
			$html .= "<td class='dbmng_functions'>";

			$id_record = $record->$aForm['primary_key'][0];

			$html .= layout_table_action( $aForm, $aParam, $id_record );
			$html .= layout_table_custom_function($aParam, $id_record);

			$html .= "</td>\n";
			
			$html .= "</tr>\n";
		}
	$html .= "</tbody>\n";

  return $html;
}

function layout_table( $result, $aForm, $aParam )
{
	$html = "";
	$html .= "<table>\n";
	
	$html .= layout_table_head( $aForm['fields'] );
	
	// write FOOTER row
	if( $result->rowCount() > 1 )
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
	
	return $html;
}
?>
