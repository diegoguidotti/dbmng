<?php
include_once "sites/all/library/dbmng/dbmng.php";

function dbmng_list_table_view() {
$html='';
$result = db_query('SELECT distinct id_table, table_label, table_name FROM dbmng_tables order by table_label ');
	$html.="<ul>";
		foreach ($result as $record) {

			$tn=t($record->table_label);
			if($tn==''){
				$tn=$record->table_name;
			}

			$html.="<li><a href='?tbl=" . $record->id_table . "'>" . $tn . "</li>";
		}
	$html.='</ul>';
return $html;
}

function dbmng() 
{
	drupal_add_css( "sites/all/modules/dbmng/dbmng.css" );

	if( isset($_REQUEST['tbl']) )
	{
		//get the form!!!
		$id_table = $_REQUEST['tbl'];
		$aForm    = dbmng_get_form_array($id_table);

		//the param array stores some custom variable used by the renderer
		//hidden_vars are some hidden variables used by the form creation
		global $user;
		$aParam                            = array();
		$aParam['filters']                 = array();
		$aParam['hidden_vars']             = array();
		$aParam['hidden_vars']['tbl']	     = $_REQUEST['tbl']; //save the table id
		$aParam['hidden_vars']['type_tbl'] = 2; //table type (1: content table; 2: system table)

		//test filter records with a specific uid
		//$aParam['filters']['id_user']	   = $user->uid;       // save the user id
		$aParam['tbl_footer']              = 1;                // allow to add filtering
		$aParam['user_function']['dup']	   = 1;	              // allow to enabled=1 or disabled=0 the duplication function
		$aParam['user_function']['ins']	   = 1;	              // allow to enabled=1 or disabled=0 the insert function
		$aParam['user_function']['upd']	   = 1;                // allow to enabled=1 or disabled=0 the update function
		$aParam['user_function']['del']	   = 1;	              // allow to enabled=1 or disabled=0 the delate function

		//print_r ($aParam['filters']);

		// update record
		dbmng_create_form_update($aForm, $aParam);

		// insert record
		dbmng_create_form_insert($aForm, $aParam);
		
		// delete record
		dbmng_create_form_delete($aForm, $aParam);
		
		// duplicate record
		dbmng_create_form_duplicate($aForm, $aParam);

		$html .= dbmng_create_table($aForm, $aParam);

		$html .= dbmng_create_form($aForm, $aParam);
	}
  return ($html);
}

?>