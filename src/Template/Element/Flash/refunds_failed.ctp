<?php
/**
 * @type $params mixed[]
 */
?>
<?= $this->element('BootstrapUI.Flash/default', [
	'message' => $this->element('Events/refunds_failed', $params),
	'params' => [
		'class' => ['alert-warning', 'alert', 'alert-dismissible', 'fade', 'in'],
		'escape' => false,
		'attributes' => ['role' => 'alert'],
	],
]);
