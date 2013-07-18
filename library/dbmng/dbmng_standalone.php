<?php


//Solve the missing t function outside drupal
function t($sString)
	{
		return $sString;
	}


//Used in image management; base_path is a drupal function outside Drupal just use relative path
function base_path()
	{
		return "";
	}

?>
