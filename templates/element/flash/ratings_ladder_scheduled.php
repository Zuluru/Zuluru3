<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 */

?>
<?= $this->element('BootstrapUI.Flash/default', [
	'message' => $this->element('Leagues/schedule/ratings_ladder_scheduled', $params),
	'params' => [
		'class' => ['alert-success', 'alert', 'alert-dismissible', 'fade', 'show', 'd-flex', 'align-items-center'],
		'escape' => false,
		'attributes' => ['role' => 'alert'],
	],
]);
