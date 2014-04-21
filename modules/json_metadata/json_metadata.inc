<?PHP

class json_metadata{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){
	
		return "display_all_metadata_json";
	
	}
	
	public function display_all_metadata_json(){
	
		$return_data = new StdClass();
		
		$return_data->link = $this->data['results'][0]['link'];
	
		foreach($this->data['results'] as $item){
		
			if(!isset($return_data->{$item['term']})){
			
				$return_data->{$item['term']} = $item[2];	
			
			}else{
			
				if(!is_array($return_data->{$item['term']})){
				
					$return_data->{$item['term']} = array($return_data->{$item['term']});
				
				}
			
				array_push($return_data->{$item['term']},$item[2]); 	
			
			}
		
		}
		
		header('Content-type: application/json');
		
		echo json_encode($return_data);	
	
	}

	public function json_metadata($site){
	
		$this->data = $this->get_metadata($site, filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH));
	
	}
	
	public function get_metadata($site, $id){
	
		$statement = $site->db_layer->select_query_multiple("SELECT link, term, node_value
																FROM  `link_table` , link_term, node_data, node_term
																WHERE link_table.link_id = :id
																AND link_table.link_id = link_term.link_id
																AND link_term.term_id = node_term.term_id
																AND node_term.node_id = node_data.node_id", 
											 array(':id' => $id), 
											 $site->database_link);
											 
		return array("results" => $site->db_layer->get_all_rows($statement, $site->database_link));
	
	}

}