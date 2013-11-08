<?PHP

class rss_search{

	var $data;
	var $site;
	
	public function return_data(){
	
		return $this->data;
	
	}
	
	public function function_choice(){
	
		return "generate_rss";
	
	}
	
	public function generate_rss(){
	
		echo '<?xml version="1.0" encoding="ISO-8859-1"?>';

		?><rss version="2.0">
				<channel>
					<title><?PHP echo $this->site->get_text_replace("RSS Feed for ?term", array("?term" => $this->data['term'])); ?></title>
					<link><?PHP echo SITE . urlencode("?action=rss_search&term=" . $this->data['term']); ?></link>
					<description><?PHP echo $this->site->get_text_replace("RSS Feed for ?term", array("?term" => $this->data['term'])); ?></description><?PHP
					
					foreach($this->data['results'] as $resource){
					
						 ?><item>
								<title><?PHP echo $resource['title']; ?></title>
								<description><?PHP echo  trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($resource['description'])))))); ?></description>
								<link><?PHP echo $resource['link']; ?></link>
								<guid><?PHP echo $resource['link']; ?></guid>
						  </item><?PHP
					
					}
					
					?>
				</channel>
			</rss><?PHP
	
	}

	public function rss_search($site){

		$term = "";
	
		if(isset($_GET['term'])){
		
			$term = filter_var(urldecode($_GET['term']), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

		}
		
		$this->data = $this->rss_search_oer($site, $term);
		$this->site = $site;
	
	}
	
	public function rss_search_oer($site, $word){
		
		$statement = $site->db_layer->select_query_multiple("select title, link, description, subject, license, site_address, length(title) - length(replace(title,:title,'')) +
											 length(description) - length(replace(description,:title,'')) +
											 length(subject) - length(replace(subject,:title,'')) as changed
											 from link_index 
											 where title like :inner or subject like :inner or description like :inner
											 order by changed desc LIMIT 50", 
											 array(':inner' => "%" . $word . "%", ':title' => $word), 
											 $site->database_link);
											 
		return array("term" => $word, "results" => $site->db_layer->get_all_rows($statement, $site->database_link));
	
	}

}