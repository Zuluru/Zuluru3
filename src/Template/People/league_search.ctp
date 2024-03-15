<?php
/**
 * @var \App\View\AppView $this
 */

$this->Breadcrumbs->add(__('People'));
$this->Breadcrumbs->add(__('League Search'));
?>

<div class="people search">
	<h2><?= __('Search People') ?></h2>

	<div class="search form">
		<?= $this->form->create(null, ['align' => 'horizontal']) ?>
		<p><?= __('Select a league to show players from.') ?></p>
<?php
if (isset($affiliate_id)) {
	echo $this->Form->hidden('affiliate_id', ['value' => $affiliate_id]);
} else if (isset($affiliates)) {
	echo $this->Form->control('affiliate_id', [
		'options' => $affiliates,
		'hide_single' => true,
	]);
}

echo $this->Form->control('league_id', [
	'options' => $leagues,
	'hide_single' => true,
]);
echo $this->Form->control('include_subs', ['type' => 'checkbox']);
echo $this->Form->hidden('sort', ['value' => 'last_name']);
echo $this->Form->hidden('direction', ['value' => 'asc']);

echo $this->Jquery->ajaxButton(__('Search'), ['selector' => '#SearchResults']);

echo $this->Form->end();
?>
	</div>

	<div id="SearchResults" class="zuluru_pagination">

<?= $this->element('People/search_results') ?>

<?php
if (!empty($params['rule'])):
?>
<p class="clear-float"><?= __('To create a mailing list for this search, use this rule:') ?> <code><?= $params['rule'] ?></code></p>
<?php
endif;
?>

	</div>
</div>
