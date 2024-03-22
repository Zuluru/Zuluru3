<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 */

?>
<?= $this->element('BootstrapUI.Flash/default', [
	'message' => $this->element('email/debug', $params),
	'params' => [
		'class' => ['alert-email', 'alert', 'alert-dismissible', 'fade', 'in'],
		'escape' => false,
		'attributes' => ['role' => 'alert'],
	],
]);
