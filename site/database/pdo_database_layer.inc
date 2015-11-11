<?PHP

class pdo_database_layer extends database_layer{

	public function pdo_database_layer(){	
	
	}
	
	public function database_connect(){

		$db = new PDO('mysql:dbname=' . DB_NAME . ';host=' . DB_HOST, DB_USERNAME_SEARCH, DB_PASSWORD);
		return $db;
	
	}
	
	public function select_query_multiple($query, $variables, $database_link){
	
		$statement = $database_link->prepare($query);
		$statement->execute($variables);
		return $statement;
	
	}
	
	public function select_query($query, $variables, $database_link){
	
		$statement = $database_link->prepare($query);
		$statement->execute($variables);
		return $statement;
	
	}
	
	public function insert_query($query, $variables, $database_link){
	
		$statement = $database_link->prepare($query);
		$statement->execute($variables);
		return $statement;
	
	}
	
	public function delete_query($query, $variables, $database_link){
	
		$statement = $database_link->prepare($query);
		$statement->execute($variables);
		return $statement;
	
	}
	
	public function update_query($query, $variables, $database_link){
	
		$statement = $database_link->prepare($query);
		$statement->execute($variables);
		return $statement;
	
	}
	
	public function last_insert_id($database_link){
	
		return $database_link->lastInsertId();
		
	}
	
	public function get_all_rows($statement){
	
		return $statement->fetchAll();
	
	}

	public function get_count($statement){
	
		return $statement->rowCount();
	
	}
	
	public function get_row($statement){
	
		return $statement->fetch();
	
	}

}