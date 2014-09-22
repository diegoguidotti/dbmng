<?php

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

	$ret=Array();

	if(is_null($var))
		{
			$callers=debug_backtrace();
			trigger_error("Warning! execute dbmng query with no array (line ".$callers[0]['line']." of ".$callers[0]['file']." )", E_USER_WARNING);

		}
	switch(DBMNG_CMS)
	{
		case "none": // to be developed
			

					try 
						{
							$sConnection = DBMNG_DB.":dbname=".DBMNG_DB_NAME.";host=".DBMNG_DB_HOST."";
							if(DBMNG_DB=='mysql')
								$sConnection .= ";charset=utf8";

							//echo $sConnection;
							$link = new PDO($sConnection, DBMNG_DB_USER, DBMNG_DB_PASS);
							$link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
							$link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					
								//echo $sql;
								//print_r ($var);

							$link->beginTransaction();
						  $res0 = $link->prepare($sql);
							if($res0)
								{	
								$res0->execute($var);
								if(startsWithL($sql,"insert"))
									{							
										$id = $link->lastInsertId();	
									}

								$link->commit();
								//echo debug_sql_statement($sql, $var)."<br/>";	

								//Temporary Fix: you can not fetch data after UPDATE INSERT and DELETE 
								if(startsWithL($sql,"update") || startsWithL($sql,"insert") || startsWithL($sql,"delete") )
									{

										$ret=Array();
		
										if(startsWithL($sql,"insert"))
											{							
												$ret['inserted_id']=$id;	
											}

										$ret['res']=$res0;
										$ret['ok']=true;										
										

									}
								else
									{
										$ret=$res0->fetchAll(PDO::FETCH_CLASS);				
									}
							 }
							else
								{
										$ret=Array();
										$ret['ok']=false;
										$ret['error']=$link->errorInfo();	
										$ret['query'] = debug_sql_statement($sql, $var);	

								}
						}	
					catch( PDOException $Exception ) {
						// PHP Fatal Error. Second Argument Has To Be An Integer, But PDOException::getCode Returns A
						// String.
						//echo $Exception->getMessage( );	

						$ret=Array();
						$ret['ok']=false;
						/*$ret['error_pdo']=$link->errorInfo();	*/						
						$ret['error']=$Exception->getMessage( );	
						$ret['query'] = debug_sql_statement($sql, $var);	
						
					
			}
			break;
			
		case "drupal":

			if(isset($var))
				{
				try 
						{
							if(startsWithL($sql,"update") || startsWithL($sql,"insert") || startsWithL($sql,"delete") ) 
								{
									$id = db_query($sql, $var, array('return' => Database::RETURN_INSERT_ID));

									$ret=Array();
									if( startsWithL($sql,"insert") )
										{
											$ret['inserted_id']=$id;	
										}
									$ret['ok']=true;
								}
							else
								{
									$ret = db_query($sql, $var);									
								}
						}
					catch( Exception $Exception ) {
						//echo ('PDO Exception: '.$Exception->getMessage( ).'<br/>');
						//echo ('Query: '.$sql.'<br/>');
						$ret=Array();
						$ret['ok']=false;
						//$ret['error_pdo']=$link->errorInfo();							
						$ret['error']=$Exception->getMessage( );	
						//$ret['query'] = debug_sql_statement($sql, $var);	
						
					}
				}
			else 
				{
					$ret = db_query($sql);
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
	return $ret;
}



function dbmng_transactions($array){

	$ret=Array();
	$ret['ok']=true;

	switch(DBMNG_CMS)
	{
		case "none": // to be developed
					try 
						{
							$sConnection = DBMNG_DB.":dbname=".DBMNG_DB_NAME.";host=".DBMNG_DB_HOST."";
							if(DBMNG_DB=='mysql')
								$sConnection .= ";charset=utf8";

							//echo $sConnection;
							$link = new PDO($sConnection, DBMNG_DB_USER, DBMNG_DB_PASS);
							$link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
							$link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

					
							// First of all, let's begin a transaction
							$link->beginTransaction();

							foreach($array as $a)
								{
									$res0 = $link->prepare($a['sql']);
									if($res0)
										{		
											$res0->execute($a['var']);
										}
									else{
											echo "ERRORE!!!!!!!!!!"	;
									}
								}

							// If we arrive here, it means that no exception was thrown
							// i.e. no query has failed, and we can commit the transaction
							$link->commit();
							} 
							catch (Exception $e) {
									// An exception has been thrown
									// We must rollback the transaction
									$link->rollback();
									
									$ret['ok']=false;
									$ret['error']=$e->getMessage( );	
							}
			break;
			
			case "drupal":

					
							try {
									// First of all, let's begin a transaction
									$transaction = db_transaction();

									foreach($array as $a)
										{
											db_query($a['sql'], $a['var']);
										}

									// If we arrive here, it means that no exception was thrown
									// i.e. no query h$link->commit();
							} catch (Exception $e) {
									// An exception has been thrown
									// We must rollback the transaction
									 $transaction->rollback();
									
									$ret['ok']=false;
									$ret['error']=$Exception->getMessage( );	
							}

			break;
	}
	return $ret;
	

}



/////////////////////////////////////////////////////////////////////////////
// dbmng_query2array
// ======================
/// Returns an associative array with the result of an executed query
/// - data: contain the result of the query execution
/// - header: contain the field name
/// - rowCount: contain the number of rows
/// - colCount: contain the number of columns
/**
\param 						$sql  query string
\param 						$var  An array of values to substitute into the query
\return  Returns an array with the result of the query
*/
function dbmng_query2array($sql, $aVal)	//dbmng_query($sql, $var=null)
{
	$res = dbmng_query($sql, $aVal);
	$colcnt = dbmng_num_columns($res);
	$rowcnt = dbmng_num_rows($res);
	$aTbl  = array();
	$aTblD = array();
	$aTblH = array();
	foreach( $res as $rec )
		{
			for( $nC = 0; $nC<=$colcnt-1; $nC++ )
				{
					$keys=array_keys((array)$rec);
					$aRow[$nC] = $rec->$keys[$nC];
					$aTblH[$nC] = $keys[$nC];
				}
			$aTblD[] = $aRow;
		}
	$aTbl['data'] = $aTblD;
	$aTbl['header'] = $aTblH;
	$aTbl['rowCount'] = $rowcnt;
	$aTbl['colCount'] = $colcnt;

	return $aTbl;
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
			
			if(count($res)>0){
				$fo = $res[0];
			}
			else{
				$fo=null;
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

	$nr=0;

	switch( DBMNG_CMS )
	{
		case "none":  // to be developed
			
			$nr = count($res); //todo
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
			
			$nr = $res->columnCount(); //todo
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
