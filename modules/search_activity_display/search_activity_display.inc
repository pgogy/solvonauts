<?PHP

class search_activity_display{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){
	
		return "display_search_activity_display_results";
		
	}

	public function search_activity_display($site){
	
		$time = "";
	
		if(isset($_GET['time'])){
		
			$time = filter_var($_GET['time'], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH) . " ";

		}
		
		$this->data = $this->list_search($site, $time);
	
	}
	
	public function list_search($site, $time){
	
		if($time==""){
		
			$time = 0;
		
		}
	
		$statement = $site->db_layer->select_query_multiple("select term, results, time_searched
											 from activity_search where time_searched > :time order by time_searched DESC limit 50", 
											 array(":time" => $time), 
											 $site->database_link);	
									 
		return array("results" => $site->db_layer->get_all_rows($statement, $site->database_link));
	
	}

}