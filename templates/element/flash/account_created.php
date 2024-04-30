<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;

if (Configure::read('feature.auto_approve')) {
	$message = $this->Html->tag('h2', __('THANK YOU')) .
		$this->Html->para(null, __('for creating an account with {0}.', Configure::read('organization.name')));
} else {
	$message = $this->Html->para(null,
		__('Your account has been created.') . ' ' .
		__('It must be approved by an administrator before you will have full access to the site.') . ' ' .
		__('However, you can log in and start exploring right away.'));
}

if ($params['continue']) {
	$message .= $this->Html->para(null, __('Please proceed with entering your next child\'s details below.'));
}

echo $this->element('BootstrapUI.Flash/default', [
	'message' => $message,
	'params' => [
		'class' => ['alert-success', 'alert', 'alert-dismissible', 'fade', 'show', 'd-flex', 'align-items-center'],
		'escape' => false,
		'attributes' => ['role' => 'alert'],
	],
]);
