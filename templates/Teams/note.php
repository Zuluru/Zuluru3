<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Team $team
 */

use Cake\Core\Configure;

$this->Breadcrumbs->add(__('Teams'));
$this->Breadcrumbs->add($team->name);
$this->Breadcrumbs->add(__('Note'));
if ($note->isNew()) {
	$this->Breadcrumbs->add(__('Add'));
} else {
	$this->Breadcrumbs->add(__('Edit'));
}
?>

<div class="teams form">
<h2><?= __('Team Note') . ': ' . $team->name ?></h2>
<?php
echo $this->Form->create($note, ['align' => 'horizontal']);
$identity = $this->Authorize->getIdentity();
$options = [
	VISIBILITY_PRIVATE => __('Only I will be able to see this'),
];
if ($this->Authorize->getIdentity()->wasCaptainOf($team)) {
	$options[VISIBILITY_CAPTAINS] = __('Only the coaches/captains of the team');
	$options[VISIBILITY_TEAM] = __('Everyone on the team');
}
if ($this->Authorize->getIdentity()->isCoordinatorOf($team)) {
	$options[VISIBILITY_COORDINATOR] = __('Admins and coordinators of this division');
}
if ($this->Authorize->getIdentity()->isManagerOf($team)) {
	$options[VISIBILITY_ADMIN] = __('Administrators only');
}
echo $this->Form->control('visibility', [
	'options' => $options,
	'hide_single' => true,
]);
echo $this->Form->control('note', ['cols' => 70, 'class' => 'wysiwyg_simple']);
echo $this->Form->button(__('Submit'), ['class' => 'btn-success']);
echo $this->Form->end();
?>
</div>
