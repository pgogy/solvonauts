<?PHP

	echo self::store_search($data);

	echo self::display_header($this->site->get_text("Search results"));

?>	<p><?PHP echo $this->form_display($data); ?></p>
	<p><?PHP echo $this->display_results_count($data); ?></p>
	<p><?PHP echo $this->display_pagination($data);	?></p>
	<?PHP $this->display_audio_resources($data); ?>
	<p><?PHP echo $this->form_display($data); ?></p>
<?PHP

	echo self::display_footer();

?>
