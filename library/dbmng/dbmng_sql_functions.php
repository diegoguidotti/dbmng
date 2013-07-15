<?php
include_once "sites/all/library/dbmng/dbmng_cfg.php";

function dbmng_query($sql)
{
	switch(DBMNG_CMS)
	{
		case "none": // to be developed
			switch( DBMNG_DB )
			{
				case "mysql":
					$link = mysql_connect(DBMNG_DB_HOST, DBMNG_DB_USER, DBMNG_DB_PASS);
					mysql_select_db(DBMNG_DB_NAME, $link);
					$res = mysql_query($sql, $link);
					break;
					
				case "postgres":
					break;
			}
			
		case "drupal":
			$res = db_query($sql);
			break;
	}
	return $res;
}

function dbmng_fetch_object($res)
{
	switch( DBMNG_CMS )
	{
		case "none":  // to be developed
			switch( DBMNG_DB )
			{
				case "mysql":
					$fo = mysql_fetch_object($res);
					break;
					
				case "postgres":
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
	switch(DBMNG_CMS)
	{
		case "none":  // to be developed
			switch( DBMNG_DB )
			{
				case "mysql":
					$nr = mysql_num_rows($res);
					break;
					
				case "postgres":
				break;
			}
			
		case "drupal":
			$nr = $res->rowCount();
			break;
	}
	
	return $nr;
}
?>