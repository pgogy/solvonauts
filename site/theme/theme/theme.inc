<?PHP

class theme{

	var $site;

	public function theme($site){
	
		$this->site = $site;
	
	}
	
	private function display_header($title){
	
	}
	
	private function display_footer(){
	
	}
	
	public function display_all_metadata_results($data){
	
		include __DIR__ . "/all_metadata_results.inc";
	
	}
	
	public function display_opml_list_results($data){
	
		include __DIR__ . "/opml_list_results.inc";
	
	}
	
	public function display_api_search_results($data){
	
		include __DIR__ . "/api_list_results.inc";
	
	}
	
	public function display_visit_activity_display_results($data){
	
		include __DIR__ . "/visit_activity_display_results.inc";
	
	}
	
	public function display_search_activity_display_results($data){
	
		include __DIR__ . "/search_activity_display_results.inc";
	
	}
	
	public function display_search_frequency_display_results($data){
	
		include __DIR__ . "/search_frequency_display_results.inc";
	
	}
	
	public function display_total_resources(){
	
		$statement_resources = $this->site->db_layer->select_query("SELECT count(link_id) as total_links from link_index", 
														 array(),
														 $this->site->database_link);
		$data =  $this->site->db_layer->get_row($statement_resources);			
											 
		return $data[0];
		
	}
	
	public function display_total_sites(){
	
		$statement_sites = $this->site->db_layer->select_query("SELECT count(distinct site_address) as total_sites from oer_site_list", 
														 array(),
														 $this->site->database_link);
		$data = $this->site->db_layer->get_row($statement_sites);		
												 
		return $data[0];
		
	}

	public function display_pagination($data){
	
		$pagination = "";
	
		for($x=0;$x<round($data['result_count'][0]/PAGINATION);$x++){
			
			if(isset($_GET['offset'])){
		
				if($_GET['offset']!=$x){

					$pagination .= "<a href='?action=search&term=" . $data['term'] . "&offset=" . $x . "'>" . ($x+1) . "</a> ";
				
				}else{
				
					$pagination .= $x+1 . " ";
				
				}
				
			}else{
			
				$pagination .= "<a href='?action=search&term=" . $data['term'] . "&offset=" . $x . "'>" . ($x+1) . "</a> ";
			
			}

		}
		
		return $pagination;
	
	}

	public function display_results_count($data){

		return $this->site->get_text_replace("Searching for %d", array("%d" => utf8_decode($data['term']))) 
				.  " : " . $this->site->get_text_replace("%d results found", array("%d" => $data['result_count'][0]))
				. " | <a href='?term=" . $data['term'] . "&action=rss_search'>" . $this->site->get_text("RSS Feed for this search") . "</a>";
	
	}
	
	public function display_resources($data){
	
		foreach($data['results'] as $resource){
		
			$this->display_resource($resource);
		
		}
	
	}
	
	public function display_resource($resource){
		
		$resource_data = "";
		
		?><p>
			<a class="oerlink" target="_blank" href="<?PHP echo $resource['link']; ?>"><?PHP echo utf8_decode(stripslashes($resource['title'])); ?></a>
		 </p>
		 <div class="item"><div><p><?PHP echo $this->site->get_text("Description"); ?></p><?PHP
	
			echo utf8_decode(stripslashes($resource['description']));
			
		?></div><div><p><?PHP echo $this->site->get_text("Subjects"); ?></p><?PHP
		
			$subjects = explode(",", $resource['subject']);
			
			foreach($subjects as $subject){
			
				$resource_data .= "<a href='?action=subject&subject=" . $subject . "'>" . utf8_decode(stripslashes($subject)) . "</a> | ";
			
			}
			
			echo substr($resource_data,0,strlen($resource_data)-3);
		
		?></div><div><p><?PHP echo $this->site->get_text("license"); ?></p><?PHP
		
			$resource_data .= utf8_decode(stripslashes($resource['license']));
		
		?></div><div><p><?PHP echo $this->site->get_text("Site sourced from"); ?></p><?PHP
			
			echo "<a href='" . $resource['site_address'] . "'>" . stripslashes($resource['site_address']) . "</a>";
	
		?><p><?PHP echo $this->site->get_text("All metadata"); ?></p><?PHP
		
			echo "<a href='?action=all_metadata&id=" . $resource['link_id'] . "'>" . $this->site->get_text("See all metadata") . "</a>";
		
		?></p></div></div><?PHP

	}
	
}