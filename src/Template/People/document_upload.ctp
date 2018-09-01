<?php
/**
 * @type \App\Model\Entity\Person $person
 * @type \App\Model\Entity\Upload $upload
 */

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb($person->full_name);
$this->Html->addCrumb(__('Upload Document'));
?>

<div class="people view">
<h2><?= __('Upload Document') . ': ' . $person->full_name ?></h2>

<p><?= __('Some site functionality may require that you upload a document to prove a claim. For example, junior players might require a waiver signed by a parent or guardian, or students might need to submit proof of enrolment to qualify for a discount.') ?></p>
<p><strong><?= __('Documents must be approved by an administrator before the related function will be allowed. This may take up to two business days to process.') ?></strong></p>

<?php
echo $this->Form->create($upload, ['align' => 'horizontal', 'type' => 'file']);
echo $this->Form->hidden('person_id', ['value' => $person->id]);
echo $this->Form->input('type_id', [
	'empty' => __('Select one:'),
]);
echo $this->Form->input('filename', ['type' => 'file']);
echo $this->Form->button(__('Upload'), ['class' => 'btn-success']);
echo $this->Form->end();
?>

</div>
