<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Upload $document
 */

use Cake\Core\Configure;
use Cake\I18n\FrozenDate;

$this->Breadcrumbs->add(__('People'));
$this->Breadcrumbs->add($document->person->full_name);
if ($this->getRequest()->getParam('action') == 'edit_document') {
	$this->Breadcrumbs->add(__('Edit Document'));
} else {
	$this->Breadcrumbs->add(__('Approve Document'));
}
$this->Breadcrumbs->add($document->upload_type->name);
?>

<div class="people form">
<?= $this->Form->create($document, ['align' => 'horizontal']) ?>
	<fieldset>
<?php
		echo $this->Form->hidden('approved', ['value' => true]);

		echo $this->Form->control('valid_from', [
			'minYear' => Configure::read('options.year.event.min'),
			'maxYear' => Configure::read('options.year.event.max'),
			'default' => FrozenDate::now()->startOfYear(),
		]);
		echo $this->Form->control('valid_until', [
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
