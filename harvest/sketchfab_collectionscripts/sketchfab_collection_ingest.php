<?PHP

	class sketchfab_collection_ingest extends standard_ingest{
	
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
	
			if(isset($this->current_url['LICENSE'])){
					
				$license = utf8_encode(implode(" ", $this->current_url['LICENSE']));
						
			}else{
					
				$license = "";
					
			}
					
			if(trim($license)==""){
					
				$license = mb_convert_encoding($this->site_licence, "UTF-8");
					
			}

			$this->node_insert();
					
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
							
			$subject = "";
							
			if(isset($this->current_url['SUBJECT'])){
					
				$subject = mb_convert_encoding(implode(",", $this->current_url['SUBJECT']), "UTF-8");
						
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
												":link" => trim($link),
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
		
		function page_process($data, $url){
			
			$this->current_url['TITLE'] = array();
			$this->current_url['DESCRIPTION'] = array();
			$this->current_url['SUBJECT'] = array();
			$this->current_url['RELATED'] = array();
			$this->current_url['LIKES'] = array();
			$this->current_url['VIEWS'] = array();
			$this->current_url['DOWNLOADS'] = array();
			$this->current_url['LICENSE'] = array();
			
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($data);
			$query = "//h1/span[contains(@class, 'model-name')]/span"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query); 
			foreach($result as $node){
				array_push($this->current_url['TITLE'], $node->nodeValue);
			}
			
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($data);
			$query = "//div/section[contains(@class, 'description')]/p"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query);
			foreach($result as $node){
				array_push($this->current_url['DESCRIPTION'], $node->nodeValue);
			}
			
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($data);
			$query = "//ul[contains(@itemprop, 'keywords')]/li"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query);
			foreach($result as $node){
				array_push($this->current_url['SUBJECT'], $node->nodeValue);
			}
			
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($data);
			$query = "//a[contains(@class, 'collection-name')]"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query);
			foreach($result as $node){
				array_push($this->current_url['RELATED'], $node->getAttribute("href"));
			}
			
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($data);
			$query = "//ul[contains(@class, 'related-user')]/li/div/a"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query);
			foreach($result as $node){
				array_push($this->current_url['RELATED'], $node->getAttribute("href"));
			}
			
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($data);
			$query = "//div[contains(@class, 'has-likes')]/span"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query);
			$counter = 0;
			foreach($result as $node){
				array_push($this->current_url['LIKES'], $node->nodeValue);
			}
			
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($data);
			$query = "//div[contains(@class, 'stats')]/div[contains(@class, 'views')]/div[contains(@class, 'tooltip')]/span"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query);
			$counter = 0;
			foreach($result as $node){
				array_push($this->current_url['VIEWS'], $node->nodeValue);
			}
			
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($data);
			$query = "//div[contains(@class, 'downloads')]/span"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query);
			$counter = 0;
			foreach($result as $node){
				array_push($this->current_url['DOWNLOADS'], $node->nodeValue);
			}
			
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($data);
			$query = "//section[contains(@class, 'license')]/p/a"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query);
			$counter = 0;
			foreach($result as $node){
				array_push($this->current_url['LICENSE'], $node->getAttribute("href"));
			}
			
			$this->node_insert();
			$this->end($url);
		
		}

		function url_process($data){
	
			$this->domDocument = new DOMDocument();
			@$this->domDocument->loadHTML($data);
			$query = "//div[contains(@class, 'model-card-thumbnail')]/a"; 
			$xpath = new DOMXPath($this->domDocument); 
			$result = $xpath->query($query); 
			
			foreach ($result as $node) {
				$url = $node->getAttribute("href");
				if($url!=""){
					$this->page_process(file_get_contents($url), $url);
				}
			}
				
		}

		
		function get_url($base_url, $url, $site_licence){
		
			$this->default_url = $base_url;
		
			$data = file_get_contents($url);

			$this->url_process($data);

			$this->url_list_show();
			
			$name = str_replace("collections|","",str_replace("/","|",str_replace("https://sketchfab.com/","",$url)));

			$this->database->update_query("update oer_site_list set items_harvested = :items_harvested where site_address = :site_address and url_type=:type",
													array(
														":items_harvested" => $this->counter,
														":site_address" => $name,

	":type" => "SKETCHFAB_COLLECTION"
														)
													, $this->link);

			echo "SKETCHFAB COLLECTION " . $url . " " . $this->counter . "\n";

		}


	}
	
	
	