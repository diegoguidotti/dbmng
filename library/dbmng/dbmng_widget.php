<?php

function dbmng_widget_input( $fld, $fld_value, $value, $more='', $mode )
{
	switch( $mode )
		{
			case "form":
				$html  = "<input type='text' name='$fld' id='$fld' $more";
				$html .= " value= '$value' ";	
				$html .= layout_get_nullable($fld_value);	
				$html .= " />\n";
				break;
			
			case "html":
				$html = $value;
				break;
		}
		
	return $html;
}
?>