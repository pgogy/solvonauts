<?PHP

	echo self::display_header($this->site->get_text("View Activity"));

?>
	<h2><?PHP
	
		echo $this->site->get_text("Recent visits"); 
		
	?></h2>
	<?PHP
		
		foreach($data['results'] as $resource){
	
		?><p>
			<a class="oerlink" target="_blank" href="<?PHP echo $resource['link']; ?>"><?PHP echo utf8_decode(stripslashes($resource['title'])); ?></a>
			<?PHP 
			
				echo $this->site->get_text_replace("Visited ?count time(s)", array("?count" => $resource['total']));
			
			?></p><?PHP
		
		}
	
	?>
<?PHP

	echo self::display_footer();

?>
