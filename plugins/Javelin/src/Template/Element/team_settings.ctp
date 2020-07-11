<?php
use Cake\Core\Configure;
?>
<fieldset>
	<legend><?= __('{0} Options', 'Javelin') ?></legend>
<?php
echo $this->element('Settings/input', [
	'category' => 'javelin',
	'name' => 'default_opt_in',
	'options' => [
		'label' => __('Default Opt-in'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'confirm' => __('By enabling this, you are making {0} an "opt out" service, where players must explicitly opt out if they do not want to participate.\n\nIf this is disabled, then players must explicitly opt in.\n\nBefore enabling, be sure that your local privacy laws allow it.', 'Javelin'),
	],
]);
?>
</fieldset>
