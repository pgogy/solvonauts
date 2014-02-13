<?PHP

class picture_search{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){
	
		return "display_picture_search_results";
	
	}

	public function picture_search($site){
	
		$term = "";
	
		if(isset($_GET['term'])){
		
			$term = filter_var(urldecode($_GET['term']), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

		}
		
		$offset = 0;
		
		if(isset($_GET['offset'])){
		
			$offset = $_GET['offset']; 

		}
		
		$this->data = $this->search_oer($site, $term, filter_var($offset, FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH));
	
	}
	
	public function search_oer($site, $word, $offset = NULL){
	
		if($offset){
		
			$extra = "offset " . $offset * PAGINATION;
	
		}else{
		
			$extra = "";
		
		}
		
		$word = str_replace(" ","?",$word);
		
		$statement = $site->db_layer->select_query_multiple("select SQL_CALC_FOUND_ROWS title, link_id, title, link, description, subject, license, site_address, length(title) - length(replace(title,:title,'')) +
											 length(description) - length(replace(description,:title,'')) +
											 length(subject) - length(replace(subject,:title,'')) as changed
											 from link_index 
											 where (title like :inner or subject like :inner or description like :inner)
											 and (link like'%.jpg%' or link like'%.png%' )
											 order by changed desc LIMIT " . PAGINATION . " " . $extra, 
											 array(':inner' => "%" . $word . "%", ':title' => $word), 
											 $site->database_link);
											 
		$statement_rows = $site->db_layer->select_query("SELECT FOUND_ROWS();", 
														 array(),
														 $site->database_link);
														 
											 
		return array("offset" => $offset, "term" => $word, "result_count" => $site->db_layer->get_row($statement_rows, $site->database_link), "results" => $site->db_layer->get_all_rows($statement, $site->database_link));
	
	}

}