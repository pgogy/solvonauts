<?PHP

	class standard_ingest{
	
		function __construct(){

			$this->url_list = array();
			include dirname(__FILE__) . "/../config.php";
			include dirname(__FILE__) . "/../site/database/database_layer.inc";
			include dirname(__FILE__) . "/../site/database/" . DB_TYPE . "_database_layer.inc";
			$db_class = DB_TYPE . "_database_layer";
			$this->database = new $db_class();
			$this->link = $this->database->database_connect();
		
		}
		
		function site_address_add($address){
			$this->site_address = $address;
		}
		
		function url_list_add($link){
			array_push($this->url_list,$link);
		}

		function process_404($list){	

			$this->url_count = 0;

			foreach($list as $url){

				$statement = $this->database->select_query("SELECT link, last_check, times_checked, live FROM 404_check WHERE link=:value", array(":value" => utf8_encode($url['link'])), $this->link);
				$data = $this->database->get_all_rows($statement);

				if(count($data)==0){
					
					$this->database->insert_query("insert into 404_check(link,live)VALUES(:link,:live)", array(":link" => utf8_encode($url['link']),":live" => 1), $this->link);
					$this->url_count++;

				}else{

					$diff = time() - $data[0]['last_check'];
					if($data['times_checked']>5){		
						if($diff>1209600){	
							$this->database->update_query("update 404_check set live = 1 where link=:link", array(":link" => utf8_encode($url['link'])), $this->link);
							$this->url_count++;
						}
					}else{
						if($diff>604800){
							$this->database->update_query("update 404_check set live = 1 where link=:link", array(":link" => utf8_encode($url['link'])), $this->link);
							$this->url_count++;
						}
					}

				}
			}			 

		}
		
		function url_list_show(){

			$statement = $this->database->select_query("SELECT link, last_updated FROM link_index WHERE site_address=:value", array(":value" => utf8_encode($this->site_address)), $this->link);
			$data = $this->database->get_all_rows($statement);
			$databkp = $data;

			foreach($data as $index => $item){
				unset($item['link']);
				$data[$index] = $item[0];	
			}

			$diff = array_diff($data, $this->url_list);
			$list = array();

			if(count($diff)!=0){

				foreach($databkp as $url){
					if(in_array($url['link'],$diff)){
						$list[] = $url;
					}
				}

				$this->process_404($list);

			}

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
		
		function translate_word($word){
		
			if(file_exists(dirname(__FILE__) . "/translate/" . $word . ".txt")){
			
				return explode(",",file_get_contents(dirname(__FILE__) . "/translate/" . $word . ".txt"));
				
			}else{
			
				return array($word);
			
			}
			
		}

		function translate_item($text){
			
			if(strpos($text," ")===FALSE){
			
				return $this->translate_word($text);
			
			}else{
			
				$words = explode(" ", $text);
				
				foreach($words as $word){
				
					$new_word = $this->translate_word($word); 
				
					if($word!=$new_word[0]){
					
						$text = str_replace($word, $word . " (" . implode(",", $new_word) . ") ", $text);  
					
					}
					
				}

				return array($text);
			
			}
		
		}
		
		function translate_list($list){
		
			$new_list = array();
			
			$list = array_unique(array_filter($list));
			
			foreach($list as $item){
			
				if(strpos(strtolower($item),"http")===FALSE){
					
					$data = $this->translate_item($item);
					array_push($data, $item);
					$new_list = array_merge($new_list, $data);
				
				}else{
				
					$new_list = $list;
				
				}
			
			}

			return array_unique(array_filter($new_list));
		
		}
		
		function node_insert(){
		
			foreach($this->current_url as $node => $list){
			
				$list = $this->translate_list($list);
				
				$this->current_url[$node] = $list;
			
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
			
			if(!in_array($node, $this->ignore_nodes)&&strpos($node,"XMLNS")===FALSE){
			
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
		
		}
	}