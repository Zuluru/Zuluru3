<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
use Cake\Routing\Router;

$this->Breadcrumbs->add(__('Settings'));
$this->Breadcrumbs->add('Stripe');
?>

<div class="settings form">
<?php
echo $this->Form->create(null, ['align' => 'horizontal']);
?>
<fieldset>
	<legend class="border-bottom"><?= __('{0} Settings', 'Stripe') ?></legend>
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
echo $this->element('Settings/input', [
	'category' => 'payment',
	'name' => 'stripe_refunds',
	'options' => [
		'label' => __('Issue refunds online'),
		'type' => 'radio',
		'options' => Configure::read('options.enable'),
		'help' => __('If enabled, refunds for payments received through {0} can be issued though {0}.', 'Stripe'),
	],
]);
?>
</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
