<?PHP

class subject{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}

	public function function_choice(){
	
		return "display_subject_results";
	
	}

	public function subject($site){
	
		$term = "";
		
		if(isset($_GET['subject'])){
		
			$term = $_GET['subject']; 

		}
		
		$offset = 0;
		
		if(isset($_GET['offset'])){
		
			$offset = $_GET['offset']; 

		}
		
		$this->data = $this->search_subject($site, $term, filter_var($offset, FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH));
	
	}

	public function search_subject($site, $word, $offset = NULL){
	
		if($offset){
		
			$extra = "offset " . $offset * PAGINATION;
	
		}else{
		
			$extra = "";
		
		}
		
		if(preg_match("/^[\p{L}A-Za-z\s\S]+$/u", $word)){
	
			$statement = $site->db_layer->select_query_multiple("select SQL_CALC_FOUND_ROWS title, link_id, title, link, description, subject, license, site_address, 
												 length(subject) - length(replace(subject,:title,'')) as changed
												 from link_index where subject like :inner order by changed desc LIMIT " . PAGINATION . " " . $extra, 
												 array(':inner' => "%" . $word . "%", ':title' => $word), 
												 $site->database_link);
												 
			$statement_rows = $site->db_layer->select_query("SELECT FOUND_ROWS();", 
															 array(),
															 $site->database_link);
															 
												 
			return array("offset" => $offset, "term" => $word, "result_count" => $site->db_layer->get_row($statement_rows, $site->database_link), "results" => $site->db_layer->get_all_rows($statement, $site->database_link));
			
		}
	
	}

}