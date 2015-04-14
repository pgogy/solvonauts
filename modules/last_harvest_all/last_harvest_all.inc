<?PHP

class last_harvest_all{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){
	
		return "display_last_harvest_results";
		
	}

	public function last_harvest_all($site){
		
		$this->data = $this->recent_harvested_items($site);
	
	}
	
	public function recent_harvested_items($site){

		$statement = $site->db_layer->select_query_multiple("SELECT title, link_id, title, link, description, subject, license, site_address
FROM link_index
GROUP BY site_address
ORDER BY last_updated DESC", 
											 array(), 
											 $site->database_link);

		return array("results" => $site->db_layer->get_all_rows($statement, $site->database_link));
	
	}

}