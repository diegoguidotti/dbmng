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
// prepare_hidden_var_form
// ======================
/// This function prepare the hidden var for the form
/**
\param $aParam  		parameter array
\return             html
*/
function prepare_hidden_var_form($aParam)
{
	$hv = "";
	if(isset($aParam['hidden_vars']))
		{
			foreach ( $aParam['hidden_vars'] as $fld => $fld_value )
				{				
					$hv.= ('<input type="hidden" name="' . $fld . '" value="' . $fld_value.'" />');
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
		if( isset($_REQUEST['act2']) )
			{
				if( !isset($_REQUEST['act2']) == "do_search" ) //!isset($_REQUEST['act2']) == "search" && 
					{
						if(	isset($fld_value['nullable']) && $fld_value['nullable'] == 0 )
								$ht .= "required ";
					}
			}
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
		if( isset($fld_value['label_long']) )
			$lb =  $fld_value['label_long'];			
		
		$sRequired = "";
		if( isset($_REQUEST['act2']) )
			{
				if( !$_REQUEST['act2'] == "do_search" ) // $_REQUEST['act2'] != "search" && 
					{
						if(isset($fld_value['nullable']) && $fld_value['nullable'] == 0)
							$sRequired = "<span class='dbmng_required'>*</span>";
					}
			}
		else
			{
				if(isset($fld_value['nullable']) && $fld_value['nullable'] == 0)
					$sRequired = "<span class='dbmng_required'>*</span>";
			}
			
		$labelfor = "dbmng_".$fld;
		if( isset($fld_value['widget']) )
			{
				if( $fld_value['widget'] == "date" )		
					{
						$labelfor .= "_tmp";
					}
			}
					
		return "<label for='".$labelfor."'>" . t($lb) . $sRequired . "</label>\n";
	}


/////////////////////////////////////////////////////////////////////////////
// layout_form_date
// ======================
/// This function allow to add the data widget to the form
/**
\param $aInput			associative array that contains the follows param:
\param $fld					field name
\param $fld_value		field value
\param $value				stored value
\return             html
*/
function layout_form_date( $aInput )
{
	$fld = $aInput['fld'];
	$fld_value = $aInput['fld_value'];
	$value = $aInput['value'];
	$actiontype = $aInput['actiontype'];
	$aParam = $aInput['aParam'];

	$placeholder = "";
	if(isset($aParam['hide_label']))
		{
			$placeholder = "placeholder='".$fld_value['label']."'";
		}

	if( strlen($aInput['actiontype'])!= 0 )
		$actiontype = "search_";

	$datetime_str='';

	//format the date string 
	if(!is_null($value) && $value!='')
		{
			$datetime = DateTime::createFromFormat('Y-m-d', $value);
			$datetime_str= $datetime->format('d-m-Y');
		}

	//add a new input field for the datapicker ui
	$html  = "<input type='text' name='".$actiontype.$fld."_tmp' id='dbmng_".$fld."_tmp' $placeholder value='".$datetime_str."' />";
	//keep hidden the "real" input form
	$html .= "<input type='hidden' name='$actiontype$fld' id='dbmng_".$fld."' ";
	$html .= " value= '$value' ";	
	$html .= layout_get_nullable($fld_value);	
	$html .= " />\n";
	$html .='<script>  jQuery("#dbmng_'.$fld.'_tmp").blur(function(){ if(jQuery("#dbmng_'.$fld.'_tmp").val()==""){  jQuery("#dbmng_'.$fld.'").val("");} }); jQuery(function() { jQuery( "#dbmng_'.$fld.'_tmp" ).datepicker({altField: \'#dbmng_'.$fld.'\', dateFormat:\'dd-mm-yy\' , altFormat: \'yy-mm-dd\'});  });  </script>';
	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_form_input
// ======================
/// This function allow to add the widget input (html tag)
/**
\param $aInput			associative array that contains the follows param:
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\param $more 				allow to insert other attributes
\return             html
*/
function layout_form_input($aInput) //( $fld, $fld_value, $value, $more='' )
{
	$fld = $aInput['fld'];
	$fld_value = $aInput['fld_value'];
	$value = $aInput['value'];
	$more = $aInput['more'];
	$actiontype = $aInput['actiontype'];
	$aParam = $aInput['aParam'];

	$placeholder = "";
	if(isset($aParam['hide_label']))
		{
			$placeholder = "placeholder='".$fld_value['label']."'";
		}
	
	if( strlen($aInput['actiontype'])!= 0 )
		$actiontype = "search_";
			
	$html  = "<input type='text' name='$actiontype$fld' id='dbmng_$fld' $more";
	$html .= " value= '$value' $placeholder ";	
	$html .= layout_get_nullable($fld_value);	
	$html .= " />\n";

	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_form_hidden
// ======================
/// This function allow to add hidden var (html tag)
/**
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\param $more 				allow to insert other attributes
\return             html
*/
function layout_form_hidden( $fld, $value )
{
	//print_r($value);
	$html = "<input type='hidden' name='$fld' id='dbmng_$fld' value='".$value."' />\n";

	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_form_password
// ======================
/// This function allow to add the widget password 
/**
\param $aInput			associative array that contains the follows param:
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\param $more 				allow to insert other attributes
\return             html
*/
function layout_form_password( $aInput )
{
	$fld = $aInput['fld'];
	$fld_value = $aInput['fld_value'];
	$value = $aInput['value'];
	$actiontype = $aInput['actiontype'];
	$aParam = $aInput['aParam'];

	$placeholder = "";
	if(isset($aParam['hide_label']))
		{
			$placeholder = "placeholder='".$fld_value['label']."'";
		}
	
	
	if( strlen($aInput['actiontype'])!= 0 )
		$actiontype = "search_";

	$html  = "<input type='password' name='$actiontype$fld' $placeholder id='dbmng_$fld'";
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
\param $aInput			associative array that contains the follows param:
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\return             html
*/
function layout_form_textarea( $aInput )
{		
	$fld = $aInput['fld'];
	$fld_value = $aInput['fld_value'];
	$value = $aInput['value'];
	$actiontype = $aInput['actiontype'];

	if( strlen($aInput['actiontype'])!= 0 )
		$actiontype = "search_";
			
	$html  = "";
	$html .= "<textarea name='$actiontype$fld' id='dbmng_$fld' ".layout_get_nullable($fld_value)." >";
	$html .= $value;	
	$html .= "</textarea>\n";
	return $html;
}

/////////////////////////////////////////////////////////////////////////////
// layout_form_captcha
// ======================
/// This function allow to add the widget textarea (html tag) 
/**
\param $aInput			associative array that contains the follows param:
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\return             html
*/
function layout_form_captcha( )
{		
	$x = rand(0,10);
	$y = rand(0,10);
	$html  = "";
	if( true )
		{
			$html .= '<br/><br/><br/><fieldset class="captcha form-wrapper">';
			$html .= '<legend><span class="fieldset-legend">CAPTCHA</span></legend>';
			$html .= '<div class="fieldset-wrapper">';
			$html .= '<div class="fieldset-description">This question is for testing whether or not you are a human visitor and to prevent automated spam submissions.</div>';
			$html .= '<input type="hidden" name="dbmng_x" id="dbmng_x" value='.$x.' />'; 
			$html .= '<input type="hidden" name="dbmng_y" id="dbmng_y" value='.$y.' />'; 
			
			$html .= '<div class="form-item form-type-textfield form-item-captcha-response">';
			$html .= "<label for='captcha'>" . t("Math question") . "<span class='dbmng_required' title='This field is required.'>*</span>" . "</label>\n"; 
			$html .= '<span class="field-prefix">'.$x.' + '.$y.' = </span> <input type="text" id="edit-captcha" name="captcha" value="" size="4" maxlength="2" class="form-text required" />';
			$html .= '<div class="description">Solve this simple math problem and enter the result. E.g. for 1+3, enter 4.</div>';
			$html .= '</div></div></fieldset>';
		}
	else
		{
			$html .= "<div class='dbmng_form_row dbmng_form_field_res_date'>";
			$html .= "<p>This question is for testing whether or not you are a human visitor and to prevent automated spam submissions.</p>";
			$html .= "<label for='captcha'>" . t("Math question") . "<span class='dbmng_required'>*</span>" . "</label>\n";
			$html .= "<div class='dbmbg_form_element'>";
			$html .= '<input type="hidden" name="dbmng_x" id="dbmng_x" value='.$x.' />'; 
			$html .= '<input type="hidden" name="dbmng_y" id="dbmng_y" value='.$y.' />'; 
			$html .= "$x + $y = <input class='dbmng_captcha' type='text' name='captcha'>";
			$html .= "Solve this simple math problem and enter the result. E.g. for 1+3, enter 4.";
			$html .= "</div></div>";
		}
	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_form_html
// ======================
/// This function allow to add the widget html (html tag) 
/**
\param $aInput			associative array that contains the follows param:
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\return             html
*/
function layout_form_html( $aInput )
{		
	$fld = $aInput['fld'];
	$fld_value = $aInput['fld_value'];
	$value = $aInput['value'];
	$actiontype = $aInput['actiontype'];

	if( strlen($aInput['actiontype'])!= 0 )
		$actiontype = "search_";
			
	$html='';
	if(DBMNG_CMS)
		drupal_add_js ( "sites/all/libraries/tinymce/jscripts/tiny_mce/tiny_mce.js" );
	else {		
		$html.='<script src="//tinymce.cachefly.net/4.0/tinymce.min.js"></script>';
	}

	$html .= '<script>';
  $html .= '	tinymce.init({selector:"textarea#dbmng_'.$fld.'"});';
	$html .= '</script>';
	$html .= "<textarea class='html_widget' name='$actiontype$fld' id='dbmng_$fld' ".layout_get_nullable($fld_value)." >";
	$html .= $value;	
	$html .= "</textarea>\n";
	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_form_multi
// ======================
/// This function allow to add the widget multi (html tag)
/**
\param $aInput			associative array that contains the follows param:
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\param $more 				allow to insert other attributes
\return             html
*/
function layout_form_multi( $aInput )
{		
	$fld = $aInput['fld'];
	$fld_value = $aInput['fld_value'];
	$value = $aInput['value'];
	$actiontype = $aInput['actiontype'];
	
	if( strlen($aInput['actiontype'])!= 0 )
		$actiontype = "search_";

	$html  = "<select class='dbmng_multi_left'  multiple  id='dbmng_$fld' name='$actiontype$fld'  >";
	$html  .= '</select>';
	// 'voc_table'=>'country', 'voc_table_pk'=>'id_country', 'voc_table_label'=>'country_label', 'rel_table'=>'test_country', 'rel_table_fk1'=>'id_test', 'rel_table_fk2'=>'id_country'  );
	
	$sql= "select  ".$fld_value['voc_table_pk'].",  ".$fld_value['voc_table_label']." from  ".$fld_value['voc_table']." ";
	$res=dbmng_query($sql,array());
	
	
	$html  .= "<select class='dbmng_multi_right' multiple id='dbmng_".$fld."_from' name='".$actiontype.$fld."_from'  >";
	
	foreach ($res as $val) {
	 	  $html.="<option value='".$val->$fld_value['voc_table_pk']."' >".$val->$fld_value['voc_table_label']."</option>";
	} 
	
	$html  .= '</select>';
	
	$js = "dbmng_multi('".$fld."');";
	
	$html  .= '<a href="javascript:'.$js.'">'.t('choice')."</a>";
	 
	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_form_file
// ======================
/// This function allow to add the widget file (html tag) 
/**
\param $aInput			associative array that contains the follows param:
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\param $aParam			parameters array
\return             html
*/
function layout_form_file( $aInput )
{		
	$fld = $aInput['fld'];
	$fld_value = $aInput['fld_value'];
	$value = $aInput['value'];
	$actiontype = $aInput['actiontype'];
	$aParam = $aInput['aParam'];
	
	if( strlen($aInput['actiontype'])!= 0 )
		$actiontype = "search_";

  $html  = "<span id='dbmng_".$fld."_link_container'>".dbmng_file_create_link($value, $aParam).'</span>';
	$html .= "<div class='dbmng_file_hide_this'><input type='file' name='$actiontype$fld' id='dbmng_$fld' ></div>";

	$html .= '<input class="dbmng_file_text" type="text" name="'.$fld.'_tmp_choosebox" id="dbmng_'.$fld.'_tmp_choosebox" value="'.$value.'" />&nbsp';
	$html .= '<a href="#" id="dbmng_'.$fld.'_tmp_choose">'.t('Choose').'</a>&nbsp';
	$html .= '<a href="#" id="dbmng_'.$fld.'_tmp_remove">'.t('Remove').'</a>';

	$html .= "<script type=\"text/javascript\">dbmng_style_fileform('dbmng_".$fld."');</script>";

	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_form_picture
// ======================
/// This function allow to add the widget picture (html tag) 
/**
\param $aInput			associative array that contains the follows param:
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\param $aParam			parameters array
\return             html
*/
function layout_form_picture( $aInput )
{		
	$fld = $aInput['fld'];
	$fld_value = $aInput['fld_value'];
	$value = $aInput['value'];
	$actiontype = $aInput['actiontype'];
	$aParam = $aInput['aParam'];
	
	if( strlen($aInput['actiontype'])!= 0 )
		$actiontype = "search_";

  $html  = "<span id='dbmng_".$fld."_link_container'>".dbmng_picture_create_link($value, $aParam, "form").'</span><br/>';
	$html .= "<div class='dbmng_file_hide_this'><input type='file' name='$actiontype$fld' id='dbmng_$fld' accept='image/*' ></div>";

	$html .= '<input class="dbmng_file_text" type="text" name="'.$fld.'_tmp_choosebox" id="dbmng_'.$fld.'_tmp_choosebox" value="'.$value.'" />';
	$html .= '<a href="#" id="dbmng_'.$fld.'_tmp_choose">'.t('Choose').'</a>&nbsp';
	$html .= '<a href="#" id="dbmng_'.$fld.'_tmp_remove">'.t('Remove').'</a>';

	$html .= "<script type=\"text/javascript\">dbmng_style_fileform('".$fld."');</script>";

	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_form_checkbox
// ======================
/// This function allow to add the widget checkbox (html tag) 
/**
\param $aInput			associative array that contains the follows param:
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\return             html
*/
function layout_form_checkbox( $aInput )
{
	$fld = $aInput['fld'];
	$fld_value = $aInput['fld_value'];
	$value = $aInput['value'];
	$actiontype = $aInput['actiontype'];

	if( strlen($aInput['actiontype'])!= 0 )
		$actiontype = "search_";
			
	$html = "<input class='dbmng_checkbox' type='checkbox' name='$actiontype$fld' id='dbmng_$fld' ";
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
\param $aInput			associative array that contains the follows param:
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\return             html
*/
function layout_form_select( $aInput )
{
	$fld = $aInput['fld'];
	$fld_value = $aInput['fld_value'];
	$value = $aInput['value'];
	$actiontype = $aInput['actiontype'];
	$aParam = $aInput['aParam'];

	$placeholder = "";
	if(isset($aParam['hide_label']))
		{
			$placeholder = $fld_value['label'];
		}

	if( strlen($aInput['actiontype'])!= 0 )
		$actiontype = "search_";
			
	$do_update = false;
	if( !is_null($value) )
		$do_update = true;
	
	if( $fld_value['widget'] == "select" )
		{
			$aVoc = array();
			$aVoc = $fld_value['voc_val'];
		} 
	$html = "<select name='$actiontype$fld' id='dbmng_$fld'  ".layout_get_nullable($fld_value)." >\n";
	$html .= "<option>".$placeholder."</option> \n";	
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
// layout_form_multiselect
// ======================
/// This function allow to add the widget select (html tag) 
/**
\param $aInput			associative array that contains the follows param:
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\return             html
*/
function layout_form_multiselect( $aInput )
{
	$fld = $aInput['fld'];
	$fld_value = $aInput['fld_value'];
	$value = $aInput['value'];
	$actiontype = $aInput['actiontype'];

	if( strlen($aInput['actiontype'])!= 0 )
		$actiontype = "search_";
			
	$do_update = false;
	if( !is_null($value) )
		$do_update = true;
	
	$aVoc = array();
	$aVoc = $fld_value['voc_val'];

	$sKey = "";
	foreach($aVoc as $voc1)
		{
			foreach($aVoc[$voc1["key"]]["vals"] as $voc2)
				{
					foreach($aVoc[$voc1["key"]]["vals"][$voc2["key"]]["vals"] as $voc3)
						{
							if( $voc3["key"] == $value )
								{
									if( isset($voc1["key"]) && isset($voc2["key"]) && isset($voc3["key"]) )
										$sKey = "'val1' : ".$voc1["key"].", 'val2' : ".$voc2["key"].", 'val3' : ".$voc3["key"];
								}
						}
				}
		}
	$html = "<select  name='$actiontype$fld' onChange='dbmng_update_multi2()' id='dbmng_".$fld."_res'  ".layout_get_nullable($fld_value)." >\n";
	$html .= "</select><br/>\n";

	$html .= "<select  name='$actiontype$fld' onChange='dbmng_update_multi3()' id='dbmng_".$fld."_res2'  ".layout_get_nullable($fld_value)." >\n";
	$html .= "</select><br/>\n";

	$html .= "<select  name='$actiontype$fld' onChange='dbmng_update_multi()' id='dbmng_".$fld."_res3'  ".layout_get_nullable($fld_value)." >\n";
	$html .= "</select><br/>\n";

	$html .= "\n<script type='text/javascript'>\n";
	$html .= "var aMultiSelectData={'res' : ".json_encode($aVoc).", 'field_name': 'dbmng_".$fld."', ".$sKey."};\n";

	if( !isset($value) )
		{
			$html .= "dbmng_multi1();\n";
		}
	else
		{
			$html .= "dbmng_multi1();\n";
			$html .= "dbmng_update_multi2();\n";
			$html .= "dbmng_update_multi3();\n";
		}
	$html .= "</script>\n";

	return $html;
}

/////////////////////////////////////////////////////////////////////////////
// layout_form_select_nm
// ======================
/// This function allow to add the widget select (html tag) 
/**
\param $aInput			associative array that contains the follows param:
\param $fld					field name
\param $fld_value		field value
\param $value				existing value
\return             html
*/
function layout_form_select_nm( $aInput )
{
	$fld = $aInput['fld'];
	$fld_value = $aInput['fld_value'];
	$value = $aInput['value'];
	$actiontype = $aInput['actiontype'];

	if( strlen($aInput['actiontype'])!= 0 )
		$actiontype = "search_";
			
	$html='';
	$do_update = false;
	if( !is_null($value) )
		$do_update = true;
	
	$aVoc = array();
	$aVoc = $fld_value['voc_val'];

	//echo '|'.$value.'|';
	$outtype = 'checkbox';
	if( $outtype == 'select' )
		{
			$html = "<select  multiple='multiple' name='".$actiontype.$fld."[]' id='dbmng_$fld'  ".layout_get_nullable($fld_value)." >\n";
			$html .= "<option/> \n";	
			//$nLen  = count($aVoc);
			
			foreach ( $aVoc as $vocKey => $vocValue )
				{
					$s = "";
					$expl=explode('|', $value);
					if($do_update && in_array($vocKey ,  $expl))
						{
							$s = " selected='true' ";
						}
				
					$html .= "<option $s value='" . $vocKey . "'>" . $vocValue . "</option> \n";	
				}
			$html .= "</select>\n";
		}
	elseif( $outtype == 'checkbox' )
		{
			$html .= "<ul class='dbmng_checkbox_ul'>";
			foreach ( $aVoc as $vocKey => $vocValue )
				{
					$s = "";
					$expl=explode('|', $value);
					if($do_update && in_array($vocKey ,  $expl))
						{
							$s = " checked='true' ";
						}
				
					$html .= "<li><input class='dbmng_checkbox' type='checkbox' name='".$fld."[]' $s value='" . $vocKey . "'/>" . $vocValue . "</li> \n";	
				}
			$html .= "</ul>";
		} 
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
				$html .= "<td><input type='text' name='$fld' id='dbmng_$fld' placeholder='" . t("Search") . " " . t($fld_value['label']) . "' /></td>\n";
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
	if( isset($fld_value['skip_in_tbl']) ) 
		{
			if($fld_value['skip_in_tbl'] == 1)
				{
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
	$nDel = 1;	$nUpd=1; 	$nDup=1; $nPrt_rec=1;
	// get user function parameters
	if( isset($aParam['user_function']) )
		{
		  $nUpd = (isset($aParam['user_function']['upd']) ? $aParam['user_function']['upd'] : 1 );
		  $nDel = (isset($aParam['user_function']['del']) ? $aParam['user_function']['del'] : 1 );
		  $nDup = (isset($aParam['user_function']['dup']) ? $aParam['user_function']['dup'] : 1 );				
		  $nPrt_rec = (isset($aParam['user_function']['prt_rec']) ? $aParam['user_function']['prt_rec'] : 1 );				
		}
	
	$html = "";
	$hv   = prepare_hidden_var($aParam);
	if( true )
		{
			if( $nDel == 1 )
				{
					$jsc = "return confirm('".t('Are you sure?')."')";
					$html .= '<a class="dbmng_delete_button" onclick="'.$jsc.'" href="?act=del&amp;tbln=' . $aForm['table_name'] . '&amp;' . $id_record .$hv.'">' . t('Delete') . '</a>' . "&nbsp;";
				}
			$act2="";
			if( isset($_REQUEST['act2']) )
				{
					$act2="&amp;act2=".$_REQUEST['act2'];
					$hv.= dbmng_search_add_hidden($aForm, $aParam, "GET");
				}

			if( $nUpd == 1 ) 
				{
					$html .= "<a class='dbmng_update_button' href='?act=upd".$act2."&amp;tbln=" . $aForm['table_name'] . "&amp;" . $id_record .$hv."'>" . t('Update') . "</a>" . "&nbsp;";
				}
			if( $nDup == 1 )
				$html .= "<a class='dbmng_duplicate_button' href='?act=dup".$act2."&amp;tbln=" . $aForm['table_name'] . "&amp;" . $id_record .$hv."'>" . t('Duplicate') . "</a>" . "&nbsp;";
			if( $nPrt_rec == 1 )
				$html .= "<a class='dbmng_print_rec_button' href='?act=prt_rec".$act2."&amp;tbln=" . $aForm['table_name'] . "&amp;" . $id_record .$hv."' target='_blank'>" . t('PDF') . "</a>" . "&nbsp;";
		}
	else
		{
			$id_rec = str_replace("&","",$id_record);
			$id_rec = str_replace("=","",$id_record);
			$fn = "frm_".$id_rec;
			$html .= "<form name='tableaction' id='".$fn."' action='?' method='post'>";
			$html .= '<input type="hidden" name="actiontype" id="actiontype" />';
			if( isset($_REQUEST['act2']) )
				{
					$html .= '<input type="hidden" name="act2" id="act2" value="'.$_REQUEST['act2'].'"/>';
				}
			$html .= '<input type="hidden" name="tbln" id="tbln" value="'.$aForm['table_name'].'"/>';
			$html .= '<input type="hidden" name="id_record" id="id_record" value=1/>';
			//			echo '<br/><a href="javascript:dbmng_table_getaction(\''.$fn.'\', \'del\')">Delete</a>';
			if( $nDel == 1 )
				{
					$jsc = "return confirm('".t('Are you sure?')."')";
					$html .= '<a class="dbmng_delete_button" onclick="'.$jsc.'" href="javascript:dbmng_table_getaction(\''.$fn.'\', \'del\')">' . t('Delete') . '</a>' . "&nbsp;";
				}
			if( $nUpd == 1 ) 
				$html .= '<a class="dbmng_update_button" href="javascript:dbmng_table_getaction(\''.$fn.'\', \'upd\')">' . t('Update') . "</a>" . "&nbsp;";
			if( $nDup == 1 )
				$html .= '<a class="dbmng_duplicate_button" href="javascript:dbmng_table_getaction(\''.$fn.'\', \'dup\')">' . t('Duplicate') . "</a>" . "&nbsp;";
			if( $nPrt_rec == 1 )
				$html .= '<a class="dbmng_print_rec_button" href="javascript:dbmng_table_getaction(\''.$fn.'\', \'prt_rec\')">' . t('PDF') . "</a>" . "&nbsp;";
		}
		
	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_table_custom_function
// ======================
/// This function allow to add costum function
/**
\param $aParam  	  parameter array
\param $id_record  	id record
\nFlds              number of fields (special case for dbmng_tables & fields)
\return             html
*/
function layout_table_custom_function($aParam, $id_record, $nFlds)
{
	$hv = prepare_hidden_var($aParam);

	$html = "&nbsp;";
	if(isset($aParam['custom_function']))
		{
			foreach($aParam['custom_function'] as $aCustom )								
				{	
					if( $aCustom['custom_variable'] == 'show_add_fields' )
						{
							if( $nFlds != -1 )
								{
									if( $nFlds <=1 )
										{
											$html.="<a href='?$aCustom[custom_variable]=on&$id_record$hv'>$aCustom[custom_label]</a> &nbsp;";
										}
								}
						}
					else
						{
							$html.="<a href='?$aCustom[custom_variable]=on&$id_record$hv'>$aCustom[custom_label]</a> &nbsp;";
						}
				}
		}
		//$html = substr($html, 0, -2);
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
		$html .= "<a class='dbmng_insert_button' href='?act=ins&amp;tbln=" . $aForm['table_name'] . $hv. "'>" . t('Insert new data') . "</a><br />\n";
	
	return $html;
}


/////////////////////////////////////////////////////////////////////////////
// layout_table_export
// ======================
/// This function allow to add the link "PDF"
/**
\param $aForm  			metadata table array 
\param $aParam  	  parameter array
\return             html
*/
function layout_table_export($aForm, $aParam)
{
  // Initialization of user function variable
  $nPrt_tbl=1;
	if( isset($aParam['user_function']) )
	  $nPrt_tbl = (isset($aParam['user_function']['prt_tbl']) ? $aParam['user_function']['prt_tbl'] : 1 );				

	$hv = prepare_hidden_var($aParam);
	
	$html = "";
	if( $nPrt_tbl == 1)
		$html .= "<a class='dbmng_print_rec_button' href='?act=prt_tbl&amp;tbln=" . $aForm['table_name'] .$hv."' target='_blank'>" . t('PDF') . "</a>" . "&nbsp;";
	
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
	$recs = 0;
	$pages = 1;
	if( isset($_GET['pages']) )
		{
			$pages = $_GET['pages'];
			$_SESSION[$aForm['table_name'].'_pages'] = $pages;
		}
	else if( isset($_SESSION[$aForm['table_name'].'_pages']) )
		{
			$pages = $_SESSION[$aForm['table_name'].'_pages'];
		}
	
	$page_recs = dbmng_num_rows($result);
	if( isset($aParam['tbl_navigation']) )
		$page_recs = $aParam['tbl_navigation'];
		
	$html ="";
	// write BODY content 
	$html .= "<tbody>\n";
	// print_r($result->fetchAllAssoc("id_gallery"));

	foreach ($result as $record) 
		{
			$recs++;
			if( ($pages == 1 && $recs <= $page_recs ) || ($pages != 1 && $recs > $page_recs*($pages-1) && $recs<= $page_recs *$pages) )
				{
					// table value
					$html .= "<tr>";
					
					$pkval = "";
					foreach ( $aForm['fields'] as $fld => $fld_value )
						{
							if(	dbmng_check_is_pk($fld_value) )
								{
									$pkval = $record->$fld;
								}
						}



					//get the query results for each field
					foreach ( $aForm['fields'] as $fld => $fld_value )
						{
							//echo '!'.$record->$fld.'!'.$fld.'<br/>';
							if( layout_view_field_table($fld_value) )
								{
									if(isset($record->$fld))
										{
											$value=dbmng_value_prepare_html($fld_value, $record->$fld, $aParam, "table");
											$html.= "<td class='dbmng_field_$fld'>".$value."</td>";
										}
									else{//TODO: add a comma separated list if widget==multi
										if( isset($fld_value['widget']) )
											{
												if($fld_value['widget'] == 'select_nm')
													{
														$aNM = $fld_value['voc_nm'];
														$val = "";
														/*
														if( $record->id_c_resource == 5 ) //$fld == "id_c_resource_c_language" )
															{
															echo "<br/>".$fld . ": " . $fld_value['widget']." ".$pkval."<br/>";
															print_r($aNM);
															}
															*/
		
														$value='';												
														if(isset($aNM[$pkval])){
															$value=dbmng_value_prepare_html($fld_value, $aNM[$pkval], $aParam, "table");
														}
														$html.= "<td class='dbmng_field_$fld'>".$value."</td>";
													}
												else
													{
														$html.= "<td class='dbmng_field_$fld'>&nbsp;</td>";		
													}
											}
										else
											{
												$html.= "<td class='dbmng_field_$fld'>&nbsp;</td>";		
											}
									}
		
								}
						}
		
					// available functionalities
					$html .= "<td class='dbmng_functions'>";
		
					$pkfield = "";
					$id_record = "";
					foreach ( $aForm['fields'] as $fld => $fld_value )
						{
							if(dbmng_check_is_pk($fld_value) )
								{
									$pkfield = $fld;
									$id_record .= $fld . "=" . $record->$fld . "&";
								}
						}
					
		
					$html .= layout_table_action( $aForm, $aParam, $id_record );
					
					$nFlds = -1;
					if(isset($aParam['custom_function']))
						{
							foreach($aParam['custom_function'] as $aCustom )								
								{	
									if( $aCustom['custom_variable'] == 'show_add_fields' )
										{
											$nFlds = $record->fld;
										}
								}
						}
					$html .= layout_table_custom_function($aParam, $id_record, $nFlds);
		
					$html .= "</td>\n";
					
					$html .= "</tr>\n";
				}
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
	if( isset($aParam['tbl_navigation']) )
		if( dbmng_num_rows($result) > $aParam['tbl_navigation'] )
			$html .= layout_table_navigation($result, $aForm, $aParam);

	//$html .= layout_table_insert($aForm, $aParam);
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
	$html .= layout_table_export($aForm, $aParam);
	$html .= $add_js;
	
	return $html;
}

function layout_table_navigation($result, $aForm, $aParam)
{
	$tbl = $aForm['table_name'];
	$link = "";
	if( isset($_GET['tbl']) )
		{
			if( $tbl == $_GET['tbl'] )
				{
					foreach( $_GET as $k => $v )
						{
							if( $k != "q" )
								{
									$link .= $k."=".$v."&";
								}
						}
				}
		}
	else
		{
			$link = "tbl=".$tbl."&";
		}
	//print_r($_GET);
	
	$paging = "";
	if( isset($aParam['tbl_navigation']) )
		{
			$pag = 1;
			if( isset($_GET['pages'] ) )
				{
					$pag = $_GET['pages'];
				}
			else if( isset($_SESSION[$aForm['table_name'].'_pages']) )
				{
					$pag = $_SESSION[$aForm['table_name'].'_pages'];
				}
			
			$recs   = dbmng_num_rows($result);
			$pages = ceil($recs / $aParam['tbl_navigation']);
			for( $i = 1; $i <= $pages; $i++ )
				{
					if( $i == $pag )
						$paging .= "<a href='?".$link."pages=".$i."'><b>".$i."</b></a> "; //?tbl=".$tbl."
					else
						$paging .= "<a href='?".$link."pages=".$i."'>".$i."</a> "; //?tbl=".$tbl."
				}
		}
	return $paging;
}

/////////////////////////////////////////////////////////////////////////////
// layout_array_print
// ======================
/// This function print on the screen the an array
/**
\param $aTbl associative array 
\param $bDebug  optional parameter: default false 
\param $aParam  	  parameter array
\return             html
*/
function layout_array_print($aTbl, $bDebug=false)
{
	$html = "";
	if( $_SERVER['SERVER_NAME'] == 'localhost' && $bDebug )
		{
			$colcnt = $aTbl['colCount'];
			$rowcnt = $aTbl['rowCount'];
			$html .= "<div class='dbmng_debug'>";
			$html .= "<p style='margin-left:20px;color:magenta;font-size:11px;font-family:\"courier new\"'>rowCount: ".$aTbl['rowCount']."</p>";
			$html .= "<p style='margin-left:20px;color:magenta;font-size:11px;font-family:\"courier new\"'>colCount: ".$aTbl['colCount']."</p>";
			$html .= "<p style='margin-left:20px;color:magenta;font-size:11px;font-family:\"courier new\"'>Use: aTbl['data'][0][1] = ".$aTbl['data'][0][1]."</p>";
			$html .= "<table border='1' cellspacing='0' cellpadding='0' style='margin-left:20px;color:magenta;font-size:11px;font-family:\"courier new\"'>";
			$html .= "<thead><tr style='background-color:#F0F0F0'>";
			$html .= "<th style='text-align:center'>[-]</th>";
			for( $nC = 0; $nC <= $colcnt-1; $nC++ )
				{
					$html .= "<th style='text-align:center'>[".$nC."] ".$aTbl['header'][$nC]."</th>";
				}
			$html .= "</tr></thead>";
			for( $nR = 0; $nR <= $rowcnt-1; $nR++ )
				{
					$html .= "<tr>";
					$html .= "<td style='background-color:#F0F0F0;text-align:right'>[".$nR."]</td>";
					for( $nC = 0; $nC <= $colcnt-1; $nC++ )
						{
							$html .= "<td align='right'>".$aTbl['data'][$nR][$nC]."</td>";
						}
					$html .= "</tr>";
				}
			$html .= "</table>";
			$html .= "</div>";
		}
	
	return $html;
}

/////////////////////////////////////////////////////////////////////////////
// layout_debug
// ======================
/// This function print on the screen debug information
/**
\param $text debug text to be printed
\param $bDebug  optional parameter: default false 
\return             html
*/
function layout_debug($text, $bDebug=false)
{
	$html = "";
	if( $_SERVER['SERVER_NAME'] == 'localhost' && $bDebug )
		{
			$html .=  "<div class='dbmng_debug'>$Text</div>";
		}
	return $html;
}
?>
