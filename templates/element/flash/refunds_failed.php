<?php
/**
 * @var \App\View\AppView $this
 * @var mixed[] $params
 */
?>
<?= $this->element('BootstrapUI.flash/default', [
	'message' => $this->element('Events/refunds_failed', $params),
	'params' => [
		'class' => ['alert-warning', 'alert', 'alert-dismissible', 'show', 'd-flex', 'align-items-center'],
		'icon' => false,
		'escape' => false,
		'attributes' => ['role' => 'alert'],
	],
]);
