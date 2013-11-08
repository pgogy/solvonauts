<html>
	<head>
		<meta charset="UTF-8">
		<title>Solvonauts : The Open Search Engine</title>
	</head>
	<body>
	<div class="searchholder">
		<form action="" method="GET">
			<input type="text" name="term" value="<?PHP echo $this->site->get_text("Enter search term here"); ?>"/>
			<input class="searchbox" type="hidden" name="action" value="search"/>
			<button>
				<?PHP echo $this->site->get_text("Search"); ?>
			</button>
		</form>
		<p>
			<?PHP echo $this->site->get_text_replace("Search ?total Resources from ?sites sites", array("?total" => $this->display_total_resources(), "?sites" => $this->display_total_sites())); ?>
		</p>
	</div>
</body>
</html>
