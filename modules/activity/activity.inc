<?PHP

class activity{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){
	
		return "display_activity_results";
	
	}

	public function activity($site){
	
		$this->data = $this->add_activity($site, filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH));
	
	}
	
	public function add_activity($site, $id){
	
		$statement = $site->db_layer->insert_query("insert into activity_link(link_id,time_clicked)values(:id,:time)", 
											 array(':id' => $id, ':time' => time()), 
											 $site->database_link);
	
	}

}