<?PHP

class flickr_attribute{

	public $site;

	public function flickr_attribute($site){
		$this->site = $site;
	}

	public function function_choice(){
	
		return "display_flickr_attribute";
	
	}
		
	public function display_flickr_attribute(){
	
		echo $this->site->theme->display_header($this->site->get_text("Search results"));
		
		if(!isset($_POST['flickr_url'])){
			?><p>
				<?PHP echo $this->site->get_text("Flickr URL - enter a FlickR URL and if the picture is licensed have it attributed."); ?>
			</p>
			<p>
				<?PHP echo $this->site->get_text("The URL should look like https://www.flickr.com/photos/username/photo id number/"); ?>
			</p>
			<form enctype="multipart/form-data" action="" method="POST">
				<input type="hidden" name="MAX_FILE_SIZE" size="1000000" />
				<input type="text" value="<?PHP echo $this->site->get_text("Enter URL here"); ?>" size="35" name="flickr_url" />
				<input type="submit" value="<?PHP echo $this->site->get_text("Get Picture"); ?>" class="button" />
			</form>
			<?PHP
			
		}else{
	
			if(count(explode("flickr.com",$_POST['flickr_url']))==1){
				die("This doesn't seem to be a valid FlickR URL");
			}
			
			$picture_url = $_POST['flickr_url'];

			$data = explode("/",$_POST['flickr_url']);
			$id = $data[5];	

			$params = array(
				'api_key'	=> '833ceebce63ad8d8a3eee8ff1a0841d0',
				'method'	=> 'flickr.photos.getInfo',
				'photo_id'	=> $id,
				'format' => 'php_serial'
			);

			$encoded_params = array();

			foreach ($params as $k => $v){
				$encoded_params[] = urlencode($k).'='.urlencode($v);
			}

			$url = "https://api.flickr.com/services/rest/?".implode('&', $encoded_params);

			$rsp = file_get_contents($url);
			$rsp_obj = unserialize($rsp);
			$pic = $rsp_obj['photo'];	
			
			switch($pic['license']){
				case 1: $license = "images/cc-by-nc-sa.png"; break;
				case 2: $license = "images/cc-by-nc.png"; break;
				case 3: $license = "images/cc-by-nc-nd.png"; break;
				case 4: $license = "images/cc-by.png"; break;
				case 5: $license = "images/cc-by-sa.png"; break;
				case 6: $license = "images/cc-by-nd.png"; break;
				case 7: $license = "PUBLIC DOMAIN"; break;
				default: echo "No CC license found"; die(); break;
			}	
			
			$title = $pic['title']['_content'];
			isset($pic['owner']['realname']) ? $author = $pic['owner']['realname'] : $author = $pic['owner']['username'];
			$filepath = "http://farm" . $pic['farm'] . ".static.flickr.com/" . $pic['server'] . "/" . $pic['id'] . "_" . $pic['secret'] . "_b.jpg";
			$mime = "jpeg";
			
			$details = getimagesize($filepath);
			$original = imagecreatefromjpeg($filepath);
			$image_height = $details[1] + 5;
			
			$im = imagecreatetruecolor($details[0],$details[1]+40);
			imagecopyresampled($im, $original, 0,0, 0, 0, $details[0], $details[1] , $details[0], $details[1]);
			
			if($license!="PUBLIC DOMAIN"){
				$original = imagecreatefrompng(dirname(__FILE__) . "/" . $license);
				$license_details = getimagesize(dirname(__FILE__) . "/" . $license);
				imagecopyresampled($im, $original, 0, $image_height, 0, 0, $license_details[0], $license_details[1], $license_details[0], $license_details[1]);
		  	}

			imagettftext($im, 10.0, 0, $license_details[0] + 10, $image_height + 10, imagecolorallocate($im,255,255,255), dirname(__FILE__) . "/arial.ttf" , str_replace(".png", "", str_replace("images/", "", $license)) . " " . $picture_url );
			imagettftext($im, 10.0, 0, $license_details[0] + 10, $image_height + 30, imagecolorallocate($im,255,255,255), dirname(__FILE__) . "/arial.ttf" , $title . " " . $author );	
					
			ob_start();
			imagejpeg($im, NULL, 100);
			$rawImageBytes = ob_get_clean();
			echo "<img src='data:image/jpeg;base64," . base64_encode( $rawImageBytes ) . "' />";

		}
		
	}
	
  }

  