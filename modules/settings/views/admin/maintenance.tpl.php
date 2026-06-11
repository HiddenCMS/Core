<div class="ui grid">
	<div class="sixteen wide center aligned column">
		<div class="btn-group switch">
			<a href="#" class="btn <?php echo ($opened = !$this->config->maintenance) ? 'btn-success' : 'btn-light' ?>"><?php echo icon($opened ? 'fas fa-toggle-on' : 'fas fa-toggle-off').' '.$this->lang('Ouvert') ?></a>
			<a href="#" class="btn <?php echo !$opened ? 'btn-danger' : 'btn-light' ?>"><?php echo icon(!$opened ? 'fas fa-toggle-on' : 'fas fa-toggle-off').' '.$this->lang('Ferm?') ?></a>
		</div>
	</div>
</div>
