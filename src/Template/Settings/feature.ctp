<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Settings'));
$this->Html->addCrumb(__('Feature'));
?>

<div class="settings form">
<?php
echo $this->Form->create(false, ['align' => 'horizontal']);

echo $this->element('Settings/banner');
?>
	<fieldset>
		<legend><?= __('Primary Options') ?></legend>
<?php
if (!$affiliate) {
	echo $this->element('Settings/input', [
		'category' => 'site',
		'name' => 'name',
		'options' => [
			'label' => __('Site name'),
			'help' => __('The name this application will be known as to your users.'),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'feature',
		'name' => 'affiliates',
		'options' => [
			'label' => __('Enable affiliates'),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'help' => __('Allow configuration of multiple affiliated organizations.'),
		],
	]);
}

echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'items_per_page',
	'options' => [
		'label' => __('Items per page'),
		'help' => __('The number of items that will be shown per page on search results and long reports.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'public',
	'options' => [
		'label' => __('Public site'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('If this is enabled, some information normally reserved for people who are logged on (statistics, team rosters, etc.) will be made available to anyone.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'registration',
	'options' => [
		'label' => __('Handle registration'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Enable or disable processing of registrations.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'spirit',
	'options' => [
		'label' => __('Handle Spirit of the Game'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Enable or disable Spirit of the Game options. If enabled here, Spirit can still be disabled on a per-league basis.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'allow_past_games',
	'options' => [
		'label' => __('Allow past games'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Enable or disable the option to schedule games in the past.'),
	],
]);
if (!$affiliate) {
	echo $this->element('Settings/input', [
		'category' => 'feature',
		'name' => 'ckeditor',
		'options' => [
			'label' => __('Use CKEditor WYSIWYG editor'),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
		],
	]);
}
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'badges',
	'options' => [
		'label' => __('Enable badges'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Enable or disable the awarding and display of badges.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'contacts',
	'options' => [
		'label' => __('Handle contacts'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('Enable or disable management of contacts for users to send messages without exposing email addresses.'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'feature',
	'name' => 'units',
	'options' => [
		'label' => __('Units'),
		'type' => 'radio',
		'options' => Configure::read('options.units'),
	],
]);
?>
	</fieldset>

<?php
$languages = Configure::read('available_translations');
if (!$affiliate && count($languages) > 1):
?>
	<fieldset>
		<legend><?= __('Language Features') ?></legend>
<?php
	echo $this->element('Settings/input', [
		'category' => 'feature',
		'name' => 'language',
		'options' => [
			'label' => __('Allow registered users to select their preferred language'),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'feature',
		'name' => 'uls',
		'options' => [
			'label' => __('Use ULS to allow language selection for anonymous users and those who haven\'t selected a preferred language'),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'help' => __('To use this, you need to separately install the ULS plugin.'),
		],
	]);
	echo $this->element('Settings/input', [
		'category' => 'site',
		'name' => 'default_language',
		'options' => [
			'label' => __('Default site language'),
			'type' => 'select',
			'options' => $languages,
			'empty' => false,
		],
	]);
?>
	</fieldset>
<?php
endif;
?>

	<fieldset>
		<legend><?= __('Twitter Features') ?></legend>
<?php
if (function_exists('curl_init')) {
	echo $this->element('Settings/input', [
		'category' => 'feature',
		'name' => 'twitter',
		'options' => [
			'label' => __('Twitter'),
			'type' => 'radio',
			'options' => Configure::read('options.enable'),
			'help' => __('Enable or disable Twitter integration.'),
		],
	]);

	echo $this->element('Settings/input', [
		'category' => 'twitter',
		'name' => 'consumer_key',
		'options' => [
			'label' => __('Consumer key'),
			'help' => __('This application\'s Twitter consumer key.'),
		],
	]);

	echo $this->element('Settings/input', [
		'category' => 'twitter',
		'name' => 'consumer_secret',
		'options' => [
			'label' => __('Consumer secret'),
			'help' => __('This application\'s Twitter consumer secret.'),
		],
	]);
} else {
	echo $this->Html->para('warning-message', __('Twitter integration requires the cUrl library, which your installation of PHP does not support. Talk to your system administrator or hosting company about enabling cUrl.'));
}
?>
	</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
