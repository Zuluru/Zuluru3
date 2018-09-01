<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Teams'));
$this->Html->addCrumb($team->name);
$this->Html->addCrumb(__('Note'));
if ($note->isNew()) {
	$this->Html->addCrumb(__('Add'));
} else {
	$this->Html->addCrumb(__('Edit'));
}
?>

<div class="teams form">
<h2><?= __('Team Note') . ': ' . $team->name ?></h2>
<?php
echo $this->Form->create($note, ['align' => 'horizontal']);
$options = [
	VISIBILITY_PRIVATE => __('Only I will be able to see this'),
];
if (Configure::read('Perm.is_admin')) {
	$options[VISIBILITY_CAPTAINS] = __('Only the coaches/captains of the team');
	$options[VISIBILITY_TEAM] = __('Everyone on the team');
	$options[VISIBILITY_COORDINATOR] = __('Admins and coordinators of this division');
	$options[VISIBILITY_ADMIN] = __('Administrators only');
} else if (in_array($team->division_id, $this->UserCache->read('DivisionIDs'))) {
	$options[VISIBILITY_CAPTAINS] = __('Only the coaches/captains of the team');
	$options[VISIBILITY_TEAM] = __('Everyone on the team');
	$options[VISIBILITY_COORDINATOR] = __('Admins and coordinators of this division');
} else if (in_array($team->id, $this->UserCache->read('TeamIDs'))) {
	$options[VISIBILITY_CAPTAINS] = __('Only the coaches/captains of the team');
	$options[VISIBILITY_TEAM] = __('Everyone on the team');
}
echo $this->Form->input('visibility', [
	'options' => $options,
	'hide_single' => true,
]);
echo $this->Form->input('note', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
