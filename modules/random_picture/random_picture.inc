<?PHP

class random_picture{

	var $data;
	var $time;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){

		return "generate_rss";
	
	}
	
	public function generate_rss(){

		echo '<?xml version="1.0" encoding="UTF-8"?>';

		?><rss version="2.0">
				<channel>
					<ttl>60</ttl>
					<title><?PHP echo $this->site->get_text("A Random OER picture from Solvonauts"); ?></title>
					<link><?PHP echo SITE . urlencode("?action=random_picture"); ?></link>
					<description><?PHP echo $this->site->get_text("A Random OER picture from Solvonauts"); ?></description>
					<item>
							<title><![CDATA[<?PHP echo substr(strip_tags(stripslashes(utf8_decode($this->data['results'][0]['title'])) . " : " . strip_tags(utf8_decode($this->data['results'][0]['description']))), 0, 100); ?>...]]></title>
							<description><![CDATA[<?PHP echo substr(strip_tags(utf8_decode($this->data['results'][0]['description'])), 0, 100); ?>]]></description>
							<link><![CDATA[<?PHP echo SITE . "?action=picture_metadata&id=" . $this->data['results'][0]['link_id']; ?>]]></link>
							<guid><![CDATA[<?PHP echo SITE . "?action=picture_metadata&id=" . $this->data['results'][0]['link_id']; ?>]]></guid>
					</item>
				</channel>
			</rss><?PHP
	
	}


	public function random_picture($site){

		$this->site = $site;
		$this->data = $this->choose_oer($site);
	
	}
	
	public function choose_oer($site){

		$statement = $site->db_layer->select_query("select title, description, link, link_id
											 from link_index
											 where link like '%.jpg' or link like '%.png'
											 order by rand()
											 limit 1", 
											 array(), 
											 $site->database_link);

		return array("results" => $site->db_layer->get_all_rows($statement, $site->database_link));
	
	}

}