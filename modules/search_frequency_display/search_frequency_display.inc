<?PHP

class search_frequency_display{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){
	
		return "display_search_frequency_display_results";
		
	}

	public function search_frequency_display($site){
	
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
	
		$statement = $site->db_layer->select_query_multiple("select term, count(term) as total
											 from activity_search group by term order by total DESC limit 50", 
											 array(":time" => $time), 
											 $site->database_link);	
									 
		return array("results" => $site->db_layer->get_all_rows($statement, $site->database_link));
	
	}

}