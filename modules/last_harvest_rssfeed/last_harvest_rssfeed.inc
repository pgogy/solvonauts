<?PHP

class last_harvest_rssfeed{

	var $data;
	var $time;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){

		return "generate_rss";
	
	}
	
	public function generate_rss(){
	
		$time = time() % 86400;
		$time = (time() - $time) + 43200;	

		echo '<?xml version="1.0" encoding="ISO-8859-1"?>';

		?><rss version="2.0">
				<channel>
					<ttl>720</ttl>
					<title><?PHP echo $this->site->get_text("Recent harvests for Solvonauts"); ?></title>
					<link><?PHP echo SITE . urlencode("?action=last_harvest_rss"); ?></link>
					<description><?PHP echo $this->site->get_text("Items harvested by Solvonauts in the last 24 hours"); ?></description><?PHP

					if($this->data['result_count'][0]!=0){

						 ?><item>
							<title><?PHP echo $this->site->get_text_replace("?total items harvested by Solvonauts in the last 24 hours", array("?total" => $this->data['result_count'][0])); ?></title>
							<description><?PHP echo $this->site->get_text_replace("?total items harvested by Solvonauts in the last 24 hours", array("?total" => $this->data['result_count'][0])); ?></description>
							<link><![CDATA[<?PHP echo SITE . "?action=last_harvest&time=" . $time; ?>]]></link>
							<guid><![CDATA[<?PHP echo SITE . "?action=last_harvest&time=" . $time; ?>]]></guid>
						  </item><?PHP
				
					}

					?>
				</channel>
			</rss><?PHP
	
	}


	public function last_harvest_rssfeed($site){

		$this->site = $site;
		$time = time() % 86400;
		$time = (time() - $time) + 43200;	
		$this->time = $time;	
		$this->data = $this->recent_harvested_items($site, $time);
	
	}
	
	public function recent_harvested_items($site, $time){

		$statement = $site->db_layer->select_query_multiple("select SQL_CALC_FOUND_ROWS title, link, link_id
											 from link_index
											 where first_harvested > (:maximum - 86400)
											 and (:maximum + 86400) > first_harvested
											 order by first_harvested DESC", 
											 array(':maximum' => $time), 
											 $site->database_link);

		$statement_rows = $site->db_layer->select_query("SELECT FOUND_ROWS();", 
									array(),
									$site->database_link);

		return array("result_count" => $site->db_layer->get_row($statement_rows, $site->database_link), "results" => $site->db_layer->get_all_rows($statement, $site->database_link));
	
	}

}