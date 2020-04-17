<?php
/**
 * @type \App\Model\Entity\Credit $credit
 */

$this->Html->addCrumb(__('Credit'));
$this->Html->addCrumb($credit->person->full_name);
$this->Html->addCrumb(__('Transfer'));
?>

<div class="credits form">
	<h2><?= __('Transfer Credit') ?></h2>
	<?= $this->Text->autoParagraph($credit->notes) ?>
	<p><?= __('This credit has {0} remaining to be spent.', $this->Number->currency($credit->balance)) ?></p>
	<p class="warning-message"><?= __('By transferring this credit, you are relinquishing any claim to it. It <strong>may</strong> be possible to reclaim it in the event of a mistaken transfer, but this is not guaranteed.') ?>
<?= $this->element('People/search_form', ['affiliates' => collection($this->UserCache->read('Affiliates'))->combine('id', function ($entity) { return $entity->translateField('name'); })->toArray()]) ?>

	<div id="SearchResults" class="zuluru_pagination">

<?= $this->element('People/search_results', [
	'extra_url' => [
		__('Transfer Credit') => ['controller' => 'Credits', 'action' => 'transfer', 'credit' => $credit->id, 'return' => false],
	]
])
?>

	</div>
</div>
