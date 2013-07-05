<?php
/*
Convention to be used in code:

insert:    "ins_" . $aForm['table_name']
update:    "upd_" . $aForm['table_name']
delete:    "del_" . $aForm['table_name']
duplicate: "dup_" . $aForm['table_name']

Associative array with all the characteristics to manage a table

 		$aForm= array(				
				'table_name' => 'test',
				'primary_key' => 'id_test',
				'fields' => array(
					array(
							'field_name' => 'name',
							'label' => 'Nome',
							'field_type' => 'text',
						),
					array(
							'field_name' => 'eta',
							'label' => 'Et&agrave',								
							'field_type' => 'text'
						),
				)
			);
*/


function getVersion(){
	return "0.0.2 MM";
}


/////////////////////////////////////////////////////////////////////////////
// dbmng_get_form_array
// ======================
/// Default action for the function
/**
\param $id_table  table id
\return           HTML generated code
*/
function dbmng_get_form_array($id_table){
		$aForm = array();

		$table = db_query("select * from dbmng_tables where id_table=".$id_table." ");
		$aForm['table_name']= $table->fetchObject()->table_name;


		$aFields=array();
		$fields = db_query("select * from dbmng_fields where id_table=".$id_table." order by field_order ASC");
		foreach ($fields as $fld)
		{
			// [MM 2013-07-05] update dbmng_fields adding pk field with type integer possible value 0, 1
			if(strpos($fld->field_name, "id_") !== false )
				$aForm['primary_key'] = "id_test";

		//	if ( true )
		//	{
		//		if(strpos($fld->field_name, "id_") !== 0 )
		//			$aForm['primary_key'] = $fld->field_name;
		//	}
		//	else
		//	{
		//		if( $fld->pk == 1 )
		//			$aForm['primary_key'] = $fld->field_name;
		//	}
			$aFields[$fld->field_name] = array('label' => $fld->field_label, 'type' => $fld->id_field_type, 'default' => $fld->default_value, 'value' => null);
		}

		$aForm['fields']=$aFields;
		
		return $aForm;
}


/////////////////////////////////////////////////////////////////////////////
// dbmng_create_table
// ======================
/// Default action for the function
/**
\param $aForm  		Associative array with all the characteristics
\return           HTML generated code
*/
function dbmng_create_table($aForm){
	  $sql = 'select * from ' . $aForm['table_name'];
		$result = db_query($sql);
    
		$html = "<h1>" . $aForm['table_name'] . "</h1>\n";
		
		$html .= "<table>";
		$html .= "<tr>";
		foreach ( $aForm['fields'] as $x => $x_value )
		{
			$html .= "<th>" . $x_value['label'] . "</th>";
		}
		$html .= "<th>" . t('functions') . "</th></tr>\n";
		
		foreach ($result as $record) {
			// table value
			$html .= "<tr><td>" . $record->id_test . "</td><td>" . $record->nome. "</td><td>" . $record->eta . "</td>";
			
			// available functionalities
			$html .= "<td>";
				$html .= "<a href='?del_" . $aForm['table_name'] . "=" . $record->id_test ."'>" . t('Delete') . "</a>" . "&nbsp;";
				$html .= "<a href='?upd_" . $aForm['table_name'] . "=" . $record->id_test ."'>" . t('Update') . "</a>" . "&nbsp;";
				$html .= "<a href='?dup_" . $aForm['table_name'] . "=" . $record->id_test ."'>" . t('Duplicate') . "</a>" . "&nbsp;";
			$html .= "</td>\n";
			
			$html .= "</tr>";
		}
    $html .= "</table>\n";
		
		$html .= "<a href='?ins_" . $aForm['table_name'] . "'>" . t('Insert new data') . "</a><br />";
		return $html;
}

/* we need to make the update
	// form per inserimento e modifica
    if( isset($_GET['insert_new']) || isset($_GET['update_id_test']) ){
	    $html .= '<form method="POST" action="?" >';
			$html .= t('Name') . ': <input name="nome" value="' . $nome_val . '" /><br />';
			$html .= t('Age') . ': <input name="eta" value="' . $eta_val . '" /><br />';
			if($update_id_test!=''){
				$html .= '<input type="hidden" name="update_record" value="'.$update_id_test.'" />';
				$html .= '<input type="submit" value="'. t('Update') .'" />';
			}
			else{
				$html .= '<input type="submit" value="' .t('Insert') .'" />';
			}
	    $html .= '</form>';
    }
		
		$html .= "<br /><br />\n";

*/


/////////////////////////////////////////////////////////////////////////////
// dbmng_create_form
// ======================
/// Default action for the function
/**
\param $aForm  		Associative array with all the characteristics
\return           HTML generated code
*/
function dbmng_create_form($aForm) 
{
	$html="";
	$do_update=false;
	
	if(isset($_GET["upd_" . $aForm['table_name']]))
		{
			$do_update=true;
		}

	if ( isset($_GET["ins_" . $aForm['table_name']]) || $do_update )
		{
      if( $do_update )
		    {
					$id_update = $_GET["upd_" . $aForm['table_name']];

					$sql       = "select * from " . $aForm['table_name'] . " where " . $aForm['primary_key'] . "=" . intval($id_update);
					$result    = db_query($sql );		
					$vals      = $result->fetchObject();
				}

			$html .= "<form method='POST' action='?' >\n";
			foreach ( $aForm['fields'] as $x => $x_value )
				{
					$html .= t($x_value['label']);
					$html .= "<input name='" . $x . "' ";
					$html .= "type='text' ";
					
					//if( $x_value['value'] == null )
				  //	$html .= "value='" . $x_value['default'] . "' ";
					
					if($do_update)
						{
							$html .= "value='" . $vals->$x . "' ";
						}

					$html .= "><br />\n";
				}

			if( isset($_GET["upd_" . $aForm['table_name']] ))
				{
					$html .= "<input type='hidden' name='update_record' value='" . $_GET["upd_" . $aForm['table_name']] . "' />\n";
					$html .= "<input type='submit' value='". t('Update') ."' />\n";
				}
			else
				{
					$html .= "<input type='submit' value='" . t('Insert') . "' />\n";
				}

	    $html .= "</form>\n";
		}
		return $html;
}


/*
function dbmng_upd_func($aForm)
{
	$table = $aForm['table_name'];
	// $nome_val       = '';
	// $eta_val        = '';
	// $update_id_test = '';

	// [MM 2013-07-05] update dbmng_fields adding pk field with type integer possible value 0, 1
	foreach ( $aForm['fields'] as $x => $x_value )
	{
	if ( true )
	{
		if(strpos($x, "id_") > 0 )
			$pk = $x;
	}
	else
	{
		if( $fld->pk == 1 )
			$pk = $x_value['primary_key'];
	}

	if(isset("upd_" . $aForm['table_name']))
	{
		$sql    = "select * from " . $table . " where " . $pk . "=:" . $pk;
		$result = db_query($sql, array(":" . $pk => intval($_REQUEST["upd_" . $aForm['table_name']])) );
    foreach ($result as $record) {
         $nome_val       = $record->nome;
         $eta_val        = $record->eta;
         $update_id_test = $record->id_test;
		}
	}
	
}
*/

?>
