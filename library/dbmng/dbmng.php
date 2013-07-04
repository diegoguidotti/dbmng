<?php

/* We create an array containing all the data needed to make the form
	
		$aFormS = array();
		$aFormS['nome'] = array('label' => 'Name', 'type' => 'text', 'default' => '', 'value' => "Michele");
		$aFormS['eta'] = array('label' => 'Age', 'type' => 'number', 'default' => '', 'value' => null);

    //It has to be modified in:
 		$aForm= array(				
				'table_name' => 'test',
				'primary_key' => 'id_test',
				'fields' => array(
					array(
							'field_name' => 'name',
							'field_label' => 'Nome',
							'field_type' => 'text',
						),
					array(
							'field_name' => 'eta',
							'field_label' => 'Et&agrave',								
							'field_type' => 'text'
						),
				)
			);
		*/


function dbmng_get_form_array($id_table){
		$aForm = array();

		$table = db_query("select * from dbmng_tables where id_table=".$id_table." ");
		$aForm['table_name']= $table->fetchObject()->table_name;


		$aFields=array();
		$fields = db_query("select * from dbmng_fields where id_table=".$id_table." order by field_order ASC");
		foreach ($fields as $fld)
		{
			$aFields[$fld->field_name] = array('label' => $fld->field_label, 'type' => $fld->id_field_type, 'default' => $fld->default_value, 'value' => null);
		}

		$aForm['fields']=$aFields;
		
		return $aForm;
}

function dbmng_create_table($aForm){
	  $sql = 'select * from ' . $aForm['table_name'];
		$result = db_query($sql);
    
		$html='';
    $html .= getVersion().'<table><tr><th>' . t('id_test') . '</th><th>' . t('name') . '</th><th>' . t('age') . '</th><th>' . t('functions') . '</th></tr>';

		foreach ($result as $record) {
			$html .= '<tr><td>' . $record->id_test . '</td><td>' . $record->nome.'</td><td>' . $record->eta . '</td>';

			$html .= '<td>';
				$html .= '<a href="?delete_id_test=' . $record->id_test.'">' . t('Delete') . '</a>' . '&nbsp;';
				$html .= '<a href="?update_id_test=' . $record->id_test . '">' . t('Update') . '</a>' . '&nbsp;';
				$html .= '<a href="?duplicate_id_test=' . $record->id_test . '">' . t('Duplicate') . '</a>';
			$html .= '</td>';
			
			$html .= '</tr>';
		}
    $html .= '</table>';
		
		$html .= '<a href="?insert_new=1">' . t('Insert new data') . '</a><br />';
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
function dbmng_create_form($aForm) {
		$html='';
		$html .= "<form method='POST' action='?' >\n";
		foreach ( $aForm['fields'] as $x => $x_value )
		{
			$html .= t($x_value['label']);
			$html .= "<input name='".$x."' ";
			$html .= "type='input' ";
			if( $x_value['value'] == null )
				$html .= "value='" . $x_value['default'] . "' ";
			else
				$html .= "value='" . $x_value['value'] . "' ";
			$html .= "><br />\n";
						
		}
		$html .= "<input type='submit' value='" .t('Insert') . "' />\n";
    $html .= "</form>\n";
		return $html;
}

function getVersion(){
	return "0.0.1";
}

?>
