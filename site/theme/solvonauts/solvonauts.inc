<?PHP

class solvonauts extends theme{

	var $site;

	public function solvonauts($site){
	
		$this->site = $site;
	
	}
	
	public function display_index(){
	
		include __DIR__ . "/index.inc";
	
	}
	
	public function store_search($data){
		
		$valid_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);  
		
		$statement = $this->site->db_layer->insert_query("insert into activity_search(term,results,ip,time_searched)values(:term,:results,:ip,:time)", 
											 array(':term' => $data['term'], ':results' => $data['result_count'][0], ':ip' => $valid_ip, ':time' => time()), 
											 $this->site->database_link);
	
	}
	
	public function display_header($title){
		
		?><html>
			<head>
				<title><?PHP echo $title; ?></title>
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
				<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
				<script type="text/javascript" src="site/theme/solvonauts/js/activity.js"></script>
				<link rel="stylesheet" type="text/css" href="site/theme/solvonauts/css/style.css">	
			</head>
			<body>
			<div class="header">	
				<ul>
					<li><a href="?action=index"><?PHP echo $this->site->get_text("Home"); ?></a></li>
					<li><a href="?action=visit_activity_display"><?PHP echo $this->site->get_text("Frequently visited resources"); ?></a></li>
					<li><a href="?action=search_activity_display"><?PHP echo $this->site->get_text("Recent searches"); ?></a></li>
					<li><a href="?action=search_frequency_display"><?PHP echo $this->site->get_text("Popular searches"); ?></a></li>
				</ul>
			</div><?PHP
	
	}
	
	private function display_footer(){
	
		?></body>
		</html><?PHP
	
	}
	
	public function display_all_metadata_results($data){
	
		include __DIR__ . "/../theme/all_metadata_results.inc";
	
	}
	
	public function display_visit_activity_display_results($data){
	
		include __DIR__ . "/../theme/visit_activity_display_results.inc";
	
	}
	
	public function display_search_activity_display_results($data){
	
		include __DIR__ . "/../theme/search_activity_display_results.inc";
	
	}
	
	public function display_search_frequency_display_results($data){
	
		include __DIR__ . "/../theme/search_frequency_display_results.inc";
	
	}
	
	public function display_search_results($data){
	
		include __DIR__ . "/search_results.inc";
	
	}
	
	public function display_subject_results($data){
	
		include __DIR__ . "/search_results.inc";
	
	}
	
	public function form_display($data){
	
		return '<form action="" method="GET">
			<input type="text" name="term" value="' . utf8_decode($data['term']) . '"/>
			<input type="hidden" name="action" value="search"/>
			<button>
				' . $this->site->get_text("Search again") . '
			</button>
		</form>';
	
	}
	
	public function display_resource($resource){
		
		$resource_data = "";
		
		?><p>
			<a class="oerlink" link_id="<?PHP echo $resource['link_id']; ?>" target="_blank" href="<?PHP echo $resource['link']; ?>"><?PHP echo utf8_decode(stripslashes($resource['title'])); ?></a>
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
		
			echo utf8_decode(stripslashes($resource['license']));
		
		?></div><div><p><?PHP echo $this->site->get_text("Site sourced from"); ?></p><?PHP
			
			echo "<a href='" . $resource['site_address'] . "'>" . stripslashes($resource['site_address']) . "</a>";
	
		?><p><?PHP echo $this->site->get_text("All metadata"); ?></p><?PHP
		
			echo "<a href='?action=all_metadata&id=" . $resource['link_id'] . "'>" . $this->site->get_text("See all metadata") . "</a>";
		
		?></p></div></div><?PHP

	}
	
}