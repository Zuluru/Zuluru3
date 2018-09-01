<fieldset>
	<legend><?= __('Moneris Options') ?></legend>
<?php
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'moneris_live_store',
	'options' => [
		'label' => __('Live store ID'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'moneris_live_password',
	'options' => [
		'label' => __('Live store password'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'moneris_test_store',
	'options' => [
		'label' => __('Test store ID'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'moneris_test_password',
	'options' => [
		'label' => __('Test store password'),
	],
]);
?>
</fieldset>
