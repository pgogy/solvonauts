<?PHP

class last_harvest_site{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){
	
		return "display_last_harvest_results";
		
	}

	public function last_harvest_site($site){

		$site_search = "";

		if(isset($_GET['site'])){
		
			$site_search = filter_var($_GET['site'], FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH) . " ";

		}
		
		$this->data = $this->recent_harvested_items($site, $site_search);
	
	}
	
	public function recent_harvested_items($site, $site_search = NULL){

		$statement = $site->db_layer->select_query_multiple("select title, link_id, title, link, description, subject, license, site_address
											 from link_index 
											 where link like :search
											 order by first_harvested DESC
											 limit 100", 
											 array(':search' => "%" . trim($site_search) . "%"), 
											 $site->database_link);

		return array("results" => $site->db_layer->get_all_rows($statement, $site->database_link));
	
	}

}