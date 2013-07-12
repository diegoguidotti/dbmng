<?php // 
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

?>