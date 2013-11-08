<?PHP

	echo self::display_header($this->site->get_text("Search Activity"));

?>
	<h2><?PHP
	
		echo $this->site->get_text("Recent searches"); 
		
	?></h2>
	<?PHP
		
		foreach($data['results'] as $resource){
	
			?><p><a href="?action=search&term=<?PHP echo trim($resource['term']); ?>"><?PHP
			
				echo $this->site->get_text_replace("A search for ?term, returned ?results", array("?term" => $resource['term'], "?results" => $resource['results']));
		
			?></a></p><?PHP
		
		}
	
	?>
<?PHP

	echo self::display_footer();

?>
