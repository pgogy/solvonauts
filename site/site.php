<?PHP



class site{



	var $db_layer;

	var $database_link;

	var $language;

	

	public function site(){

	

		require_once(__DIR__ . "/database/database.inc");



		require_once(__DIR__ . "/theme/theme/theme.inc");



		if(require_once(__DIR__ . "/theme/" . SITE_THEME . "/" . SITE_THEME . ".inc")){



			$theme = SITE_THEME;

			$this->language = LANGUAGE;

			$this->theme = new $theme($this);

			$this->database_connect();

			

			if(isset($_GET['action'])){

			

				if($_GET['action']!="index"){

			

					if(preg_match("/^[a-z_]+$/",filter_var($_GET['action'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH))){

					

						$action_class = filter_var($_GET['action'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);			

						

						if(file_exists(__DIR__ . "/../modules/" . $action_class . "/" . $action_class . ".inc")){



							require_once(realpath(__DIR__ . "/../modules/" . $action_class . "/" . $action_class . ".inc"));	

							$action = new $action_class($this);		

							$function = $action->function_choice();

		

							if(is_callable(array($this->theme, $function))){

									

								$this->theme->$function($action->return_data());

					

							}else if(is_callable(array($action, $function))){



								$action->$function();

								

							}else{

							

								echo "ERROR";

							

							}

						

						}else{

						

							echo "404";

						

						}

					

					}

					

				}else{

				

					$function = "display_index";

					

					if(is_callable(array($this->theme, $function))){

				

						$this->theme->$function();

				

					}

				

				}

					

			}else{

			

				$function = "display_index";

					

				if(is_callable(array($this->theme, $function))){

				

					$this->theme->$function();

				

				}

			

			}

			

		}else{

		

			echo "invalid theme";

		

		}

		

	}

	

	public function database_connect(){

		

		if(defined("DB_TYPE")){

		

			if(trim(DB_TYPE)!=""){

			

				$db_type = DB_TYPE . "_database_layer";



				$this->db_layer = new $db_type();

					

			}

				

		}

		

		$this->database_link = $this->db_layer->database_connect();

	

	}



	public function get_text($string){

	

		if(file_exists(__DIR__ . "/languages/" . $this->language . "/" . substr(urlencode($string),0,200) . ".txt")){



			return file_get_contents(__DIR__ . "/languages/" . $this->language . "/" . substr(urlencode($string),0,200) . ".txt");

			

		}else{



			file_put_contents(__DIR__ . "/languages/" . $this->language . "/" . substr(urlencode($string),0,200) . ".txt", $string);

			return file_get_contents(__DIR__ . "/languages/" . $this->language . "/" . substr(urlencode($string),0,200) . ".txt");

		

		}

		

	}

	

	public function get_text_replace($string, $change){

	

		if(file_exists(__DIR__ . "/languages/" . $this->language . "/" . urlencode($string) . ".txt")){

		

			$text = file_get_contents(__DIR__ . "/languages/" . $this->language . "/" . urlencode($string) . ".txt");

			

			foreach($change as $term => $replace){

			

				$text = str_replace($term, $replace, $text);

			

			}

			

			return $text;

			

		}else{

		

			file_put_contents(__DIR__ . "/languages/" . $this->language . "/" . urlencode($string) . ".txt", $string);

			

			$text = file_get_contents(__DIR__ . "/languages/" . $this->language . "/" . urlencode($string) . ".txt");

		

			foreach($change as $term => $replace){

			

				$text = str_replace($term, $replace, $text);

			

			}

		

			return $text;

		

		}

		

	}



}

