<?php
/**
 * @type \App\View\AppView $this
 */

use Cake\Routing\Router;

$this->Html->addCrumb(__('Settings'));
$this->Html->addCrumb('Stripe');
?>

<div class="settings form">
<?php
echo $this->Form->create(false, ['align' => 'horizontal']);
?>
<fieldset>
	<legend><?= __('{0} Settings', 'Stripe') ?></legend>
	<p><?= __('To find this information, log in to the {0} dashboard, then click "Get your live API keys".',
		$this->Html->link('Stripe', 'https://dashboard.stripe.com/test/dashboard', ['target' => '_new'])
	) ?></p>
<?php
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'stripe_live_publishable_key',
	'options' => [
		'label' => __('Live publishable key'),
		'help' => __('Should start with "{0}".', 'pk_live_'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'stripe_live_secret_key',
	'options' => [
		'label' => __('Live secret key'),
		'help' => __('Should start with "{0}".', 'sk_live_'),
	],
]);
?>
	<p><?= __('To do any testing of your registration system, log in to the {0} dashboard, then click "Get your test API keys".',
		$this->Html->link('Stripe', 'https://dashboard.stripe.com/test/dashboard')
	) ?></p>
<?php
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'stripe_test_publishable_key',
	'options' => [
		'label' => __('Test publishable key'),
		'help' => __('Should start with "{0}".', 'pk_test_'),
	],
]);
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'stripe_test_secret_key',
	'options' => [
		'label' => __('Test secret key'),
		'help' => __('Should start with "{0}".', 'sk_test_'),
	],
]);
?>
</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
