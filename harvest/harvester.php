<?php

	include "../config.php";
	include "../site/database/database_layer.inc";
	include "../site/database/" . DB_TYPE . "_database_layer.inc";
	$db_class = DB_TYPE . "_database_layer";
	$database = new $db_class();
	
	$link = $database->database_connect();
	$statement = $database->select_query_multiple("select index_link, site_address, site_licence from oer_site_list where url_type = :type", array(":type" => "RSS"), $link);
	$data = $database->get_all_rows($statement);

	foreach($data as $row){

		$file = "<?php \n";
		$file .= " include '" . getcwd()  . "/standard_ingest.php';\n";
		$file .= " include '" . getcwd()  . "/rssscripts/xml_ingest.php';\n";
		$file .= " \$xml_ingest = new xml_ingest(); \$xml_ingest->curl_data('" . $row['site_address'] . "', '" . $row['index_link'] . "');\n \$xml_ingest->xml_process('" . $row['site_licence'] . "','" . $row['index_link'] . "','" . $row['site_address'] . "');\n";
		$file .= "?>";
		file_put_contents("rssscripts/" . $row['index_link'] . "_harvestscript.php",$file);

	}
	
	$statement = $database->select_query_multiple("select index_link, site_address, site_licence from oer_site_list where url_type = :type", array(":type" => "OAI"), $link);
	$data = $database->get_all_rows($statement);

	foreach($data as $row){

		$file = "<?php \n";
		$file .= " include '" . getcwd()  . "/standard_ingest.php';\n";
		$file .= " include '" . getcwd()  . "/oaiscripts/xml_ingest_oai.php';\n";
		$file .= " \$xml_ingest = new xml_ingest_oai(); \$xml_ingest->get_url('" . $row['site_address'] . "','" . $row['site_address'] . "','" . $row['site_licence'] . "');\n";
		$file .= "?>";

		file_put_contents("oaiscripts/" . $row['index_link'] . "_harvestscript.php",$file);

	}
	
	$statement = $database->select_query_multiple("select index_link, site_address, site_licence from oer_site_list where url_type = :type", array(":type" => "FLICKR"), $link);
	$data = $database->get_all_rows($statement);

	foreach($data as $row){

		$file = "<?php \n";	
		$file .= " include '" . getcwd()  . "/standard_ingest.php';\n";	
		$file .= " include '" . getcwd()  . "/flickrscripts/flickr_ingest.php';\n";
		$file .= " \$flickr_ingest = new flickr_ingest(); \$flickr_ingest->flickr_search('" . $row['site_address'] . "');\n";
		$file .= "?>";

		file_put_contents("flickrscripts/" . $row['index_link'] . "_harvestscript.php",$file);

	}
	
	$statement = $database->select_query_multiple("select index_link, site_address, site_licence from oer_site_list where url_type = :type", array(":type" => "TUMBLR"), $link);
	$data = $database->get_all_rows($statement);

	foreach($data as $row){

		$file = "<?php \n";
		$file .= " include '" . getcwd()  . "/standard_ingest.php';\n";
		$file .= " include '" . getcwd()  . "/tumblrscripts/tumblr_ingest.php';\n";
		$file .= " \$tumblr_ingest = new tumblr_ingest(); \$tumblr_ingest->tumblr_search('" . $row['site_licence'] . "','" . $row['site_address'] . "');\n";
		$file .= "?>";

		file_put_contents("tumblrscripts/" . $row['index_link'] . "_harvestscript.php",$file);

	}
	
	$statement = $database->select_query_multiple("select index_link, site_address, site_licence from oer_site_list where url_type = :type", array(":type" => "SLIDESHARE"), $link);
	$data = $database->get_all_rows($statement);

	foreach($data as $row){

		$file = "<?php \n";
		$file .= " include '" . getcwd()  . "/standard_ingest.php';\n";
		$file .= " include '" . getcwd()  . "/slidesharescripts/slideshare_ingest.php';\n";
		$file .= " \$slideshare_ingest = new slideshare_ingest(); \$slideshare_ingest->slideshare_search('" . $row['site_address'] . "','" . $row['site_licence'] . "');\n";
		$file .= "?>";

		file_put_contents("slidesharescripts/" . $row['index_link'] . "_harvestscript.php",$file);

	}
	
	$statement = $database->select_query_multiple("select index_link, site_address, site_licence from oer_site_list where url_type = :type", array(":type" => "YOUTUBE"), $link);
	$data = $database->get_all_rows($statement);

	foreach($data as $row){

		$file = "<?php \n";
		$file .= " include '" . getcwd()  . "/standard_ingest.php';\n";
		$file .= " include '" . getcwd()  . "/youtubescripts/youtube_ingest.php';\n";
		$file .= " \$youtube_ingest = new youtube_ingest(); \$youtube_ingest->youtube_search('" . $row['site_address'] . "','" . $row['site_licence'] . "');\n";
		$file .= "?>";

		file_put_contents("youtubescripts/" . $row['index_link'] . "_harvestscript.php",$file);

	}

	$statement = $database->select_query_multiple("select index_link, site_address, site_licence from oer_site_list where url_type = :type", array(":type" => "TES"), $link);
	$data = $database->get_all_rows($statement);

	foreach($data as $row){

		$file = "<?php \n";
		$file .= " include '" . getcwd()  . "/standard_ingest.php';\n";
		$file .= " include '" . getcwd()  . "/tesscripts/tes_ingest.php';\n";
		$file .= " \$tes_ingest = new tes_ingest(); \$tes_ingest->get_url('http://tes.com','http://www.tes.com/resources/search/?authorId=" . $row['site_address'] . "','" . $row['site_licence'] . "');\n";
		$file .= "?>";

		file_put_contents("tesscripts/" . $row['index_link'] . "_harvestscript.php",$file);

	}

	$statement = $database->select_query_multiple("select index_link, site_address, site_licence from oer_site_list where url_type = :type", array(":type" => "SKETCHFAB_COLLECTION"), $link);
	$data = $database->get_all_rows($statement);

	foreach($data as $row){

		$address = explode("|",$row['site_address']);

		$file = "<?php \n";
		$file .= " include '" . getcwd()  . "/standard_ingest.php';\n";
		$file .= " include '" . getcwd()  . "/sketchfab_collectionscripts/sketchfab_collection_ingest.php';\n";
		$file .= " \$sketchfab_collection_ingest = new sketchfab_collection_ingest(); \$sketchfab_collection_ingest->get_url('https://sketchfab.com/','https://sketchfab.com/" . $address[0] . "/collections/" . $address[1] . "','" . $row['site_licence'] . "');\n";
		$file .= "?>";

		file_put_contents("sketchfab_collectionscripts/" . $row['index_link'] . "_harvestscript.php",$file);

	}

	$statement = $database->select_query_multiple("select index_link, site_address, site_licence from oer_site_list where url_type = :type", array(":type" => "WIKI_USER_FILE_CONTRIB"), $link);
	$data = $database->get_all_rows($statement);

	foreach($data as $row){

		$file = "<?php \n";
		$file .= " include '" . getcwd()  . "/standard_ingest.php';\n";
		$file .= " include '" . getcwd()  . "/wiki_user_file_contribscripts/wiki_user_file_contrib_ingest.php';\n";
		$file .= " \$wiki_user_file_contrib_ingest = new wiki_user_file_contrib_ingest(); \$wiki_user_file_contrib_ingest->get_url('https://commons.wikimedia.org/','" . $row['site_address'] . "','" . $row['site_licence'] . "');\n";
		$file .= "?>";

		file_put_contents("wiki_user_file_contribscripts/" . $row['index_link'] . "_harvestscript.php",$file);

	}
