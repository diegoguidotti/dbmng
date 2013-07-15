<?php

function sql_query($sql)
{
	$res = db_query($sql);
	
	return $res;
}

function sql_fetch_object($res)
{
	$fo = $res->fetchObject();
	
	return $fo;
}
?>