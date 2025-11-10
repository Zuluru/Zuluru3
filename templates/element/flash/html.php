<?php
/**
 * @var \App\View\AppView $this
 * @var array $message
 */

use Cake\Core\Configure;

$message = (array)$message;

foreach ($message as $key => $part) {
	if (!empty($params['replacements'])) {
		$message[$key] = $this->Html->formatMessage([
			'format' => $part,
			'replacements' => $params['replacements'],
		]);
	}
}
$message = $this->Html->para(null, implode(' ', $message));

if (isset($params['class']) && is_string($params['class'])) {
	$class = "alert-{$params['class']}";
} else {
	$class = 'alert-success';
}

echo $this->element('BootstrapUI.flash/default', [
	'message' => $message,
	'params' => [
		'class' => [$class, 'alert', 'alert-dismissible', 'show', 'd-flex', 'align-items-center'],
		'icon' => false,
		'escape' => false,
		'attributes' => ['role' => 'alert'],
	],
]);
