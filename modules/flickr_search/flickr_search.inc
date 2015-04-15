<?PHP



class flickr_search{



	public $site;



	public function flickr_search($site){

		$this->site = $site;

	}



	public function function_choice(){

	

		return "display_flickr_search";

	

	}

		

	public function display_flickr_search(){

	

		echo $this->site->theme->display_header($this->site->get_text("FlickR Search"));

		

		if(!isset($_POST['flickr_term'])){

			?><p>

				<?PHP echo $this->site->get_text("Flickr Search and Attribute."); ?>

			</p>

			<form enctype="multipart/form-data" action="" method="POST">

				<p><input type="text" value="<?PHP echo $this->site->get_text("Enter Search Term here"); ?>" size="85" name="flickr_term" /></p>
				<p>License
				<select name="license">
				<option value="1">Attribution-NonCommercial-ShareAlike License http://creativecommons.org/licenses/by-nc-sa/2.0/</option>
<option value="2">Attribution-NonCommercial License http://creativecommons.org/licenses/by-nc/2.0/</option>
<option value="3">Attribution-NonCommercial-NoDerivs License http://creativecommons.org/licenses/by-nc-nd/2.0/</option>
<option value="4">Attribution License" http://creativecommons.org/licenses/by/2.0/</option>
<option value="5">Attribution-ShareAlike License http://creativecommons.org/licenses/by-sa/2.0/</option>
<option value="6">Attribution-NoDerivs License http://creativecommons.org/licenses/by-nd/2.0/</option>
<option value="7">No known copyright restrictions http://flickr.com/commons/usage/</option>
				</select></p>
				<input type="submit" value="<?PHP echo $this->site->get_text("Search"); ?>" class="button" />

			</form>

			<?PHP

			

		}else{

			if(isset($_POST['page'])){
				$page = $_POST['page'];
			}else{
				$page = 1;
			}

			$params = array(

				'api_key'	=> 'API KEY HERE',

				'method'	=> 'flickr.photos.search',

				'text'	=> $_POST['flickr_term'],

				'license'	=> $_POST['license'],

				'page'	=> $page,

				'format' => 'php_serial'

			);



			$encoded_params = array();



			foreach ($params as $k => $v){

				$encoded_params[] = urlencode($k).'='.urlencode($v);

			}

			$url = "https://api.flickr.com/services/rest/?".implode('&', $encoded_params);

			$rsp = file_get_contents($url);

			$rsp_obj = unserialize($rsp);

			if(isset($rsp_obj['photos'])){

				$data = $rsp_obj['photos'];

				?><p><?echo $data['perpage'] * $data['pages'] . " " . $this->site->get_text("photos found"); ?></p><?PHP

				?><form action="" method="POST"><p><?PHP echo $this->site->get_text("Select a page"); ?> <select name="page"><?PHP

				for($x=1;$x!=$data['pages'];$x++){
					?><option <?PHP if($page == $x){ echo " selected "; } ?> value="<?PHP echo $x; ?>"><?PHP echo $this->site->get_text("Page") . " " . $x; ?></option><?PHP
				}

				?></select>
				<input name="flickr_term" type="hidden" value="<?PHP echo $_POST['flickr_term']; ?>" />
				<input name="license" type="hidden" value="<?PHP echo $_POST['license']; ?>" />
				<input type="submit" value="<?PHP echo $this->site->get_text("Change page"); ?>" />
				</p></form><?PHP

				foreach($data['photo'] as $photo){
					?><div style="display:inline-block; width:80px; text-align:center;" ><img src="https://farm<?PHP echo $photo['farm']; ?>.staticflickr.com/<?PHP echo $photo['server']; ?>/<?PHP echo $photo['id']; ?>_<?PHP echo $photo['secret']; ?>_s.jpg" /><form target="_blank" method="POST" action="?action=flickr_attribute"><input type="hidden" name="flickr_url" value="https://www.flickr.com/photos/<?PHP echo $photo['owner']; ?>/<?PHP echo $photo['id']; ?>/" /><input type="submit" value="Attribute" /></form></div><?PHP
				}

			}



		}

		

	}

	

  }



  