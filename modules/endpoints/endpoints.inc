<?PHP



class endpoints{

	var $data;

	public function return_data(){

		return $this->data;
	}

	public function function_choice(){

		return "endpoints_display";	

	}

	public function endpoints_display(){

		$counter = 1;
		foreach($this->data['results'] as $row){
			echo $counter++ . " " . $row[1] . " " . $row[0] . "<br />";
		}	

	}

	public function endpoints($site){

		$this->data = $this->list_feeds($site);

	}

	public function list_feeds($site){

		$statement = $site->db_layer->select_query_multiple("select site_address, url_type from oer_site_list", 

											 array(), 

											 $site->database_link);											 

		return array("results" => $site->db_layer->get_all_rows($statement, $site->database_link));
	

	}

}