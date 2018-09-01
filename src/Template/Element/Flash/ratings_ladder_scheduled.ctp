<?= $this->element('BootstrapUI.Flash/default', [
	'message' => $this->element('Leagues/schedule/ratings_ladder_scheduled', $params),
	'params' => [
		'class' => ['alert-success', 'alert', 'alert-dismissible', 'fade', 'in'],
		'escape' => false,
		'attributes' => ['role' => 'alert'],
	],
]);
