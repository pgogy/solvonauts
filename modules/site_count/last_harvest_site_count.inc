<?PHP

class site_count{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}

	public function display_count(){

		print_r($this->data['results']);

	}
	
	public function function_choice(){
	
		return "display_count";
		
	}

	public function site_count($site){

		$site_search = "";

		if(isset($_GET['site'])){
		
			$site_search = filter_var($_GET['site'], FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_HIGH) . " ";

		}
		
		$this->data = $this->recent_harvested_items($site, $site_search);
	
	}
	
	public function recent_harvested_items($site, $site_search = NULL){

		$statement = $site->db_layer->select_query_multiple("select title, link_id, title, link, description, subject, license, site_address
											 from link_index 
											 where link like :search", 
											 array(':search' => "%" . trim($site_search) . "%"), 
											 $site->database_link);


		return array("results" => $site->db_layer->get_count($statement));
	
	}

}