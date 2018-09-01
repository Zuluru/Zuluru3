<?php

use Cake\Core\Configure;

$this->Html->addCrumb(__('Registration'));
$this->Html->addCrumb($event->name);
$this->Html->addCrumb(__('Preferences'));
?>

<div class="registrations form">
	<h2><?= __('Registration Preferences') . ': ' . $event->name ?></h2>

<?php
echo $this->element('Registrations/relative_notice');
echo $this->element('Registrations/notice');

if ($waiting) {
	echo $this->Html->para('warning-message', __('Note that you are only adding yourself to the waiting list for this event. You will be contacted if a space opens up at a later time.'));
}

// This form is intentionally not 'align' => 'horizontal'
echo $this->Form->create($registration);
echo $this->Form->hidden('event_id', ['value' => $event->id]);

echo $this->element('Questionnaires/input', ['questionnaire' => $event->questionnaire, 'responses' => $registration->responses]);

// If there is more than one price option, or if user-selected deposit amount are allowed,
// then we need to provide payment options.
if (count($event->prices) > 1 ||
	in_array($event->prices[0]->online_payment_option, [ONLINE_MINIMUM_DEPOSIT, ONLINE_SPECIFIC_DEPOSIT, ONLINE_NO_MINIMUM])
):
?>
	<fieldset>
		<legend><?= __('Payment') ?></legend>
<?php
	if (count($event->prices) > 1):
?>
		<p><?= __('This event has the following options. Please select your preference. Note that options may have limitations, which will be noted on selection.') ?></p>
<?php
		$options = [];
		foreach ($event->prices as $price_option) {
			$options[$price_option->id] = $price_option->name . ' (' . $this->Number->currency($price_option->total) . ')';
		}
		echo $this->Jquery->ajaxInput('price_id', [
			'selector' => '#PaymentDetails',
			'url' => ['action' => 'register_payment_fields'],
		], [
			'label' => __('Registration options'),
			'empty' => __('Select one:'),
			'options' => $options,
		]);
	else:
		// There is only one price option
		echo $this->Form->hidden('price_id', ['value' => $event->prices[0]->id]);
	endif;
?>
		<div id="PaymentDetails">
<?php
	echo $this->element('Registrations/register_payment_fields', ['price' => $registration->price]);
?>
		</div>
	</fieldset>
<?php
else:
	// There is only one price option
	echo $this->Form->hidden('price_id', ['value' => $event->prices[0]->id]);
endif;
?>

<?= $this->Form->button('Submit', ['class' => 'btn-success']) ?>
<?= $this->Form->end() ?>

</div>
<div class="actions columns">
	<?= $this->element('Events/actions', ['event' => $event, 'is_event_manager' => Configure::read('Perm.is_manager'), 'format' => 'list']) ?>
</div>
