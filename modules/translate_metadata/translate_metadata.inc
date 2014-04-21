<?PHP

class translate_metadata{

	var $data;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){
	
		return "display_translate_metadata";
	
	}
	
	public function display_translate_metadata(){
		
		header('Content-type: application/csv');
		
		foreach($this->data['results'] as $data){
	
			echo utf8_decode($data['node_value']) . "," . $data['total'] . "\n";

		}
		
	}

	public function translate_metadata($site){
	
		$this->data = $this->get_metadata($site);
	
	}
	
	public function get_metadata($site){
	
		if(isset($_GET['offset'])){
	
			if(is_numeric($_GET['offset'])){
			
				$offset = $_GET['offset'];
			
			}else{
			
				$offset = 0;
			
			}
		
		}else{
			
			$offset = 0;
		
		}
		
		$statement = $site->db_layer->select_query_multiple("SELECT node_value, COUNT( node_term.node_id ) AS total
																FROM node_term, node_data
																WHERE node_term.node_id = node_data.node_id
																GROUP BY node_term.node_id
																ORDER BY total DESC
																LIMIT " . $offset . " , 50", 
											array(),
											$site->database_link);
											
		return array("results" => $site->db_layer->get_all_rows($statement, $site->database_link));
	
	}

}