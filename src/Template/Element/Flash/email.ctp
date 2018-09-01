<?= $this->element('BootstrapUI.Flash/default', [
	'message' => $this->element('Email/debug', $params),
	'params' => [
		'class' => ['alert-email', 'alert', 'alert-dismissible', 'fade', 'in'],
		'escape' => false,
		'attributes' => ['role' => 'alert'],
	],
]);
