<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;

$this->Html->addCrumb(__('People'));
$this->Html->addCrumb(__('Inactive Search'));
?>

<div class="people search">
	<h2><?= __('Search People') ?></h2>

<?php
if (Configure::read('feature.affiliates')):
?>
	<div class="search form">
		<?= $this->Form->create(false, ['align' => 'horizontal']) ?>
<?php
	if (isset($affiliate_id)) {
		echo $this->Form->hidden('affiliate_id', ['value' => $affiliate_id]);
	} else if (isset($affiliates)) {
		echo $this->Form->input('affiliate_id', [
				'options' => $affiliates,
				'hide_single' => true,
		]);
	}
	echo $this->Form->hidden('sort', ['value' => 'last_name']);
	echo $this->Form->hidden('direction', ['value' => 'asc']);

	echo $this->Jquery->ajaxButton(__('Search'), ['selector' => '#SearchResults']);

	echo $this->Form->end();
?>
	</div>
<?php
endif;
?>

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
