<?php
	$html ='';
	if( $_POST['upd_file'])
		{	
			if ($_FILES["file"]["error"] > 0)
			  {
			  $html .= "Error: " . $_FILES["file"]["error"] . "<br>";
			  }
			else
			  {
			  $html .= "Upload: " . $_FILES["file"]["name"] . "<br>";
			  $html .= "Type: " . $_FILES["file"]["type"] . "<br>";
			  $html .= "Size: " . round($_FILES["file"]["size"] / 1024, 2) . " kB<br>";
			  $html .= "Temp file: " . $_FILES["file"]["tmp_name"] ."<br />";
			  
			  if (file_exists("upload/" . $_FILES["file"]["name"]))
			  	{
			  		$html .= $_FILES["file"]["name"] . " already exists. ";
			  	}
			  else
			  	{
					  move_uploaded_file($_FILES["file"]["tmp_name"], "upload/" . $_FILES["file"]["name"]);
					  $html .= "Stored in: " . "upload/" . $_FILES["file"]["name"];
			  	}
			  }
		}	
	else
		{
			$html .= "<html>";
			$html .= "<body>";
		
			$html .= '<form action="?upd_file" method="post" enctype="multipart/form-data">';
			$html .= '<label for="file">Filename:</label>';
			$html .= '<input type="file" name="file" id="file"><br>';
			$html .= '<input type="input" name="filename", value=""><br>'; 
			$html .= '<input type="submit" name="submit" value="Submit">';
			$html .= '</form>';
		
			$html .= "</html>";
			$html .= "</body>";
	
		}
	echo $html;
?>