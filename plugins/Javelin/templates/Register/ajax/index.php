<?= $this->element('People/search_results', [
	'extra_url' => [
		$this->Html->iconImg('/javelin/img/javelin.png', ['alt' => __('Register'), 'title' => __('Register')]) => [
			'plugin' => 'Javelin', 'controller' => 'Register', 'action' => 'index', '?' => ['return' => false],
			'_link_opts' => ['escape' => false, 'class' => 'icon', 'confirm' => __('Are you sure you want to register this person as the primary contact for {0}?', 'Javelin')]
		],
	]
])
?>
