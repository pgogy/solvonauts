<?PHP



	class tumblr_ingest extends standard_ingest{

	

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



		function add_tumblr_resource(){

		

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

			

				echo __FILE__ . "NO LINKS " . $this->address . "\n";

			

			}



			$this->link_insert = array();

			$this->current_url = array();

			$this->current_data = array();



		}

		

		public function process_post($post, $author){

		

			$this->add_entry("DC:FORMAT", $post['format']);

			

			$title = explode("</p>", $post['content']['caption']);

			

			$title = strip_tags($title[0]);

			

			$this->add_entry("TITLE", $title);

			$this->add_entry("DESCRIPTION", strip_tags($post['content']['caption']));

			$this->add_entry("CREATOR", $author);

			$this->add_entry("LICENSE", $this->site_license); 

			

			foreach($post['tags'] as $tag){

				

				$this->add_entry("SUBJECT", $tag);

			

			}

			

			array_push($this->link_insert,$post['url']);

			

			$this->add_tumblr_resource();

		

		}

		

		public function tumblr_search($site_license, $name){

		

			$this->site_license = $site_license;

			$this->name = $name;

		

			# ***** BEGIN LICENSE BLOCK *****

			# This file is part of phpTumblr.

			# Copyright (c) 2006 Simon Richard and contributors. All rights

			# reserved.

			#

			# phpTumblr is free software; you can redistribute it and/or modify

			# it under the terms of the GNU General Public License as published by

			# the Free Software Foundation; either version 2 of the License, or

			# (at your option) any later version.

			# 

			# phpTumblr is distributed in the hope that it will be useful,

			# but WITHOUT ANY WARRANTY; without even the implied warranty of

			# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

			# GNU General Public License for more details.

			# 

			# You should have received a copy of the GNU General Public License

			# along with phpTumblr; if not, write to the Free Software

			# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

			#

			# ***** END LICENSE BLOCK *****



			require dirname(__FILE__).'/clearbricks/_common.php';

			require dirname(__FILE__).'/clearbricks/net/class.net.socket.php';

			require dirname(__FILE__).'/clearbricks/net.http/class.net.http.php';

			require dirname(__FILE__).'/class.read.tumblr.php';



			$oTumblr = new readTumblr($this->name);

			$counter = 0;

			$page = 100;

			$total_read = 0;

			$oTumblr->getPosts($counter,$page,'all','regular');

			$aTumblr = $oTumblr->dumpArray();

			$total = $aTumblr['stats']['num-all'];

			$this->address = $aTumblr['tumblelog']['url'];

			if(isset($aTumblr['posts'])){

				for($x=$page;$x<=$total;$x++){		

					foreach($aTumblr['posts'] as $id => $post){
	
						$this->process_post($post, $aTumblr['tumblelog']['title']);
					}

					$counter+=$page;

					$x = $counter;
	
					$oTumblr = new readTumblr($this->name);

					$oTumblr->getPosts($counter,$page,'all','regular');

					$aTumblr = $oTumblr->dumpArray();

				}

			}

			$this->database->insert_query("update oer_site_list set items_harvested = :items_harvested 

														where site_address = :site_address",

														array(

															":items_harvested" => $this->counter,

															":site_address" => $name

															)

														, $this->link);

		echo "TUMBLR " . $name . " " . $this->counter . "\n";

									

		}

			

	}

	