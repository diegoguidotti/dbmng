<?php
include_once DBMNG_LIB_PATH."dbmng_cfg.php";

/////////////////////////////////////////////////////////////////////////////
// dbmng_query
// ======================
/// Executes an arbitrary query string 
/**
\param 						$sql  query string
\param 						$var  An array of values to substitute into the query
\return           A prepared statement object, already executed 
*/
function dbmng_query($sql, $var=null)
{

	if(is_null($var))
		{
			$callers=debug_backtrace();
			trigger_error("Warning! execute dbmng query with no array (line ".$callers[0]['line']." of ".$callers[0]['file']." )", E_USER_WARNING);

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

			if(isset($var))
				{
				try 
						{
							$res = db_query($sql, $var);
						}
					catch( Exception $Exception ) {
						echo ('PDO Exception: '.$Exception->getMessage( ).'<br/>');
						echo ('Query: '.$sql.'<br/>');
						$res = null;
					}
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

	/*
	$tsql = $sql;
	foreach ( $var as $k => $k_value )
		{				
			$tsql = str_replace($k, $k_value, $tsql);
		}
	echo "<br>$tsql</br>";
	*/
	return $res;
}

/////////////////////////////////////////////////////////////////////////////
// dbmng_fetch_object
// ======================
/// Returns the current row of a result set as an object
/**
\param 						$res  A result set identifier returned by dbmng_query
\return           Returns an object with string properties that corresponds to the fetched row 
*/
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

/////////////////////////////////////////////////////////////////////////////
// dbmng_num_rows
// ======================
/// Get number of rows in result 
/**
\param 						$res  A result set identifier returned by dbmng_query
\return           The number of rows in a result set 
*/
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

/////////////////////////////////////////////////////////////////////////////
// dbmng_num_columns
// ======================
/// Get number of columns in result 
/**
\param 						$res  A result set identifier returned by dbmng_query
\return           The number of columns in a result set 
*/
function dbmng_num_columns($res)
{
	switch( DBMNG_CMS )
	{
		case "none":  // to be developed
			switch( DBMNG_DB )
			{
				case "pdo":
					$nr = $res->columnCount(); //todo
					break;
			}
			break;
			
		case "drupal":
			$nr = $res->columnCount();
			break;
	}
	
	return $nr;
}

/////////////////////////////////////////////////////////////////////////////
// startsWithL
// ======================
/// Check if a string (haystack) starts with an other string (needle)
/**
\param 						$haystack
\param 						$needle
\return           boolean
*/
function startsWithL($haystack, $needle)
	{
    return !strncmp(trim(strtolower($haystack)), $needle, strlen($needle));
	}
?>
