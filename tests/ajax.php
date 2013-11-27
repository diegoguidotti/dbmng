<?php
//include_once "sites/all/libraries/dbmng/dbmng.php";


	$ok=false;
	$err_msg="";

	if(isset($_POST['aForm'])) {
		$aForm = json_decode($_POST['aForm'], true);
		$table = $aForm['table_name'];
		$ok=true;
	}

	if(isset($_POST['inserted']))
		$aIns = json_decode($_POST['inserted'], true);

	if(isset($_POST['deleted']))
		$aDel = json_decode($_POST['deleted'], true);

	
	if($ok) {	
		//print_r( $aForm );
		// ********** GET PRIMARY KEY ********** //
		foreach( $aForm['primary_key'] as $fld => $fld_value )
			{
				$pk = $fld_value;
			}
	
		// ********** INSERT ********** //
		if( isset($aIns) )
			{
				$sql = "";
				$sql_ins = "insert into $table values ";
			
				foreach($aIns as $index => $val)
					{
						$sVal = "";
						$aVal = array();
						foreach( $aForm['fields'] as $fld => $fld_value )
							{
								if(isset($val[$fld])){
									$sVal .= $fld . "= :$fld, ";
									$aVal = array_merge( $aVal, array(":".$fld => $val[$fld]) );
								}
								else{
									$err_msg .= "Field ".	$fld ." not found in val ";																		
								}

								//$sVal .= $fld . "=" . $val[$fld] . ", ";
							}
						$sVal = substr( $sVal, 0, strlen($sVal)-2 );
						$sql = $sql_ins . "(" . $sVal . ");";
						echo $sql;
						//print_r( $aVal );
					}
			}

		// ********** DELETE ********** //
		if( isset($aDel) )
			{
				$sql = "";
				$sql_del = "delete from $table where ";
				$sVal = "";
				$aVal = array();
				foreach( $aDel as $index => $val )
					{
						//$sVal = $pk . " = " . $val[$pk] . ";\n";
						$sVal = $pk . " = :" . $pk . ";\n";
						$aVal = array(":".$pk => $val[$pk]);
						$sql .= $sql_del . $sVal; 
					}
				echo $sql;
				print_r($aVal);
			}

			echo $err_msg;
	}
	else{
		echo '{"ok":false}';
	}
?>
