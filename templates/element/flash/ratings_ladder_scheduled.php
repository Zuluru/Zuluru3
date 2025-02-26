<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 */

?>
<?= $this->element('BootstrapUI.flash/default', [
	'message' => $this->element('Leagues/schedule/ratings_ladder_scheduled', $params),
	'params' => [
		'class' => ['alert-success', 'alert', 'alert-dismissible', 'show', 'd-flex', 'align-items-center'],
		'icon' => false,
		'escape' => false,
		'attributes' => ['role' => 'alert'],
	],
]);
