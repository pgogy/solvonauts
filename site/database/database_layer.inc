<?PHP

class database_layer{

	var $db_layer;

	public function database_layer(){	
	
	
	}
	
	public function database_connect(){
	
		echo "HERE";
	
	}
	
	public function select_query_multiple($query, $variables, $database_link){
	
	}
	
	public function select_query($query, $variables, $database_link){
	
	}
	
	public function insert_query($query, $variables, $database_link){
	
	}
	
	public function delete_query($query, $variables, $database_link){
	
	}
	
	private function update_query($query, $variables, $database_link){
	
	}
	
	public function last_insert_id($database_link){
	
		
	}
	
	public function get_all_rows($statement){
	
	
	}
	
	public function get_row($statement){
	
	
	}


}