<?PHP

	class wiki_user_file_contrib_ingest extends standard_ingest{
	
		var $link;
		var $database;
	
		var $default_url = "";

		var $nodes_insert = array();
		var $urls_list = array();
		var $link_insert = array();
		var $ignore_nodes = array();
		var $current_url = array();
		var $current_data = array();
		var $terms_used = array();
		var $nesting = array();

		var $rss_data = "";		
		var $name = "";		
		var $address = "";		
		var $site_licence = "";	
		var $counter = 0;
		var $item_passed = false;
		
		function end($link){
	
			$link = urldecode($link);
	
			if(isset($this->current_url['LICENSE'])){
					
				$license = utf8_encode(implode(" ", $this->current_url['LICENSE']));
						
			}else{
					
				$license = "";
					
			}
					
			if(trim($license)==""){
					
				$license = mb_convert_encoding($this->site_licence, "UTF-8");
					
			}
				
			$statement = $this->database->select_query("SELECT link_id FROM link_table WHERE link=:link", array(":link" => trim($link)), $this->link);
			$data = $this->database->get_all_rows($statement);	
				
			if(count($data)==0){						
							
				$this->database->insert_query("insert into link_table(link)VALUES(:link)", array(":link" => trim($link)), $this->link);
				$link_id = $this->database->last_insert_id($this->link);
					
			}else{
												
				$link_id = $data[0]['link_id'];
				$this->database->delete_query("delete from link_term where link_id=:link_id", array(":link_id" => $link_id), $this->link);
							
			}
							
			foreach($this->terms_used as $term){
					
				$this->database->insert_query("insert into link_term(link_id,term_id)VALUES(:link_id, :term_id)", array(":link_id" => $link_id, ":term_id" => $term), $this->link);
					
			}						
							
			if(isset($this->current_url['DESCRIPTION'])){
							
				$description = mb_convert_encoding(implode(" ", $this->current_url['DESCRIPTION']), "UTF-8");
								
			}else{
							
				$description = "";
							
			}
							
			if(isset($this->current_url['TITLE'])){
					
				$title = mb_convert_encoding(implode(" ", $this->current_url['TITLE']), "UTF-8");
						
			}else{
					
				$title = "";
					
			}
			
			$subject = "";
							
			if(isset($this->current_url['SUBJECT'])){
					
				$subject = mb_convert_encoding(implode(",", $this->current_url['SUBJECT']), "UTF-8");
						
			}

			$statement = $this->database->select_query("SELECT link_id FROM link_index WHERE link=:link", array(":link" => trim($link)), $this->link);
			$data = $this->database->get_all_rows($statement);	
					
			if(count($data)==0){

				$this->database->insert_query("insert into link_index(link_id,link,title,description,subject,license,site_address, last_updated, first_harvested)
											   VALUES
												   (:link_id,:link,:title,:description,:subject,:license,:site_address,:last_updated, :first_harvested)", 
													   array(
															":link_id" => $link_id,
															":link" => trim($link),
															":title" => $title,
															":description" => $description,
															":subject" => $subject,
															":license" => $license,
															":site_address" => $this->default_url,
															":last_updated" => time(),
															":first_harvested" => time()
															)
														, $this->link);
						
			}else{
					
					$this->database->update_query("update link_index set link_id = :link_id,title = :title, description = :description, 
											subject = :subject, license = :license, site_address = :site_address, last_updated = :last_harvested 
											where link = :link",
											array(
												":link_id" => $link_id,
												":link" => $link,
												":title" => $title,
												":description" => $description,
												":subject" => $subject,
												":license" => $license,
												":site_address" => $this->default_url,
												":last_harvested" => time()
												)
											, $this->link);
							
			}
							
			$this->counter++;
			
			$this->terms_used = array();
			$this->link_insert = array();
			$this->current_url = array();
			$this->current_data = array();
	
		}	

		function url_process($data){

			foreach($data as $page){
			
				if(strpos($page->title,"File:")!==FALSE){
			
					$page_data = json_decode(file_get_contents("https://commons.wikimedia.org/w/api.php?action=query&prop=imageinfo&iiprop=extmetadata&format=json&titles=" . urlencode($page->title)));
				
					$keys = array_keys(get_object_vars($page_data->query->pages));				
					$metadata = $page_data->query->pages->{$keys[0]}->imageinfo[0]->extmetadata;
					
					$this->current_url["TITLE"] = array($metadata->ObjectName->value);
					$this->current_url["DESCRIPTION"] = array($metadata->ImageDescription->value);
					$this->current_url["DATE"] = array($metadata->DateTime->value);
					$this->current_url["RELATED"] = array($metadata->Credit->value);
					$this->current_url["AUTHOR"] = array($metadata->Artist->value);
					$this->current_url["LICENSE"] = array($metadata->LicenseUrl->value);
					
					$this->current_url["SUBJECT"] = array();
					$categories = explode("|",$metadata->Categories->value);
					foreach($categories as $category){
						array_push($this->current_url["SUBJECT"], $category);
					}
						
					$this->node_insert();
					
					$this->end($this->default_url . "wiki/" . urlencode($page->title));
							
				}
			
			}
			
		}

		
		function get_url($default_url, $url, $site_licence){
		
			if($this->default_url==""){		
	
				$this->default_url = $default_url;
				$this->site_licence = $site_licence;
				$harvest_url = $default_url . "w/api.php?action=query&list=usercontribs&ucuser=" . $url . "&uclimit=500&format=json";
	
			}else{
			
				$harvest_url = $url;
			
			}
		
			$data = json_decode(file_get_contents($harvest_url));
			
			$this->url_process($data->query->usercontribs);

			if(isset($data->continue)){
				
				if($harvest_url != $default_url . "/api.php?action=query&list=allpages&format=json" . "&continue=" . $data->continue->continue . "&apcontinue=" . str_replace("&", "%26", $data->continue->apcontinue)){ 
					$this->get_url($default_url, $default_url . "/api.php?action=query&list=allpages&format=json" . "&continue=" . $data->continue->continue . "&apcontinue=" . str_replace("&", "%26", $data->continue->apcontinue), $site_licence);
				}else{
				}
			
			}

			$this->database->insert_query("update oer_site_list set items_harvested = :items_harvested 
													where site_address = :site_address and url_type=:type",
													array(
														":items_harvested" => $this->counter,
														":site_address" => $url,

	":type" => "WIKI_USER_FILE_CONTRIB"
														)
													, $this->link);

			echo "WIKI USER FILE CONTRIBS " . $url . " " . $this->counter . "\n";

		}

	}
	
	
	