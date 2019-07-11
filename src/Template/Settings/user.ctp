<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Settings'));
$this->Html->addCrumb(__('User'));
?>

<div class="settings form">
<?php
echo $this->Form->create(false, ['align' => 'horizontal']);

echo $this->element('Settings/banner');
?>
	<fieldset>
		<legend><?= __('User Features') ?></legend>
<?php
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'auto_approve',
	'options' => [
		'label' => __('Automatically Approve New User Accounts'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('By enabling this, you reduce administrative work and minimize delays for users. However, you also lose the ability to detect and eliminate duplicate accounts.') . ' ' .
				$this->Html->tag('span', __('Use of this feature is recommended only for brand new sites wanting to ease the transition for their members.'), ['class' => 'warning-message']),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'antispam',
	'options' => [
		'label' => __('Anti-spam Measures'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Enable this to add honeypot-style anti-spam measures to the "create account" page. These measures are generally invisible to users.'),
	],
]);
if (!$affiliate) {
	echo $this->element('Settings/input', [
		'category' => 'feature',
		'name' => 'multiple_affiliates',
		'options' => [
			'label' => __('Enable Joining Multiple Affiliates'),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'help' => __('Allow users to join multiple affiliates (only applicable if affiliates are enabled above).'),
		],
	]);
}
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'photos',
	'options' => [
		'label' => __('Photos'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Enable or disable the option for people to upload profile photos.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'approve_photos',
	'options' => [
		'label' => __('Approve Photos'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('If enabled, profile photos must be approved by an administrator before they will be visible.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'gravatar',
	'options' => [
		'label' => 'Gravatar',
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Enable or disable the option for people to use Gravatar for their photo.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'documents',
	'options' => [
		'label' => __('Handle Document Uploads'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Enable or disable uploading of documents by people (e.g. as an alternative to faxing or emailing).'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'annotations',
	'options' => [
		'label' => __('Enable Annotations'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Allow people to attach notes to other people, teams, games and {0}.', __(Configure::read('UI.fields'))),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'tasks',
	'options' => [
		'label' => __('Enable Tasks'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Enable or disable the management and assignment of tasks.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'dog_questions',
	'options' => [
		'label' => __('Dog Questions'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Enable or disable questions and options about dogs.'),
	],
]);
?>
	</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
