<?php // 
function layout_input( $fld, $fld_value, $value )
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

function layout_textarea( $fld, $fld_value, $value )
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

function layout_select( $fld, $fld_value, $value )
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

?>