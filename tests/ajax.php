<?php

	define( 'DBMNG_LIB_PATH'    , '../library/' );
	define( 'DBMNG_CMS'         , 'none' );
	define( 'DBMNG_DB'          , 'pdo' );

	//0.include the library
	include(DBMNG_LIB_PATH.'dbmng/dbmng.php');
	include(DBMNG_LIB_PATH.'dbmng/dbmng_standalone.php');



	$ok=false;
	$json="";

	$login=true;
	//TODO check login
	//TODO check that the uid can access the given id_table
	

	if($login){
		if(isset($_REQUEST['id_table'])) {
			$aForm = dbmng_get_form_array($_REQUEST['id_table']); 

			$table = $aForm['table_name'];
			$ok=true;
		}

		if(isset($_POST['get_records'])){		
			//$json['records']=Array();
			echo dbmng_get_js_array($aForm, Array());
			//echo "{'records':[]}";
		}
		else {

			if(isset($_POST['inserted']))
				$aIns = json_decode($_POST['inserted'], true);

			if(isset($_POST['deleted']))
				$aDel = json_decode($_POST['deleted'], true);

			if(isset($_POST['updated']))
				$aUpd = json_decode($_POST['updated'], true);
	
	
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
						$sql_ins = "insert into $table  ";
						$json['inserted']=Array();

						foreach($aIns as $index => $val)
							{
								$sFld = "";
								$sVal = "";
								$aVal = array();
								foreach( $aForm['fields'] as $fld => $fld_value )
									{
										if( $fld != $pk )
											{
												if(isset($val['record'][$fld])){
													$sFld .= $fld .", ";
													$sVal .= ":$fld, ";
													$aVal = array_merge( $aVal, array(":".$fld => $val['record'][$fld]) );
												}
												else{
													//$err_msg .= "Field ".	$fld ." not found in val ";																		
												}
											}
									}
								$sVal = substr( $sVal, 0, strlen($sVal)-2 );
								$sFld = substr( $sFld, 0, strlen($sFld)-2 );
								$sql = $sql_ins . "(" . $sFld . ") VALUES (" . $sVal . ");";						
								$ret = (dbmng_query( $sql, $aVal));
								if($ret['ok']==1){
									$json['inserted'][$index]['ok']=1;
									$json['inserted'][$index]['inserted_id']=$ret['inserted_id'];
								}
								else{
									$json['inserted'][$index]['ok']=0;				
									$json['inserted'][$index]['error']=$ret['error'][2];				
								}

							}
					}


				// ********** UPDATE ********** //
				if( isset($aUpd) )
					{
						$sql = "";
						$sql_ins = " UPDATE $table ";
						$json['updated']=Array();

						foreach($aUpd as $index => $val)
							{						
								$sVal = "";
								$where = "";
								$aVal = array();
								foreach( $aForm['fields'] as $fld => $fld_value )
									{
										if( $fld != $pk )
											{
												if(isset($val['record'][$fld])){
													$sVal .= $fld."= :$fld, ";
													$aVal = array_merge( $aVal, array(":".$fld => $val['record'][$fld]) );
												}
												else{
													//there are a missing fields in update
													//it may be thata field will not be editable
													//$err_msg .= "Field ".	$fld ." not found in val ";																		
												}
											}
										else{
											//TODO: check if $val['record'][$fld] exists
												//IF not exists block the update
											$where = '  '.$fld.'=:'.$fld.' ';
											$aVal = array_merge( $aVal, array(":".$fld => $val['record'][$fld]) );
										}
									}
								$sVal = substr( $sVal, 0, strlen($sVal)-2 );

								$sql = $sql_ins . " SET " . $sVal . " WHERE ".$where."; ";						
								$ret = (dbmng_query( $sql, $aVal));
								if($ret['ok']==1){
									$json['updated'][$index]['ok']=1;							
								}
								else{
									$json['updated'][$index]['ok']=0;				
									$json['updated'][$index]['error']=$ret['error'][2];				
								}

							}
					}

				// ********** DELETE ********** //
				if( isset($aDel) )
					{
						$json['deleted']=Array();

						$sql = "";
				
						$sVal = "";
						$aVal = array();
						$sql_del = "delete from $table where ";
				
						foreach( $aDel as $index => $val )
							{
								//print_r ($val);
								//$sVal = $pk . " = " . $val[$pk] . ";\n";
								$sVal = $pk . " = :" . $pk . ";\n";
								$aVal = array(":".$pk => $val['record'][$pk]);
								$sql = $sql_del . $sVal; 
								$ret = (dbmng_query( $sql, $aVal));
								if($ret['ok']==1){
									$json['deleted'][$index]['ok']=1;
								}
								else{
									$json['deleted'][$index]['ok']=0;				
									$json['deleted'][$index]['error']=$ret['error'][2];				
								}
						
							}
					}

					echo json_encode( $json );
			}
			else{
				echo '{"ok":false}';
			}
		}
	}
	else{
			echo '{"ok":false, "msg": "The user is not authorized to login"}';
	}
?>
