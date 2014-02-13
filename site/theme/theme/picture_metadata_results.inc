<?PHP

	$output = "";

	$link = "";
	
	$title = "All metadata";
	$description = "Description not set";
	$license = "License not set";

	foreach($data['results'] as $item){
	
		$link = $item['link'];
		$link_open = false;
	
		$output .= "<p>" . ucfirst(strtolower($item['term'])) . " : <span ";
		
		switch($item['term']){
		
			case "TITLE": $output .= " itemprop='name' property='dc:title' "; $title = $item['node_value']; break;
			case "SUBJECT": $output .= " property='dc:subject' "; break;
			case "DESCRIPTION": $output .= " property='dc:description' "; $description = $item['node_value']; break;
			case "DATE": $output .= " itemprop='dateCreated' property='dc:date' "; break;
			case "TYPE": $output .= " property='dc:type' "; break;
			case "FORMAT": $output .= " property='dc:format' "; break;
			case "LANGUAGE": $output .= " itemprop='inLanguage' property='dc:language' "; break;
			case "LICENSE": $output .= "><a rel='license' itemprop='useRightsUrl' property='dc:rights'"; 
							if(substr($item['node_value'],0,4)=="http"){
							
								$output .= " href='" . $item['node_value'] . "' ";
							
							}
							$link_open = true;
							$license = $item['node_value'];
							break;
			case "CREATOR": $output .= " itemprop='author' property='dc:creator' "; break;
			case "PUBLISHER": $output .= " itemprop='publisher' property='dc:publisher' "; break;
			case "CONTRIBUTOR": $output .= " property='dc:contributor' "; break;
		
		}
		
		$output .= " >" . stripcslashes($item['node_value']);
		
		if($link_open){
		
			$output .= "</a>";
		
		}
		
		$output .= "</span></p>";
	
	}
	
	echo self::display_picture_twitter_header($link, $this->site->get_text($title), $description, $license);
	
	echo "<div about='" . $link . "'>" . $output . "</div>";
	
	echo self::display_footer();
