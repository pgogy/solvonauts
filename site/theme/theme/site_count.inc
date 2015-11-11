<html>
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="site/theme/solvonauts/css/style.css">	
	</head>
	<body>
	<div class="header">	
	</div>
		<?PHP
			echo $data['results'] . " " . $this->site->get_text("harvested from that domain");
		?>	
	</body>
</html>