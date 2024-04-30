<?php
/**
 * @var mixed[] $params
 */
?>
<?= $this->element('BootstrapUI.Flash/default', [
	'message' => $this->element('Events/refunds_failed', $params),
	'params' => [
		'class' => ['alert-warning', 'alert', 'alert-dismissible', 'fade', 'show', 'd-flex', 'align-items-center'],
		'escape' => false,
		'attributes' => ['role' => 'alert'],
	],
]);
