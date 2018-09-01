<fieldset>
	<legend><?= __('Chase Paymentech Options') ?></legend>
<?php
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_live_store',
	'options' => [
		'label' => __('Live payment page ID'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_live_password',
	'options' => [
		'label' => __('Live transaction key'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_live_response',
	'options' => [
		'label' => __('Live response key'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_test_store',
	'options' => [
		'label' => __('Test payment page ID'),
		'help' => __('These test settings are only required if you are doing test payments through {0}', $this->Html->link('rpm.demo.e-xact.com', 'https://rpm.demo.e-xact.com/'))
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_test_password',
	'options' => [
		'label' => __('Test transaction key'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'chase_test_response',
	'options' => [
		'label' => __('Test response key'),
	],
]);
?>
</fieldset>
