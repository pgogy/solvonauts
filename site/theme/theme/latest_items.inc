<?PHP
	$this->site->theme->display_header($this->site->get_text("Latest content from sites"));
?>
	<h1><?PHP echo $this->site->get_text("Latest content from sites"); ?></h1>
	<?PHP 

		foreach($sites as $site => $harvested){

			?><p><a target="_blank" href="?action=last_harvest_site&site=<?PHP echo $site; ?>"><?PHP echo $this->site->get_text("see"); ?> <?PHP echo $site; ?></a> | <?PHP echo date("l jS, F Y", $harvested); ?></P><?PHP

		}

	?>
	</body>
</html>