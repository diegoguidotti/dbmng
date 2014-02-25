<?php

	define( 'DBMNG_LIB_PATH'    , '../library/' );
	define( 'DBMNG_CMS'         , 'none' );
	define( 'DBMNG_DB'          , 'pdo' );

	//0.include the library
	include(DBMNG_LIB_PATH.'dbmng/dbmng.php');
	include(DBMNG_LIB_PATH.'dbmng/dbmng_standalone.php');



	$ok=false;
	$json="";

	

	if(isset($_REQUEST['do_login'])){

		if(isset($_REQUEST['name']) && isset($_REQUEST['pass'])){	

			$q="select uid, name, mail from dbmng_users WHERE name=:name and pass=md5(:pass)";
			$res = dbmng_query($q, array(":name"=>$_REQUEST['name'], ":pass"=>$_REQUEST['pass']));
			$ob = dbmng_fetch_object($res);		
			if($ob==null){
				echo '{"login":false, "msg":"'.t("Unrecognized username or password").'"}';
			}
			else{

				$res = dbmng_query("SELECT * FROM dbmng_role_tables d , dbmng_tables t, dbmng_users_roles ur WHERE ur.uid=:uid AND d.id_table=t.id_table AND d.rid=ur.rid", array(":uid"=>$ob->uid));
				

				echo '{"login":true, "uid":"'.$ob->uid.'", "user_name":'.json_encode($_REQUEST['name']).', "mail":"'.$ob->mail.'" , "table": '.json_encode($res).'}';
			}

		}
		else{
			echo '{"login":false, "msg":"Missing fields"}';
		}
	}
	else if(isset($_REQUEST['do_logout'])){		
		echo '{"login":false, "msg":"To be implemented"}';
	}
	else {
		echo '{"msg":"Command not found"}';
	}
	
?>
