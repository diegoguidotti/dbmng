<?php
function layout_textarea($fld, $other, $value)
{
	$html  = "";
	$html .= "<textarea  name='$fld' id='$fld'  $other >";
	$html .= " $value ";	
	$html .= "</textarea>\n";
	return $html;
}

function layout_select($fld, $other, $aVoc, $value, $do_update)
{
	$html  = "";
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



/*
									else if ($x_value['type']=='select')
									{
										$html .= "<select  name='$id' id='$id'  $other >\n";
										$html .= "<option/> \n";	
										$nLen = count($aVoc);
										
										foreach ( $aVoc as $vocKey => $vocValue )
										{
											$s="";
											if($do_update && $value==$vocKey){
												$s=" selected='true' ";
											}

											$html .= "<option $s value='" . $vocKey . "'>" . $vocValue . "</option> \n";	
										}
										$html .= "</select>\n";
									}
									else //varchar and integer
									{									
										$html .= "<input type='text' name='$id' id='$id' ";
										$html .= " value= '$value' ";	
										$html .= " $other ";	
										$html .= " />\n";
									}
*/
?>