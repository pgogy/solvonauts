<?PHP

	echo self::display_header($this->site->get_text("Search Frequency"));

?>
	<h2><?PHP
	
		echo $this->site->get_text("Popular searches"); 
		
	?></h2>
	<?PHP
	
		foreach($data['results'] as $resource){
	
			?><p><a href="?action=search&term=<?PHP echo trim($resource['term']); ?>"><?PHP
			
				echo $this->site->get_text_replace("?term has been searched for ?total times", array("?term" => $resource['term'], "?total" => $resource['total']));
		
			?></a></p><?PHP
		
		}
	
	?>
<?PHP

	echo self::display_footer();

?>
