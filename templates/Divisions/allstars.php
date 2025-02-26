<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Divisions'));
$this->Breadcrumbs->add($division->full_league_name);
$this->Breadcrumbs->add(__('Allstar Nominations Report'));
?>

<div class="divisions allstars">
<h2><?= __('Allstar Nominations Report') . ': ' . $division->full_league_name ?></h2>

<div class="table-responsive">
<table class="table table-striped table-hover table-condensed">
<?php
$rows = [];
$gender = null;
$column = Configure::read('gender.column');
foreach ($allstars as $allstar) {
	// TODO: Eliminate gender breakdown for non-admin users, if we open up permissions
	if ($allstar->person->$column != $gender) {
		$gender = $allstar->person->$column;
		$rows[] = [[$this->Html->tag('h3', __x('gender', $gender)), ['colspan' => 3]]];
	}
	$rows[] = [
		$this->element('People/block', ['person' => $allstar->person]),
		$this->Html->link($allstar->person->email, "mailto:{$allstar->person->email}"),
		$allstar->count,
	];
}
echo $this->Html->tableCells($rows);
?>
</table>
</div>

</div>

<?php
if ($min > 1) {
	echo $this->Html->para(null, __("This list shows only those with at least {0} nominations. The {1} is also available.",
		$min,
		$this->Html->link(__('complete list'), ['action' => 'allstars', '?' => ['division' => $division->id, 'min' => 1]])
	));
}
