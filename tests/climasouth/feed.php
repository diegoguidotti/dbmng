<?php
include_once "sites/all/libraries/dbmng/dbmng.php";

	echo "prova xml [mm]";
	header("Content-type: text/xml; charset=utf-8");
	
	$rss = '<?xml version="1.0" encoding="utf-8"?>';
	$rss .= '<rss version="2.0">';
	$rss .= '<channel>';
	$rss .= '<title>ClimaSouth feed</title>';
	$rss .= '<link>http://www.climasouth.eu/</link>';
	$rss .= '<description>Add small description</description>';
	
	$res = dbmng_query("select * from c_news order by date_from desc ", array());

	foreach( $res as $rec )
		{
			$place = $rec->place;
			$title = $rec->title;
			$date_from = new DateTime($rec->date_from);
			$date_to = $rec->date_to;
			$node = $rec->linked_page;
			if(isset($date_to))
				$date_to = new DateTime($rec->date_to);

			$days = $date_from->format("d");
			$months = $date_from->format("F");
			$year = $date_from->format("Y");
			
			if( isset($date_to) )
				{
					$date_to = new DateTime($rec->date_to);
					$days .= "-".$date_to->format("d");
					
					if( $months != $date_to->format("F") )
						$months .= " ".$date_to->format("F");

					if( $year != $date_to->format("Y") )
						$months .= " ".$date_to->format("Y");
				}
			$date = $days." ".$months." ".$year;
		
			$rss .= '<item>';
			$rss .= '<title>'.$title.'</title>';
			$rss .= '<link>'.l($title,$node).'</link>';
			$rss .= '<guid>'.l($title,$node).'</guid>';
			$rss .= '<pubDate>Wed, 27 Nov 2013 15:17:32 GMT</pubDate>';
			$rss .= '<description>'.strtoupper($place) . " - " . $date.'</description>';
			$rss .= '</item>';
		}
	$rss .= '</channel>';
	$rss .= '</rss>';
	
	echo $rss;
	
?>