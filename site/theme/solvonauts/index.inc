<html>
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="site/theme/solvonauts/css/style.css">
		<title>Solvonauts : The Open Search Engine</title>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-45552163-1', 'solvonauts.org');
  ga('send', 'pageview');

</script>
	</head>
	<body>
	<div class="searchholder">
		<form action="" method="GET">
			<input type="text" name="term" value="<?PHP echo $this->site->get_text("Enter search term here"); ?>"/>
			<?PHP
			
				if(isset($_GET['search'])){
					
					switch($_GET['search']){
						case "picture_search" : $search = "picture_search"; break;
						case "audio_search" : $search = "audio_search"; break;
						case "video_search" : $search = "video_search"; break;
					}
					
				}else{
				
					$search = "search";
				
				}
				
			?>
			<input class="searchbox" type="hidden" name="action" value="<?PHP echo $search; ?>"/>
			<button>
				<?PHP 
				
					switch($search){
						case "search" :	echo $this->site->get_text("Search"); break;
						case "picture_search" :	echo $this->site->get_text("Search for pictures"); break;
						case "audio_search" :	echo $this->site->get_text("Search for audio"); break;
						case "video_search" :	echo $this->site->get_text("Search for video"); break;
					}
					
				?>
			</button>
		</form>
		<p>
			<?PHP echo $this->site->get_text_replace("Search ?total Resources from ?sites sites", array("?total" => $this->display_total_resources(), "?sites" => $this->display_total_sites())); ?>
		</p>
		<p>
			<a href="?search=picture_search"><?PHP echo $this->site->get_text("Picture search"); ?></a> |
			<a href="?search=video_search"><?PHP echo $this->site->get_text("Video search"); ?></a> |
			<a href="?search=audio_search"><?PHP echo $this->site->get_text("Audio search"); ?></a> |
			<a href="?action=flickr_attribute"><?PHP echo $this->site->get_text("Flickr Attribute"); ?></a> |
			<a href="?action=visit_activity_display"><?PHP echo $this->site->get_text("Frequently visited resources"); ?></a> |
			<a href="?action=search_activity_display"><?PHP echo $this->site->get_text("Recent searches"); ?></a> | 
			<a href="?action=search_frequency_display"><?PHP echo $this->site->get_text("Popular searches"); ?></a> |
			<a href="http://solvonauts.org/community/">Community and about us </a>
		</p>
	</div>
</body>
</html>