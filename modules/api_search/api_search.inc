<?PHP

class api_search{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){
	
		return "display_api_search_results";
	
	}

	public function api_search($site){
	
		$term = "";
	
		if(isset($_GET['term'])){
		
			$term = filter_var($_GET['term'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH) . " ";

		}
		
		$this->data = $this->search_oer($site, $term);
	
	}
	
	public function search_oer($site, $word){
	
		$statement = $site->db_layer->select_query_multiple("select title, link, description, subject, license, site_address, first_harvested, length(title) - length(replace(title,:title,'')) +
											 length(description) - length(replace(description,:title,'')) +
											 length(subject) - length(replace(subject,:title,'')) as changed
											 from link_index 
											 where title like :inner or subject like :inner or description like :inner
											 order by changed desc", 
											 array(':inner' => "%" . $word . "%", ':title' => $word), 
											 $site->database_link);
											 
		$valid_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);  
		
		$data = $site->db_layer->get_all_rows($statement, $site->database_link);
		
		$statement = $site->db_layer->insert_query("insert into activity_search(term,results,ip,time_searched)values(:term,:results,:ip,:time)", 
											 array(':term' => $word, ':results' => count($data) , ':ip' => "API_" . $valid_ip, ':time' => time()), 
											 $site->database_link);
											 
		return array("term" => $word, "results" => $data);
	
	}

}