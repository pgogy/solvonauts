<html>
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" type="text/css" href="site/theme/solvonauts/css/style.css">	
	</head>
	<body>
	<div class="header">	
	</div>
	<p><?PHP echo $this->display_results_count($data); ?></p>
	<?PHP $this->display_resources($data); ?>
	</body>
</html>