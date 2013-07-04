<?

//0.include the library
//include('.../lib/dbmng.php');

//1.create an array with all the metadata
$aDBZ= array(				
		'table_name' => 'test',
		'primary_key' => 'id_test',
		'fields' => array(
			array(
					'field_name' => 'name',
					'field_label' => 'Nome',								
				),
			array(
					'field_name' => 'eta',
					'field_label' => 'Et&agrave',								
				),
		)
	);

//2. create the object
//$tab = new DBMng($aDBZ);

//3. the render function will print the HTML interface
//$output = $tab->render();

//4. echo $output (or use in drupal or ther CMS)
//echo $output;

>
