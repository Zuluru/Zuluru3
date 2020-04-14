<?php
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb($document->person->full_name);
if ($this->getRequest()->action == 'edit_document') {
	$this->Html->addCrumb(__('Edit Document'));
} else {
	$this->Html->addCrumb(__('Approve Document'));
}
$this->Html->addCrumb($document->upload_type->name);
?>

<div class="people form">
<?= $this->Form->create($document, ['align' => 'horizontal']) ?>
	<fieldset>
<?php
		echo $this->Form->hidden('approved', ['value' => true]);

		echo $this->Form->input('valid_from', [
			'minYear' => Configure::read('options.year.event.min'),
			'maxYear' => Configure::read('options.year.event.max'),
			'default' => FrozenDate::now()->startOfYear(),
		]);
		echo $this->Form->input('valid_until', [
			'minYear' => Configure::read('options.year.event.min'),
			'maxYear' => Configure::read('options.year.event.max'),
			'default' => FrozenDate::now()->endOfYear(),
		]);
?>
	</fieldset>
<?php
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
