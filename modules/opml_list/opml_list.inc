<?PHP

class opml_list{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){
	
		return "display_opml_list_results";
	
	}

	public function opml_list($site){
		
		$this->data = $this->list_feeds($site);
	
	}

	public function list_feeds($site){
	
		$statement = $site->db_layer->select_query_multiple("select site_address from oer_site_list", 
											 array(), 
											 $site->database_link);														 
											 
		return array("results" => $site->db_layer->get_all_rows($statement, $site->database_link));
	
	}

}