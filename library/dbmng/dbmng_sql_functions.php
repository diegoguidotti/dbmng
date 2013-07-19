<?php
include_once DBMNG_LIB_PATH."dbmng_cfg.php";

function dbmng_query($sql, $var=null)
{

	if(is_null($var))
		{
			$callers=debug_backtrace();
			trigger_error("Warning! execute dbmng query with no array (line ".$callers[1]['line']." of ".$callers[1]['file']." )", E_USER_WARNING);

		}
	switch(DBMNG_CMS)
	{
		case "none": // to be developed
			switch( DBMNG_DB )
			{
				case "pdo":

					try 
						{
							$sConnection = "mysql:dbname=".DBMNG_DB_NAME.";host=".DBMNG_DB_HOST.";charset=utf8";
							$link = new PDO($sConnection, DBMNG_DB_USER, DBMNG_DB_PASS);
							$link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
							$link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					

						  $res0 = $link->prepare($sql);
						  $res0->execute($var);
							
							//Temporary Fix: you can not fetch data after UPDATE INSERT and DELETE 
							if(startsWithL($sql,"update") || startsWithL($sql,"insert") || startsWithL($sql,"delete") )
								{
									$res=$res0;
								}
							else
								{
									$res=$res0->fetchAll(PDO::FETCH_CLASS);									
								}
						}	
					catch( PDOException $Exception ) {
						// PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
						// String.
						echo ('PDO Exception: '.$Exception->getMessage( ).'<br/>');
						echo ('Query: '.$sql.'<br/>');
					}
					break;
					
			}
			break;
			
		case "drupal":

			if(isset($var)){
				$res = db_query($sql, $var);
			}
			else 
				{
					$res = db_query($sql);
				}

			break;
	}
/* // per verificare cosa hai in uscita abilita questi commenti
	echo "function <b>dbmng_query</b><br />";
	echo "CMS <b>".DBMNG_CMS."</b><br />";
	echo "DB <b>".DBMNG_DB."</b><br />";
	print_r( $res );
	echo "<br /><br />";
*/
	return $res;
}

function dbmng_fetch_object($res)
{
	switch( DBMNG_CMS )
	{
		case "none":  // to be developed
			switch( DBMNG_DB )
			{
				case "pdo":
					$fo = $res[0];
					break;
			}
			break;
			
		case "drupal":
			$fo = $res->fetchObject();
			break;
	}
	return $fo;
}

function dbmng_num_rows($res)
{
	switch( DBMNG_CMS )
	{
		case "none":  // to be developed
			switch( DBMNG_DB )
			{
				case "pdo":
					$nr = count($res); //todo
					break;
			}
			break;
			
		case "drupal":
			$nr = $res->rowCount();
			break;
	}
	
	return $nr;
}

function startsWithL($haystack, $needle)
	{
    return !strncmp(strtolower($haystack), $needle, strlen($needle));
	}
?>
