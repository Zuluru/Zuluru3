<?php
/**
 * @var \App\Model\Entity\Credit $credit
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Credit'));
$this->Breadcrumbs->add(__('View'));
?>

<div class="credits view">
	<dl class="row">
		<dt class="col-sm-2 text-end"><?= __('Owner') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $this->element('People/block', ['person' => $credit->person]) ?></dd>
		<dt class="col-sm-2 text-end"><?= __('Date') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $this->Time->date($credit->created) ?></dd>
		<dt class="col-sm-2 text-end"><?= __('Initial Amount') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $this->Number->currency($credit->amount) ?></dd>
		<dt class="col-sm-2 text-end"><?= __('Amount Used') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $this->Number->currency($credit->amount_used) ?></dd>
<?php
if ($credit->payment_id):
?>
		<dt class="col-sm-2 text-end"><?= __('Registration') ?></dt>
		<dd class="col-sm-10 mb-0"><?php
			$invnum = sprintf(Configure::read('registration.order_id_format'), $credit->payment->registration_id);
			echo $this->Html->link($invnum, ['controller' => 'Registrations', 'action' => 'view', '?' => ['registration' => $credit->payment->registration_id]]);
		?></dd>
<?php
endif;
?>
		<dt class="col-sm-2 text-end"><?= __('Notes') ?></dt>
		<dd class="col-sm-10 mb-0"><?= $this->Text->autoParagraph(h($credit->notes)) ?></dd>
	</dl>
</div>

<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Html->iconLink('view_32.png',
	['action' => 'index'],
	['alt' => __('List'), 'title' => __('List Credits')]));
if ($this->Authorize->can('edit', $credit)) {
	echo $this->Html->tag('li', $this->Html->iconLink('edit_32.png',
		['action' => 'edit', '?' => ['credit' => $credit->id]],
		['alt' => __('Edit'), 'title' => __('Edit Credit')]));
}
if ($this->Authorize->can('delete', $credit)) {
	$confirm = __('Are you sure you want to delete this credit?');
	if ($credit->payment_id) {
		$confirm .= "\n\n" . __('Doing so will also delete the related refund, but will NOT change the payment status of the registration.');
	}
	echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
		['action' => 'delete', '?' => ['credit' => $credit->id]],
		['alt' => __('Delete'), 'title' => __('Delete Credit')],
		['confirm' => $confirm]));
}
?>
	</ul>
</div>
