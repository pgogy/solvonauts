<?PHP

class api_visit_activity{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){
	
		return "display_data";
	
	}
	
	public function display_data(){
	
		foreach($this->data['results'] as $key => $result){
		
			unset($result[0]);
			unset($result[1]);
			unset($result[2]);
			
			$this->data['results'][$key] = $result;
		
		}
	
		print_r(json_encode($this->data));
	
	}

	public function api_visit_activity($site){
	
		$time = "";
	
		if(isset($_GET['time'])){
		
			$time = filter_var($_GET['time'], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH) . " ";

		}
		
		$this->data = $this->list_activity($site, $time);
	
	}
	
	public function list_activity($site, $time){
	
		if($time==""){
		
			$time = 0;
		
		}
	
		$statement = $site->db_layer->select_query_multiple("select link, activity_link.link_id, time_clicked
											 from activity_link, link_index where activity_link.link_id = link_index.link_id AND time_clicked > :time", 
											 array(":time" => $time), 
											 $site->database_link);	
									 
		return array("results" => $site->db_layer->get_all_rows($statement, $site->database_link));
	
	}

}