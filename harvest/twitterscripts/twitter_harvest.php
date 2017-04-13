<?PHP

	class twitter_harvest extends data{

		private $language;

		public function __construct($language){

			$this->language = $language;

		}

		public function classification(){

			$classification = new StdClass();

			$classification->type = "Twitter Analysis";
			$classification->column = "Twitter setup and harvest";
			$classification->link = "?data=twitter_harvest";
			$classification->name = "Twitter harvest";

			return $classification;

		}

		public function head($file_process){

			$scripts = $file_process->read_folder(dirname(__FILE__) . "/scripts");

			$output = "";

			while($script = array_pop($scripts)){

				$output .= "\t<script type='text/javascript' language='javascript' src='look/" . $theme . "/scripts/" . $script . "'></script>\n";

			}

			$css = $file_process->read_folder(dirname(__FILE__) . "/css");

			while($style = array_pop($css)){

				$output .= "\t\t<link href='look/" . $theme . "/css/" . $style . "' rel='stylesheet' type='text/css'>\n";

			}

		}

		public function index(){

			if(!isset($_GET['action'])){

				$output = "<h2>" . $this->language->translate("data/twitter_setup", "Twitter harvest") . "</h2>
						   <ul>
								<li>
									<a href='?data=twitter_harvest&action=instructions'>" . $this->language->translate("data/twitter_harvest", "Instructions") . "</a>
								</li>
								<li>
									<a href='?data=twitter_harvest&action=hashtag'>" . $this->language->translate("data/twitter_harvest", "Twitter harvest search term") . "</a>
								</li>
								<li>
									<a href='?data=twitter_harvest&action=list_harvest'>" . $this->language->translate("data/twitter_harvest", "Twitter harvest list") . "</a>
								</li>
								<li>
									<a href='?data=twitter_harvest&action=usertweets'>" . $this->language->translate("data/twitter_harvest", "Twitter harvest user tweets") . "</a>
								</li>
						   </ul>";

			}else{

				$output = self::$_GET['action']();

			}

			return $output;

		}

		private function instructions(){

			$output = $this->language->translate_help("data/twitter_harvest", "help");

			return $output . "<p><a href='?data=twitter_harvest'>" . $this->language->translate("data/twitter_harvest", "Return to Twitter Harvest") . "</a></p>";

		}

		private function is_twitter_setup(){

			require_once("core/file_handling/file_handling.php");
			$file_process = new file_handling();

			$twitter_setup = false;

			if($file_process->file_exists_check("data/twitter_harvest/files/ckey")){

				if($file_process->file_exists_check("data/twitter_harvest/files/csecret")){

					if($file_process->file_exists_check("data/twitter_harvest/files/okey")){

						if($file_process->file_exists_check("data/twitter_harvest/files/osecret")){

							$twitter_setup = true;

						}

					}

				}

			}

			return $twitter_setup;

		}

		private function get_twitter_setup(){

			require_once("core/file_handling/file_handling.php");
			$file_process = new file_handling();
			$ckey = $file_process->file_get_all("data/twitter_setup/files/ckey");
			$csecret = $file_process->file_get_all("data/twitter_setup/files/csecret");
			$okey = $file_process->file_get_all("data/twitter_setup/files/okey");
			$osecret = $file_process->file_get_all("data/twitter_setup/files/osecret");

			return array($ckey, $csecret, $okey, $osecret);

		}

		private function get_tweets($counter,$connection,$url,$stem,$time){

			echo $url . "<br />";
			echo urldecode($url) . "<br />";

			$content = $connection->get($url);
			$content = $connection->get($url);

			if(isset($_POST['screen_name'])){

				$term = urlencode($_POST['screen_name']) . "_" . urlencode($_POST['list_name']);

			}else{

				$term = urlencode($_POST['term']);

			}

			require_once("core/file_handling/file_handling.php");
			$file_process = new file_handling();
			$file_process->create_file("data/twitter_harvest/files/" . $term . "_" . $time . "_" . $counter . ".json", serialize($content));

			if(strpos($url,"list")!==FALSE){

				$last = array_pop($content);

				if(isset($last->id_str)){

					if(strpos($url,"max_id")===FALSE){

						$new_url = $url . "&max_id=" . $last->id_str;

					}else{

						$parts = explode("&max_id", $url);

						$new_url = $parts[0] . "&max_id=" . $last->id_str;

					}

					if($new_url!=$stem){

						echo "OOOOPS";
						
						die();

						$this->get_tweets($counter+1, $connection, $new_url, $url, $time);

					}

				}

			}else if(isset($content->search_metadata->next_results)){

				$this->get_tweets($counter+1, $connection, $stem . $content->search_metadata->next_results, $stem, $time);

			}else if(isset($content->statuses)){
			
				echo "IN HERE<br />";

				echo count($content->statuses) . "<br />";
				
				echo "<pre>***";
				print_r($content->statuses);
				echo "***</pre>";

				if(count($content->statuses)==99){

					echo "IN 99 <Br />";

					$url = $url . "&max_id=" . $content->statuses[98]->id_str;

					$this->get_tweets($counter+1, $connection, $url, $stem, $time);

				}else{

					echo $content->statuses . "<br>";

					if(count($content->statuses)>=1){

						echo "IN 1 <Br />";

						$tweet = array_pop($content->statuses);

						$new_url = $url . "&max_id=" . $tweet->id_str;
					
						$this->get_tweets($counter+1, $connection, $new_url, $url, $time);

					}else{
					
						
					}

				}

			}

		}

		private function configure_twitter_harvest($details){

			require_once('data/twitter_harvest/twitteroauth/OAuth.php');
			require_once('data/twitter_harvest/twitteroauth/twitteroauth.php');

			define('CONSUMER_KEY', $details[0]);
			define('CONSUMER_SECRET', $details[1]);
			define('OAUTH_CALLBACK', '');

			function getConnectionWithAccessToken($oauth_token, $oauth_token_secret) {		  
			  $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $oauth_token, $oauth_token_secret);
			  return $connection;
			}

			$connection = getConnectionWithAccessToken($details[2], $details[3]);

			return $connection;

		}

		private function process_user_tweets($time){

			require_once("core/file_handling/file_handling.php");
			$file_process = new file_handling();
			$harvest_files = $file_process->read_folder("data/twitter_harvest/files/");

			$aggregate = array();

			while($file = array_shift($harvest_files)){

				$res = explode($time,$file);

				if(count($res)!==1){

					array_push($aggregate, $file);

				}

			}

			$tweets = array();

			$counter = 0;

			while($file = array_shift($aggregate)){

				$counter++;

				$content = unserialize($file_process->file_get_all("data/twitter_harvest/files/" . $file));

				$tweets = array_merge($tweets, $content);

			}

			$file_process->create_file("data/twitter_harvest/files/usertweets/" . urlencode($_POST['term']) . "_" . $time . ".json", serialize($tweets));

			return array($counter,count($tweets));

		}


		private function aggregate($time){

			require_once("core/file_handling/file_handling.php");
			$file_process = new file_handling();
			$harvest_files = $file_process->read_folder_files_only("data/twitter_harvest/files/");

			$aggregate = array();

			while($file = array_shift($harvest_files)){

				if(count(explode($time,$file))!==1){

					array_push($aggregate, $file);

				}

			}

			$tweets = array();

			$counter = 0;

			while($file = array_shift($aggregate)){

				$counter++;

				$content = unserialize($file_process->file_get_all("data/twitter_harvest/files/" . $file));

				if(isset($content->statuses)){

					$content_tweets = $content->statuses;
					if(is_array($content_tweets)){
						if(count($content_tweets)!==0){
							$tweets = array_merge($tweets, $content_tweets);
						}
					}

				}else{

					if(count($content)!=0){
						$content_tweets = $content;
						if(is_array($content_tweets)){
							if(count($content_tweets)!=0){
								$tweets = array_merge($tweets, $content_tweets);
							}
						}

					}

				}

			}

			$keep = array();

			$already = array();

			foreach($tweets as $tweet){

				if(!in_array($tweet->id_str, $already)){

					array_push($already,$tweet->id_str);
					array_push($keep,$tweet);

				}

			}

			$file_process->create_file("data/twitter_harvest/files/aggregate/" . urlencode($_POST['term']) . "_" . $time . ".json", serialize($keep));

			return array($counter,count($tweets));

		}

		private function list_harvest(){

			if(count($_POST)!==0){

				$setup = $this->get_twitter_setup();

				$connection = $this->configure_twitter_harvest($setup);

				$url = "lists/statuses.json?slug=" . urlencode($_POST['list_name']) . "&owner_screen_name=" . urlencode($_POST['screen_name']) . "&count=200";

				$time = time();

				$this->get_tweets(0,$connection,$url,"lists/statuses.json", $time);

				$data = $this->aggregate($time);

				$output = "<p>" . $data[0] . " " . $this->language->translate("data/twitter_harvest", "Files saved") . " / " . $data[1] . " " . $this->language->translate("data/twitter_harvest", "Tweets harvested") . "</p>";
				return $output . "<p><a href='?data=twitter_harvest'>" . $this->language->translate("data/twitter_harvest", "Return to Twitter harvest") . "</a></p>";

			}else{

				if($this->is_twitter_setup()){

					return "<h2>" . $this->language->translate("data/twitter_harvest", "Twitter is not set up") . "</h2>
					<p><a href='?data=twitter_setup'>" . $this->language->translate("data/twitter_harvest", "Setup twitter") . "</a></p>";

				}

				$output = "<h2>" . $this->language->translate("data/twitter_harvest", "Twitter list harvest") . "</h2>
							<form action='' method='POST'>";

				$output .= "<p>" . $this->language->translate("data/twitter_harvest", "Enter user name of list owner") . "</p>
						<input type='text' size=100 value='" . $this->language->translate("data/twitter_harvest", "Enter the term you wish to search twitter for") . "' name='screen_name' />
						<p>" . $this->language->translate("data/twitter_harvest", "Enter list name") . "</p>
						<input type='text' size=100 value='" . $this->language->translate("data/twitter_harvest", "Enter the term you wish to search twitter for") . "' name='list_name' />
						
						<input type='submit' value='" . $this->language->translate("data/twitter_harvest", "Search Twitter") . "' />
					</form>";

				return $output;

			}

		}

		private function hashtag(){

			if(count($_POST)!==0){

				$setup = $this->get_twitter_setup();

				$connection = $this->configure_twitter_harvest($setup);

				$url = "search/tweets.json?q=" . urlencode($_POST['term']) . "&count=100";

				$time = time();

				$this->get_tweets(0,$connection,$url,"search/tweets.json", $time);

				$data = $this->aggregate($time);

				$output = "<p>" . $data[0] . " " . $this->language->translate("data/twitter_harvest", "Files saved") . " / " . $data[1] . " " . $this->language->translate("data/twitter_harvest", "Tweets harvested") . "</p>";
				return $output . "<p><a href='?data=twitter_harvest'>" . $this->language->translate("data/twitter_harvest", "Return to Twitter harvest") . "</a></p>";

			}else{

				if($this->is_twitter_setup()){

					return "<h2>" . $this->language->translate("data/twitter_harvest", "Twitter is not set up") . "</h2>
					<p><a href='?data=twitter_setup'>" . $this->language->translate("data/twitter_harvest", "Setup twitter") . "</a></p>";

				}

				$output = "<h2>" . $this->language->translate("data/twitter_harvest", "Twitter hashtag harvest") . "</h2>
							<form action='' method='POST'>";

				$output .= "<p>" . $this->language->translate("data/twitter_harvest", "Enter a search term") . "</p>
						<input type='text' size=100 value='" . $this->language->translate("data/twitter_harvest", "Enter the term you wish to search twitter for") . "' name='term' />
						<input type='submit' value='" . $this->language->translate("data/twitter_harvest", "Search Twitter") . "' />
					</form>";

				return $output;

			}

		}

		private function usertweets(){

			if(count($_POST)!==0){

				$setup = $this->get_twitter_setup();

				$connection = $this->configure_twitter_harvest($setup);

				$url = "statuses/user_timeline.json?screen_name=" . $_POST['term'] . "&count=200";

				$time = time();

				$this->get_tweets(0,$connection,$url,"statuses/user_timeline.json", $time);

				$data = $this->process_user_tweets($time);

				$output = "<p>" . $data[0] . " " . $this->language->translate("data/twitter_harvest", "Files saved") . " / " . $data[1] . " " . $this->language->translate("data/twitter_harvest", "Tweets harvested") . "</p>";
				return $output . "<p><a href='?data=twitter_harvest'>" . $this->language->translate("data/twitter_harvest", "Return to Twitter harvest") . "</a></p>";

			}else{

				if($this->is_twitter_setup()){

					return "<h2>" . $this->language->translate("data/twitter_harvest", "Twitter is not set up") . "</h2>
					<p><a href='?data=twitter_setup'>" . $this->language->translate("data/twitter_harvest", "Setup twitter") . "</a></p>";

				}

				$output = "<h2>" . $this->language->translate("data/twitter_harvest", "Twitter hashtag harvest") . "</h2>
							<form action='' method='POST'>";

				$output .= "<p>" . $this->language->translate("data/twitter_harvest", "Enter a search term") . "</p>
						<input type='text' size=100 value='" . $this->language->translate("data/twitter_harvest", "Enter the term you wish to search twitter for") . "' name='term' />
						<input type='submit' value='" . $this->language->translate("data/twitter_harvest", "Search Twitter") . "' />
					</form>";

				return $output;

			}

		}

	}