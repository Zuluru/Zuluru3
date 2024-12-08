<?php
/**
 * @var \App\View\AppView $this
 * @var array $params
 */

?>
<?= $this->element('BootstrapUI.flash/default', [
	'message' => $this->element('email/debug', $params),
	'params' => [
        'icon' => 'mailbox',
        'iconOptions' => [
            'size' => 'xl',
            'class' => 'me-2',
        ],
		'class' => ['alert-email', 'alert', 'alert-dismissible', 'show', 'd-flex', 'align-items-center'],
		'escape' => false,
		'attributes' => ['role' => 'alert'],
	],
]);
