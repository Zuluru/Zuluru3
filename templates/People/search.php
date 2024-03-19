<?php
/**
 * @var \App\View\AppView $this
 */

$this->Breadcrumbs->add(__('People'));
$this->Breadcrumbs->add(__('Search'));
?>

<div class="people search">
	<h2><?= __('Search People') ?></h2>

<?= $this->element('People/search_form') ?>

<?php
if ($this->Authorize->can('rule_search', \App\Controller\PeopleController::class)):
?>
	<p><?= __('Alternately, you may {0}, {1} or {2}.',
		$this->Html->link(__('enter a rule and find people who match'), ['action' => 'rule_search']),
		$this->Html->link(__('find everyone participating in a particular league'), ['action' => 'league_search']),
		$this->Html->link(__('find all inactive users (not currently on any team)'), ['action' => 'inactive_search'])
	) ?></p>
<?php
endif;
?>

	<div id="SearchResults" class="zuluru_pagination">

<?php
if ($this->Authorize->getIdentity()->isManager()) {
	echo $this->element('People/search_results', [
		'extra_url' => [
			__('Change Password') => ['controller' => 'Users', 'action' => 'change_password', 'url_parameter' => 'user', 'url_field' => 'user_id'],
			__('Act As') => ['controller' => 'People', 'action' => 'act_as'],
			__('Add Credit') => ['controller' => 'Credits', 'action' => 'add'],
		],
	]);
} else {
	echo $this->element('People/search_results');
}
?>

	</div>
</div>
