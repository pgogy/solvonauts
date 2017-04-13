<?PHP

	class xml_ingest_oai extends standard_ingest{
	
		var $link;
		var $database;
	
		var $default_url = "";

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
		var $item_passed = false;
		
		var $ignore_nodes = array(
									  'A',
									  'A10:CONTENT',
									  'A10:ID',
									  'A10:NAME',
									  'ABBR',
									  'ABSTRACT',
									  'ARCH:DOCID',
									  'ARCH:STRING',
									  'ATOM:UPDATED',
									  'BR',
									  'CODE',
									  'COMMENTS',
									  'CONTENT:ENCODED',
									  'DATESTAMP',
									  'DOCS',
									  'DATEUPLOADED',
									  'DD',
									  'DIV',
									  'DT',
									  'EM',
									  'EC:COURSE_ID',
									  'EC:LEVEL',
									  'EC:STRUCTURE',
									  'EC:TERM',
									  'ERROR',
									  'EMAIL',
									  'EXPLICIT',
									  'FLICKR:BUDDYICON',
									  'FLICKR:NSID',
									  'FEEDBURNER:BROWSERFRIENDLY',
									  'FEEDBURNER:EMAILSERVICEID',
									  'FEEDBURNER:FEEDBURNERHOSTNAME',
									  'FEEDBURNER:FEEDFLARE',
									  'FEEDBURNER:ORIGENCLOSURELINK',
									  'FEEDBURNER:ORIGLINK',
									  'H1',
									  'HTML:I',
									  'HTML:P',
									  'HTML:I',
									  'H2',
									  'H3',
									  'H5',
									  'GUID','LINK',
									  'ICON',
									  'IMG',
									  'HEIGHT',
									  'IDENTIFIER',
									  'IMAGE',
									  'INPUT',
									  'LABEL',
									  'LI',
									  'OC:ARK_IDENTIFIER',
									  'OC:COINS',
									  'OC:DIARYCOUNT',
									  'OC:ICONURI',
									  'OC:ID',
									  'OC:LEVEL',
									  'OC:LIC_ICON_URI',
									  'OC:LIC_NAME',
									  'OC:LIC_URI',
									  'OC:LIC_VERS',
									  'OC:MEDIACOUNT',
									  'OC:NAME',
									  'OC:NO_PROPS',
									  'OC:PRIMARY_XSL',
									  'OC:PROJECT_NAME',
									  'OC:PROJGEOPOINT',
									  'OC:PROJGEOPOLY',
									  'OC:PUB_DATE',
									  'OC:QUERYVAL',
									  'OC:RELATION',
									  'OC:ROOTPATH',
									  'OC:SPACECOUNT',
									  'OC:THUMBNAILURI',
									  'OC:TYPE',
									  'OPTION',
									  'P',
									  'PHEEDO:ORIGLINK',
									  'RDF:LI',
									  'REQUEST',
									  'RESPONSEDATE',
									  'RESUMPTIONTOKEN',
									  'SCRIPT',
									  'SETSPEC',
									  'SLASH:COMMENTS',
									  'SOURCE',
									  'SPAN',
									  'STYLE',
									  'SY:UPDATEFREQUENCY',
									  'SY:UPDATEPERIOD',
									  'SYN:UPDATEBASE',
									  'SYN:UPDATEFREQUENCY',
									  'SYN:UPDATEPERIOD',
									  'TD',
									  'TH',
									  'TT',
									  'THR:TOTAL',
									  'TTL',
									  'URL',
									  'WEBMASTER',
									  'WFW:COMMENTRSS',
									  'WIDTH'
									);
		
		function add_entry($term, $value){
				
			if($term=="DOMAIN"||$term=="RDF:RESOURCE"||$term=="URI"||$term=="FEEDBURNER:ORIGLINK"){
			
				if(strpos($value,"creativecommons")!==FALSE){
				
					$term = "DC:RIGHTS";
				
				}else{
			
					$term = "RELATION";
					
				}
			
			}			
		
			if($term=="DC:RIGHTS"||$term=="CC:LICENSE"||$term=="CREATIVECOMMONS:LICENSE"){
			
				$term = "LICENSE";
			
			}
		
			$term = str_replace("DC:","",$term);			
			
			if($term=="CATEGORY"||$term=="TERM"||$term=="MEDIA:CATEGORY"||$term=="ITUNES:KEYWORDS"){
			
				$term = "SUBJECT";
			
			}
			
			if($term=="ITUNES:SUMMARY"){
			
				$term = "DESCRIPTION";
				
			}
			
			if($term=="ITUNES:AUTHOR"){
			
				$term = "CREATOR";
				
			}
		
			if($term=="RDF:LI"){
			
				$term = $this->nesting[1];
				
			}
			
			if(!in_array($value, $this->current_data)){
				
				if(!isset($this->current_url[$term])){
				
					$this->current_url[$term] = array();
				
				}
				
				array_push($this->current_data, $value);			
				array_push($this->current_url[$term], $value);
			
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

		function start_tag($parser, $name, array $attributes){	
		
			$this->name = $name;
		
			array_push($this->nesting, $name);
			
			if($name=="ITEM"||$name=="ENTRY"||$name=="LISTRECORDS"){
			
				$this->item_passed = true;
			
			}
			
			if($this->item_passed){
			
				if($name=="LINK"){
				
					if(isset($attributes['HREF'])){	
					
						array_push($this->link_insert, $attributes['HREF']);
						
					}
				
				}
			
				if($name=="ITEM"){
				
					if(isset($attributes['RDF:ABOUT'])){	
					
						array_push($this->link_insert, $attributes['RDF:ABOUT']);
						
					}
				
				}
				
				if(isset($attributes['URL'])){	

					$this->add_entry('DC:RELATION',$attributes['URL']);
				
				}
				
				if($name=="CC:LICENSE"&&isset($attributes['RDF:RESOURCE'])){
				
					$this->add_entry('LICENSE',$attributes['RDF:RESOURCE']);
				
				}

				$key = array_keys($attributes);

				foreach($attributes as $keyname => $data){		

					$attr_data = $data;			

					$short_data = addslashes($attr_data);

					$this->add_entry($keyname,$short_data);

				}	

			}else{
				
				$this->nesting = array();
				
			}

		}

		function end_tag($parser,$name){
		
			$this->name = $name;
		
			if($this->item_passed){

				if($name=="LINK"||$name=="GUID"||$name=="OER_URL"||$name=="DC:IDENTIFIER"||$name=="IDENTIFIER"){
				
					if($this->rss_data!=""){

						if(strpos($this->rss_data,"http")!==FALSE){

							if(substr(trim($this->rss_data),0,4)=="http"){

								array_push($this->link_insert,$this->rss_data);
								
							}
							
						}

					}

				}
				
				if($name=="DC:RIGHTS"||$name=="CC:LICENSE"||$name=="CREATIVECOMMONS:LICENSE"||$name=="RIGHTS"||$name=="COPYRIGHT"){
				
					$name = "LICENSE";
					
					$this->add_entry($name,$this->rss_data);
				
				}
			
				if(trim($this->rss_data)!=""){

					$short_data = substr(addslashes(strip_tags(trim($this->rss_data))),0,800);
						
					if(substr($short_data,799)=='\\'){
					
						$short_data = substr(addslashes(strip_tags(trim($this->rss_data))),0,799);
					
					}

					if($short_data!=""){
					
						$this->add_entry($name,$short_data);

					}
					
				}
			
				if($name=="ITEM"||$name=="RECORD"||$name=="ENTRY"){
				
					if(count($this->link_insert)!=0){
					
						sort($this->link_insert);

						$links_to_use = array_unique($this->link_insert);
						
						if(isset($this->current_url['LICENSE'])){
						
							$license = utf8_encode(implode(" ", $this->current_url['LICENSE']));
							
						}else{
						
							$license = "";
						
						}
						
						if(trim($license)==""){
						
							$license = mb_convert_encoding($this->site_licence, "UTF-8");
						
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
									
								$this->database->insert_query("insert into link_index(link_id,link,title,description,subject,license,site_address, last_updated, first_harvested)
															   VALUES
															   (:link_id,:link,:title,:description,:subject,:license,:site_address,:last_updated, :first_harvested)", 
															   array(
																	":link_id" => $link_id,
																	":link" => trim($links_to_use[0]),
																	":title" => $title,
																	":description" => $description,
																	":subject" => $subject,
																	":license" => $license,
																	":site_address" => $this->address,
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
				
			}
			
			array_pop($this->nesting);
			
			$this->rss_data = "";
			$this->name = "";

		}	


		function node_data($parser,$data_node){
	
			if($this->name=="DC:IDENTIFIER"||$this->name=="DESCRIPTION"||$this->name=="DC:RIGHTS"){
			
				$this->rss_data .= $data_node;
			
			}else if($this->name=="LINK"){
			
				$this->rss_data .= trim(str_replace("\\Browse","/Browse",$data_node));
			
			}else{

				$this->rss_data = $data_node;
				
			}

		}

		function xml_process($data, $licence, $file_passed, $url_address){

			$this->address = $url_address;

			$file = $file_passed;	

			$parser = xml_parser_create("UTF-8");		

			xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);

			xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 1);	

			xml_set_element_handler ( $parser, array($this, 'start_tag'), array($this, 'end_tag') );

			xml_set_character_data_handler ( $parser, array($this, 'node_data') );	

			if(xml_parse($parser, $data)){

			}else{
			
				$error = xml_parse($parser, $data);
				echo xml_error_string($error) . "\n";
				echo "INVALID XML\n";
					
			}
			
			$this->url_list_show();
			
			$this->database->insert_query("update oer_site_list set items_harvested = :items_harvested 
													where site_address = :site_address",
													array(
														":items_harvested" => $this->counter,
														":site_address" => $url_address
														)
													, $this->link);

	

		}

		
		function get_url($default_url, $url, $site_licence){

			if($this->default_url==""){
			
				$this->default_url = $default_url;
				$this->site_licence = $site_licence;
	
			}
		
			$counter = 0;
						
			$useragent="Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

			curl_setopt($ch, CURLOPT_URL, $url); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_HEADER, 0); 
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20); 
			curl_setopt($ch, CURLOPT_TIMEOUT, 500); 
			curl_setopt($ch, CURLOPT_MAXREDIRS, 10); 

			$data = curl_exec($ch);
			
			$this->xml_process($data, null, null, $this->default_url);

			$xml = simplexml_load_string($data);

			if(isset($xml->ListRecords->resumptionToken[0])){
				
				$url_parse = explode("&", $url);

				$url = $url_parse[0] . "&resumptionToken=" . $xml->ListRecords->resumptionToken[0];
				
				$this->get_url($this->default_url, $url, $site_licence);

			}else{

				echo "OAI " . $url . " " . $this->counter . "\n";			

			}


		}

	}
	
	