<?PHP

	class fourzerofour_check{
	
		function __construct(){

			include dirname(__FILE__) . "/../config.php";
			include dirname(__FILE__) . "/../site/database/database_layer.inc";
			include dirname(__FILE__) . "/../site/database/" . DB_TYPE . "_database_layer.inc";
			$db_class = DB_TYPE . "_database_layer";
			$this->database = new $db_class();
			$this->link = $this->database->database_connect();
		
		}

		function get_http_response_code($url) {
    			$headers = get_headers($url);
    			return substr($headers[0], 9, 3);
		}
		
		function process_404(){	

			$statement = $this->database->select_query("SELECT link FROM 404_check WHERE live=1", array(), $this->link);
			$data = $this->database->get_all_rows($statement);
			
			foreach($data as $url){
				$code = $this->get_http_response_code($url['link']);
				if($code!=200){

					$this->database->update_query("update 404_check set error_code = :code, last_check = :last_checked, times_checked = times_checked + 1 where link=:link", array(":last_checked" => time(), ":code" => $code, ":link" => utf8_encode($url['link'])), $this->link);

				}else{
					$this->database->update_query("update 404_check set error_code = :code, last_check = :last_checked, live = 0, times_checked = times_checked + 1 where link=:link", array(":last_checked" => time(), ":code" => $code, ":link" => utf8_encode($url['link'])), $this->link);
			
				}

				echo $url['link'] . " " . $code . "\n";
			}

		}
	}

	$check = new fourzerofour_check();
	$check->process_404();