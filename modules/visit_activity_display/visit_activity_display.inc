<?PHP

class visit_activity_display{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){
	
		return "display_visit_activity_display_results";
		
	}

	public function visit_activity_display($site){
	
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
	
		$statement = $site->db_layer->select_query_multiple("select link, link_index.link_id, title, description, subject, license, site_address, count(link) as total
											 from activity_link, link_index where activity_link.link_id = link_index.link_id AND time_clicked > :time group by link limit 50", 
											 array(":time" => $time), 
											 $site->database_link);	
									 
		return array("results" => $site->db_layer->get_all_rows($statement, $site->database_link));
	
	}

}