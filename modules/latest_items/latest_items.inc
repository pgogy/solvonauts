<?PHP

class latest_items{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){
	
		return "display_latest_items";
		
	}

	public function latest_items($site){
		
		$this->data = $this->recent_harvested_items($site);
	
	}
	
	public function recent_harvested_items($site){

		$statement = $site->db_layer->select_query_multiple("SELECT site_address, MAX( first_harvested ) as last_update
FROM link_index
GROUP BY site_address", 
											 array(), 
											 $site->database_link);

		return array("results" => $site->db_layer->get_all_rows($statement, $site->database_link));
	
	}

}