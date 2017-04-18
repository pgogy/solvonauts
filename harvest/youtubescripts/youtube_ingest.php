<?PHP

	class youtube_ingest extends standard_ingest{
	
		var $link;
		var $database;

		var $nodes_insert = array();
		var $link_insert = array();
		var $current_url = array();
		var $current_data = array();
		var $terms_used = array();
		var $nesting = array();

		var $rss_data = "";		
		var $name = "";		
		var $address = "";		
		var $site_licence = "";	
		var $site_address = "";	
		var $counter = 0;
		
		
		function term_insert($node, $node_id){
			
			$statement = $this->database->select_query("SELECT term_id FROM node_term WHERE term=:term and node_id =:node_id", array(":term" => $node, ":node_id" => $node_id), $this->link);
			$data = $this->database->get_all_rows($statement);	
			
			if(count($data)==0){
						
				$this->database->insert_query("insert into node_term(term,node_id)VALUES(:node, :node_id)", array(":node" => $node, ":node_id" => $node_id), $this->link);
				$term_id = $this->database->last_insert_id($this->link);
					
			}else{
										
				$term_id = $data[0]['term_id'];
					
			}
			
			array_push($this->terms_used, $term_id);
			
		}

		function add_youtube_resource(){
		
			if(count($this->link_insert)!=0){
			
				sort($this->link_insert);

				$links_to_use = array_unique($this->link_insert);
				
				if(isset($this->current_url['LICENSE'])){
				
					$license = utf8_encode(implode(" ", $this->current_url['LICENSE']));
					
				}else{
				
					$license = "";
				
				}
				
				if($license!=""){
					
					$this->node_insert();
					
					$statement = $this->database->select_query("SELECT link_id FROM link_table WHERE link=:link", array(":link" => trim($links_to_use[0])), $this->link);
					$data = $this->database->get_all_rows($statement);	
					
					if(count($data)==0){						
							
						$this->database->insert_query("insert into link_table(link)VALUES(:link)", array(":link" => trim($links_to_use[0])), $this->link);
						$link_id = $this->database->last_insert_id($this->link);
							
					}else{
												
						$link_id = $data[0]['link_id'];
						$this->database->delete_query("delete from link_term where link_id=:link_id", array(":link_id" => $link_id), $this->link);
							
					}
					
					foreach($this->terms_used as $term){
					
						$this->database->insert_query("insert into link_term(link_id,term_id)VALUES(:link_id, :term_id)", array(":link_id" => $link_id, ":term_id" => $term), $this->link);
					
					}						
					
					if(isset($this->current_url['SUBJECT'])){
					
						$subject = mb_convert_encoding(implode(",", $this->current_url['SUBJECT']),"UTF-8");
						
					}else{
					
						$subject = "";
					
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

					$statement = $this->database->select_query("SELECT link_id FROM link_index WHERE link=:link", array(":link" => trim($links_to_use[0])), $this->link);
					$data = $this->database->get_all_rows($statement);	
		
					if(count($data)==0){
							
						$this->database->insert_query("insert into link_index(link_id,link,title,description,subject,license,site_address, first_harvested, last_updated)
													   VALUES
													   (:link_id,:link,:title,:description,:subject,:license,:site_address,:first_harvested,:last_updated)", 
													   array(
															":link_id" => $link_id,
															":link" => trim($links_to_use[0]),
															":title" => $title,
															":description" => $description,
															":subject" => $subject,
															":license" => $license,
															":site_address" => $this->address,
															":last_updated" => time(),
															":first_harvested" => time(),
															)
														, $this->link);
						
					}else{
												
						$this->database->update_query("update link_index set link_id = :link_id,title = :title, description = :description, 
											subject = :subject, license = :license, site_address = :site_address, last_updated = :last_harvested 
											where link = :link",
											array(
												":link_id" => $link_id,
												":link" => trim($links_to_use[0]),
												":title" => $title,
												":description" => $description,
												":subject" => $subject,
												":license" => $license,
												":site_address" => $this->address,
												":last_harvested" => time()
												)
											, $this->link);
							
					}
					
					$this->counter++;
					
				}
				
				$this->terms_used = array();

			}else{
			
				echo "NO LINKS " . $this->address . "\n";
			
			}

			$this->link_insert = array();
			$this->current_url = array();
			$this->current_data = array();

		}
		
		public function process_video($video, $license, $name){
		
			$this->add_entry("TITLE", $video['title']);
			$this->add_entry("DESCRIPTION", $video['description']);
			$this->add_entry("CREATOR", $name);
			$this->add_entry("LICENSE", $license); 
			
			array_push($this->link_insert,"http://www.youtube.com/watch?v=" . $video['resourceId']['videoId']);

			$this->add_youtube_resource();
		
		}
		
		public function youtube_search($name, $license){

			  require_once dirname(__FILE__) . '/google-api/Google_Client.php';

			  require_once dirname(__FILE__) . '/google-api/contrib/Google_YouTubeService.php';

			  $this->address = "http://www.youtube.com/user/" . $name;

			  $DEVELOPER_KEY = 'ADD DEVELOPER KEY HERE';

			  $client = new Google_Client();
			  $client->setDeveloperKey($DEVELOPER_KEY);

			  $youtube = new Google_YoutubeService($client);

			  $channelsResponse = $youtube->channels->listChannels('contentDetails', array(
				  'forUsername' => $name,
				));
				
			  $list = $channelsResponse['items'][0]['contentDetails']['relatedPlaylists']['uploads'];
			  
			  $channelsResponse = $youtube->playlistItems->listPlaylistItems('snippet', array(
				  'playlistId' => $list,
				  'maxResults' => 25,
			  ));
			  
			  $harvest = true;
			  
			  while($harvest){
			  
				  while($video = array_pop($channelsResponse['items'])){
				  
					$this->process_video($video['snippet'], $license, $name);
				  
				  }
				  
				  $channelsResponse = $youtube->playlistItems->listPlaylistItems('snippet', array(
					  'playlistId' => $list,
					  'maxResults' => 25,
					  'pageToken' => $channelsResponse['nextPageToken'],
				  ));
				  
				  if(!isset($channelsResponse['nextPageToken'])){
				  
					$harvest = false;
				  
				  }
				  
			  }
			  
			  if($this->counter!==0){

					$this->url_list_show();

				}
			  
			  $this->database->insert_query("update oer_site_list set items_harvested = :items_harvested 
													where site_address = :site_address and url_type=:type",
													array(
														":items_harvested" => $this->counter,
														":type" => "YOUTUBE",
														":site_address" => $name
														)
													, $this->link);
	
			echo "YOUTUBE " . $name . " " . $this->counter . "\n";
			echo "404 check " . $this->url_count . "\n";
	
		}

			
	}
	