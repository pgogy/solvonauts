<?PHP

class api_url_exists{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){
	
		return "display_api_url_result";
	
	}

	public function api_url_exists($site){
	
		$url = "";
	
		if(isset($_GET['url'])){
		
			$url = filter_var($_GET['url'], FILTER_SANITIZE_URL, FILTER_FLAG_STRIP_HIGH);

		}
		
		$this->data = $this->search_for_url($site, $url);
	
	}
	
	public function search_for_url($site, $url){
	
		$statement = $site->db_layer->select_query("select link_id, link 
											 from link_index 
											 where link like :url", 
											 array(':url' => $url), 
											 $site->database_link);
											 
		return $site->db_layer->get_all_rows($statement, $site->database_link);
		
	}
	
	public function display_api_url_result(){
		
		if(isset($this->data[0]['link_id'])){
		
			echo json_encode(array("true",$this->data[0]['link_id'],$this->data[0]['link']));
		
		}else{
		
			echo json_encode(array("false"));
		
		}
		
	
	}

}