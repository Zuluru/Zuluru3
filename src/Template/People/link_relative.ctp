<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Person $person
 */

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb($person->full_name);
$this->Html->addCrumb(__('Link Relative'));
?>

<div class="people link_relative">
<h2><?= __('Link Relative') . ': ' . $person->full_name ?></h2>

<p><?= __('By linking someone as a relative, you will be able to see their schedule and perform certain actions in the system on their behalf.') ?>
<?= __('Linking someone as a relative does <strong>not</strong> give them any control over your information; to allow this, they need to link you as a relative.') ?></p>
<p><?= __('After linking them, they still need to accept you as a relative before you can manage their account for them.') ?></p>
<?= $this->element('People/search_form', ['affiliates' => collection($this->UserCache->read('Affiliates'))->combine('id', function ($entity) { return $entity->translateField('name'); })->toArray()]) ?>

	<div id="SearchResults" class="zuluru_pagination">

<?= $this->element('People/search_results', [
	'extra_url' => [
		__('Link as relative') => ['controller' => 'People', 'action' => 'link_relative', 'person' => $person->id, 'return' => false, 'url_parameter' => 'relative'],
	]
])
?>

	</div>
</div>
