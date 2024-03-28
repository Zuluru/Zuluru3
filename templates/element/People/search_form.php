<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="search form">
<?= $this->Form->create(null, ['align' => 'horizontal']) ?>
<p><?= __('Enter first and/or last name of person to search for and click \'submit\'. You may use \'*\' as a wildcard.') ?>

<?= $this->Html->help(['action' => 'people', 'searching']) ?>
</p>
<?php
if (isset($affiliate_id)) {
	echo $this->Form->hidden('affiliate_id', ['value' => $affiliate_id]);
} else if (isset($affiliates)) {
	echo $this->Form->control('affiliate_id', [
		'options' => $affiliates,
		'hide_single' => true,
	]);
}

echo $this->Form->control('first_name', ['size' => 70, 'maxlength' => 100]);
if ($this->Authorize->can('display_legal_names', \App\Controller\PeopleController::class)) {
	echo $this->Form->control('legal_name', ['size' => 70, 'maxlength' => 100]);
}
echo $this->Form->control('last_name', ['size' => 70, 'maxlength' => 100]);
echo $this->Form->hidden('sort', ['value' => 'last_name']);
echo $this->Form->hidden('direction', ['value' => 'asc']);

echo $this->Jquery->ajaxButton(__('Search'), ['selector' => '#SearchResults']);

echo $this->Form->end();
?>
</div>
