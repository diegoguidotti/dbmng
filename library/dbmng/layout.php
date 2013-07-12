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

//This is a comment
function layout_form_input( $fld, $fld_value, $value )
{
	$other = "";		
	if($fld_value['nullable'] == 0)
		$other .= "required ";			

	$html  = "";
	$html .= "<label for='$fld'>" . t($fld_value['label_long']) . "</label>\n";
	$html .= "<input type='text' name='$fld' id='$fld' ";
	$html .= " value= '$value' ";	
	$html .= " $other ";	
	$html .= " />\n";
	return $html;
}

function layout_form_textarea( $fld, $fld_value, $value )
{
	$other = "";		
	if($fld_value['nullable'] == 0)
		$other .= "required ";			

	$html  = "";
	$html .= "<label for='$fld'>" . t($fld_value['label_long']) . "</label>\n";
	$html .= "<textarea  name='$fld' id='$fld'  $other >";
	$html .= " $value ";	
	$html .= "</textarea>\n";
	return $html;
}

function layout_form_select( $fld, $fld_value, $value )
{
	$do_update = false;
	if( !is_null($value) )
		$do_update = true;
	
	if( $fld_value['type'] == "select" )
		{
			$aVoc = array();
			$aVoc = $fld_value['voc_val'];
		} 

	$other = "";		
	if($fld_value['nullable'] == 0)
		$other .= "required ";			

	$html  = "";
	$html .= "<label for='$fld'>" . t($fld_value['label_long']) . "</label>\n";
	$html .= "<select  name='$fld' id='$fld'  $other >\n";
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
			if( $fld_value['skip_in_tbl'] == 0 )
				$html .= "<th>" . t($fld_value['label']) . "</th>\n";
		}
	$html .= "<th>" . t('actions') . "</th>\n";
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
			if( $fld_value['skip_in_tbl'] == 0 )
				$html .= "<td><input type='text' name='$fld' id='$fld' placeholder='" . t("Search") . " " . t($fld_value['label']) . "' /></td>\n";
		}
	$html .= "<td>" . t("Clear filtering") . "</td>";
	$html .= "</tr>\n";
	$html .= "</tfoot>\n";
	return $html;
}

function layout_table_select( $fld_value, $value )
{
	$html = "";
	if( $fld_value['skip_in_tbl'] == 0 )
		{
			if( $fld_value['type'] == "select" )
				{
					$aVoc = array();
					$aVoc = $fld_value['voc_val'];
					if(isset($aVoc[$value])){
						$html.= "<td>" . $aVoc[$value] . "</td>";
					}
					else{
						$html.= "<td></td>";
					}
				}
		}
	return $html;
}

function layout_table_cell( $fld_value, $value )
{
	$html = "";
	if( $fld_value['skip_in_tbl'] == 0 && $fld_value['type'] != "select" )
		{
			$html.= "<td>".$value."</td>";
		}
	return $html;
}

function layout_table_action( $aForm, $aParam, $id_record )
{
		
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
  // $nIns = 1;
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
					$html .= layout_table_select( $fld_value, $record->$fld );
					$html .= layout_table_cell( $fld_value, $record->$fld );
				}

			// available functionalities
			$html .= "<td>";

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
