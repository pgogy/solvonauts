<?PHP

class last_harvest{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){
	
		return "display_last_harvest_results";
		
	}

	public function last_harvest($site){

		$time = "";

		if(isset($_GET['time'])){
		
			$time = filter_var($_GET['time'], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH) . " ";

		}
		
		$this->data = $this->recent_harvested_items($site, $time);
	
	}
	
	public function recent_harvested_items($site, $time = NULL){

		if($time==NULL){
	
			$statement = $site->db_layer->select_query("select max(first_harvested) as maximum from link_index", 
											 array(), 
											 $site->database_link);
									 
			$data = $site->db_layer->get_row($statement, $site->database_link);

			$time = $data['maximum'];

		}

		$statement = $site->db_layer->select_query_multiple("select SQL_CALC_FOUND_ROWS title, link_id, title, link, description, subject, license, site_address
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