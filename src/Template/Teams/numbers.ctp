<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 * @var \App\Model\Entity\Person $person
 */

$this->Breadcrumbs->add(__('Teams'));
$this->Breadcrumbs->add(h($team->name));
if (isset($person)) {
	$this->Breadcrumbs->add($person->full_name);
	$this->Breadcrumbs->add(__('Shirt Number'));
} else {
	$this->Breadcrumbs->add(__('Shirt Numbers'));
}
?>

<div class="numbers form">
	<?= $this->Form->create($team, ['align' => 'horizontal']) ?>
<?php
if (isset($person)):
	echo $this->Html->tag('h2', h($team->name) . ': ' . h($person->full_name) . ': ' . __('Shirt Number'));
	echo $this->Form->control('people.0._joinData.number', [
		'type' => 'number',
	]);
else:
?>
	<fieldset>
		<legend><?= h($team->name) . ': ' . __('Shirt Numbers') ?></legend>
<?php
	foreach ($team->people as $key => $person) {
		echo $this->Form->control("people.$key._joinData.number", [
			'label' => [
				'text' => $this->element('People/block', compact('person')),
				'escape' => false,
			],
			'type' => 'number',
		]);
		echo $this->Form->hidden("people.$key.id", ['value' => $person->id]);
	}
?>
	</fieldset>

<?php
endif;
?>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
