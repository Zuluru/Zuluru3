<?php
// Output a block listing teams that will be excluded from scheduling.
// This is used in all of the views that the SchedulesController may render.
?>
<?php
if (isset($this->getRequest()->data) && array_key_exists('ExcludeTeams', $this->getRequest()->data)):
?>
<p><?= __('You will be excluding the following teams from the schedule') ?>:</p>
<ul>
<?php
foreach ($this->getRequest()->getData('ExcludeTeams') as $team_id => $one) {
	$team = array_pop(collection($division->teams)->match(['id' => $team_id])->extract('name')->toArray());
	echo $this->Html->tag('li', $team);
}
?>
</ul>
<?php
endif;
