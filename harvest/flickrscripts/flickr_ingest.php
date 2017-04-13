<?PHP

	class flickr_ingest extends standard_ingest{

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



		function add_flickr_resource(){

		

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



			}



			$this->link_insert = array();

			$this->current_url = array();

			$this->current_data = array();



		}

		

		public function picture_process($picture){

		

			if(isset($picture['photo']['originalformat'])){

		

				$this->add_entry("DC:FORMAT", $picture['photo']['originalformat']);

			

			}

			$this->add_entry("TITLE", $picture['photo']['title']['_content']);

			$this->add_entry("DESCRIPTION", $picture['photo']['description']['_content']);

			//$this->add_entry("USAGE", $picture['photo']['views']);

			$this->add_entry("CREATOR", $picture['photo']['owner']['username']);

			

			switch($picture['photo']['license']){

			

				case "1" : $this->add_entry("LICENSE", "http://creativecommons.org/licenses/by-nc-sa/2.0/"); break;

				case "2" : $this->add_entry("LICENSE", "http://creativecommons.org/licenses/by-nc/2.0/"); break;

				case "3" : $this->add_entry("LICENSE", "http://creativecommons.org/licenses/by-nc-nd/2.0/"); break;

				case "4" : $this->add_entry("LICENSE", "http://creativecommons.org/licenses/by/2.0/"); break;

				case "5" : $this->add_entry("LICENSE", "http://creativecommons.org/licenses/by-sa/2.0/"); break;

				case "6" : $this->add_entry("LICENSE", "http://creativecommons.org/licenses/by-nd/2.0/"); break;

				case "7" : $this->add_entry("LICENSE", "No known copyright restrictions"); break;

				

			}

			

			if(isset($picture['photo']['tags']) && isset($picture['photo']['tags']['tag'])){



				foreach($picture['photo']['tags']['tag'] as $tag){

				

					$this->add_entry("SUBJECT", $tag['_content']);

			

				}



			}

			

			foreach($picture['photo']['urls']['url'] as $link){

			

				array_push($this->link_insert,$link['_content']);

			

				$this->add_entry("DC:RELATION", $link['_content']);

			

			}

			

			$this->add_flickr_resource();

		

		}

		

		public function flickr_search($name){

			mail("patrick.lockley@googlemail.com","FLICK",$name . " " . time());

			$params = array(			

				'username'	=>  $name,		

				'api_key'	=> 'f642fd9921531963fcb994c1182b97a6',

				'method'	=> 'flickr.people.findbyusername',

				'format'	=> 'php_serial'				

			);			

			$encoded_params = array();

			foreach ($params as $k => $v){

				$encoded_params[] = urlencode($k).'='.urlencode($v);				

			}

			$url = "https://api.flickr.com/services/rest/?".implode('&', $encoded_params);

			$rsp = $this->get_url($url);

			$rsp_obj = unserialize($rsp);

			if(isset($rsp_obj['user'])){
			
				$this->address = $rsp_obj['user']['username']['_content'] . " | FlickR ";

				$user = $rsp_obj['user']['nsid'];

				$params = array(

					'user_id'	=>  $user,		

					'api_key'	=> 'f642fd9921531963fcb994c1182b97a6',

					'method'	=> 'flickr.photos.search',

					'format'	=> 'php_serial',

					'per_page'	=> 500

				);

				$encoded_params = array();

				foreach ($params as $k => $v){

					$encoded_params[] = urlencode($k).'='.urlencode($v);

				}	
			
				$url = "https://api.flickr.com/services/rest/?".implode('&', $encoded_params);

				$rsp = $this->get_url($url);			

				$rsp_obj = unserialize($rsp);

				if(isset($rsp_obj['photos'])){

					$page = $rsp_obj['photos']['page'];
	
					$all_pages = $rsp_obj['photos']['pages'];

					for($x=$page;$x<=$all_pages;$x++){

						$params = array(

							'user_id'	=>  $user,		

							'api_key'	=> 'f642fd9921531963fcb994c1182b97a6',

							'method'	=> 'flickr.photos.search',

							'format'	=> 'php_serial',

							'per_page'	=> 500,

							'page' => $x

						);

						$encoded_params = array();

						foreach ($params as $k => $v){

							$encoded_params[] = urlencode($k).'='.urlencode($v);

						}

						$url = "https://api.flickr.com/services/rest/?".implode('&', $encoded_params);

						$rsp = $this->get_url($url);

						$rsp_obj = unserialize($rsp);

						foreach($rsp_obj['photos']['photo'] as $photo){

							$params = array(

								'api_key'	=> 'f642fd9921531963fcb994c1182b97a6',

								'method'	=> 'flickr.photos.getInfo',

								'format'	=> 'php_serial',

								'photo_id'	=> $photo['id']

							);

							$encoded_params = array();

							foreach ($params as $k => $v){

								$encoded_params[] = urlencode($k).'='.urlencode($v);
							
							}

							$url = "https://api.flickr.com/services/rest/?".implode('&', $encoded_params);

							$rsp = $this->get_url($url);

							if(isset($rsp)&&$rsp!=""){

								$rsp_obj = unserialize($rsp);

								$this->picture_process($rsp_obj);

							}

						}

					}

					$this->database->insert_query("update oer_site_list set items_harvested = :items_harvested where site_address = :site_address and url_type=:type",

												array(

													":items_harvested" => $this->counter,

													":site_address" => $name,

	":type" => "FLICKR",

													)

												, $this->link);

				}

				if($this->counter!==0){

					$this->url_list_show();

				}

				echo "FLICKR " . $name . " " . $this->counter . "\n";

			}else{
			
			}

		}

		function get_url($url){

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);

			$data = curl_exec($ch);

			return $data;

		}

	}

	