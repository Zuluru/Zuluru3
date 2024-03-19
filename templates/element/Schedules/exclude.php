<?php
/**
 * Output a block listing teams that will be excluded from scheduling.
 * This is used in all the views that the SchedulesController may render.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 */

if (!empty($this->getRequest()->getData('ExcludeTeams'))):
?>
<p><?= __('You will be excluding the following teams from the schedule') ?>:</p>
<ul>
<?php
foreach ($this->getRequest()->getData('ExcludeTeams') as $team_id => $one) {
	$team = collection($division->teams)->match(['id' => $team_id])->first()->name;
	echo $this->Html->tag('li', $team);
}
?>
</ul>
<?php
endif;
