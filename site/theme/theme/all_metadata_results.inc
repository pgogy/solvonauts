<?PHP

	echo self::display_header();

	foreach($data['results'] as $item){
	
		echo ucfirst(strtolower($item['term'])) . " : " . utf8_decode($item['node_value']);
		
		echo "<br/>";
	
	}

	echo self::display_footer();
