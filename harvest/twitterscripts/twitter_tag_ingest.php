<?PHP

	class twitter_tag_ingest{

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
		var $counter = 0;	
		
		
		function twitter_tag_ingest(){	

			ini_set("max_execution_time", 30000);

			include dirname(__FILE__) . "/../../config.php";

			include dirname(__FILE__) . "/../../site/database/database_layer.inc";

			include dirname(__FILE__) . "/../../site/database/" . DB_TYPE . "_database_layer.inc";

			$db_class = DB_TYPE . "_database_layer";

			$this->database = new $db_class();			

			$this->link = $this->database->database_connect();

		}	

		function add_entry($term, $value){			

			if(!in_array($value, $this->current_data)){				

				if(!isset($this->current_url[$term])){				

					$this->current_url[$term] = array();				

				}

				array_push($this->current_data, $value);	

				array_push($this->current_url[$term], $value);

			}		

		}

		function node_insert(){

			foreach($this->current_url as $node => $list){

				foreach($list as $item){

					$item = trim($item);

					$statement = $this->database->select_query("SELECT node_id FROM node_data WHERE node_value=:value", array(":value" => utf8_encode($item)), $this->link);

					$data = $this->database->get_all_rows($statement);	

					if(count($data)==0){

						$this->database->insert_query("insert into node_data(node_value)VALUES(:item)", array(":item" => utf8_encode($item)), $this->link);

						$node_id = $this->database->last_insert_id($this->link);

					}else{			

						$node_id = $data[0]['node_id'];

					}
					
					$this->term_insert($node,$node_id);

				}

			}

		}

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

		function add_twitter_resource(){

			if(count($this->link_insert)!=0){

				sort($this->link_insert);

				$links_to_use = array_unique($this->link_insert);

				if(isset($this->current_url['LICENSE'])){

					$license = utf8_encode(implode(" ", $this->current_url['LICENSE']));

				}else{

					$license = "";
					
				}

				if($license!="" || LICENSE_SKIP == "true"){

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

			}

			$this->link_insert = array();

			$this->current_url = array();

			$this->current_data = array();

		}

		public function tweet_process($tweet){
		
			array_push($this->link_insert, "https://twitter.com/" . $tweet->user->screen_name . "/status/" . $tweet->id_str);
		
			$this->add_entry("LANGUAGE", $tweet->metadata->iso_language_code);
			$this->add_entry("LANGUAGE", $tweet->lang);
			$this->add_entry("DESCRIPTION", $tweet->text);
			$this->add_entry("TITLE", $tweet->text);
			$this->add_entry("ID", $tweet->id_str);
			$this->add_entry("SOURCE", $tweet->source);
			$this->add_entry("AUTHOR", $tweet->user->id_str);
			$this->add_entry("AUTHOR", $tweet->user->name);
			$this->add_entry("AUTHOR", $tweet->user->screen_name);
			$this->add_entry("DATE", $tweet->created_at);
			if(isset($tweet->entities->urls)){
				foreach($tweet->entities->urls as $url){
					$this->add_entry("RELATION", $url->expanded_url);
				}
			}
			if(isset($tweet->entities->hashtags)){
				foreach($tweet->entities->hashtags as $tag){
					$this->add_entry("SUBJECT", $tag->text);
				}
			}
			if(isset($tweet->entities->media)){
				foreach($tweet->entities->media as $media){
					if($media->type == "photo"){
						$this->add_entry("PICTURE", $media->media_url_https);
						$this->add_entry("RELATION", $media->media_url_https);
					}
				}
			}

			$this->add_twitter_resource();

		}

		public function twitter_search(){
		
			$statement = $this->database->select_query("SELECT site_address FROM oer_site_list WHERE url_type='twitter' order by last_harvested desc limit 15", array(), $this->link);

			$data = $this->database->get_all_rows($statement);	

			foreach($data as $resource){
				
				$text = explode("-", $resource['site_address']);
				
				if($text[1] == "tag"){
					$this->twitter_tag($text);
				}else{
					$this->twitter_user($text);
				}
				
			}
			
		}
		
		private function get_twitter_setup(){

			$ckey = file_get_contents(dirname(__FILE__) . "/ckey.txt");
			$csecret = file_get_contents(dirname(__FILE__) . "/csecret.txt");
			$okey = file_get_contents(dirname(__FILE__) . "/okey.txt");
			$osecret = file_get_contents(dirname(__FILE__) . "/osecret.txt");

			return array($ckey, $csecret, $okey, $osecret);

		}
		
		private function configure_twitter_harvest($details){

			require_once('twitteroauth/OAuth.php');
			require_once('twitteroauth/twitteroauth.php');

			define('CONSUMER_KEY', $details[0]);
			define('CONSUMER_SECRET', $details[1]);
			define('OAUTH_CALLBACK', '');

			function getConnectionWithAccessToken($oauth_token, $oauth_token_secret) {		  
			  $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $oauth_token, $oauth_token_secret);
			  return $connection;
			}

			$connection = getConnectionWithAccessToken($details[2], $details[3]);

			return $connection;

		}
			
		public function twitter_tag($text){
		
			$name = implode("-", $text);
		
			array_shift($text);
			array_shift($text);
			
			$text = implode("-", $text);

			$setup = $this->get_twitter_setup();
			$connection = $this->configure_twitter_harvest($setup);
			$url = "search/tweets.json?q=" . urlencode($text) . "&count=100";
			$content = $connection->get($url);
			
			if(isset($content->statuses)){
			
				foreach($content->statuses as $tweet){
					
					$this->tweet_process($tweet);
				
				}
				
				$this->database->update_query("update oer_site_list set items_harvested = :items_harvested, last_harvested = :last_harvested where site_address = :site_address",

											array(

												":items_harvested" => $this->counter,
												":last_harvested" => time(),
												":site_address" => $name

												)

											, $this->link);

			}else{
			
				echo "INvALID RSP\n";
			
			}

		}

	}

	