<?PHP

class random_oer{

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
					<title><?PHP echo $this->site->get_text("A Random OER from Solvonauts"); ?></title>
					<link><?PHP echo SITE . urlencode("?action=random_oer"); ?></link>
					<description><?PHP echo $this->site->get_text("A Random OER from Solvonauts"); ?></description>
					<item>
							<title><![CDATA[<?PHP echo $this->data['results'][0]['title']; ?>]]></title>
							<description><![CDATA[<?PHP echo utf8_decode(strip_tags($this->data['results'][0]['description'])); ?>]]></description>
							<link><![CDATA[<?PHP echo SITE . "?action=all_metadata&id=" . $this->data['results'][0]['link_id']; ?>]]></link>
							<guid><![CDATA[<?PHP echo SITE . "?action=all_metadata&id=" . $this->data['results'][0]['link_id']; ?>]]></guid>
					</item>
				</channel>
			</rss><?PHP
	
	}


	public function random_oer($site){

		$this->site = $site;
		$this->data = $this->choose_oer($site);
	
	}
	
	public function choose_oer($site){

		$statement = $site->db_layer->select_query("select title, description, link, link_id
											 from link_index
											 order by rand()
											 limit 1", 
											 array(), 
											 $site->database_link);

		return array("results" => $site->db_layer->get_all_rows($statement, $site->database_link));
	
	}

}