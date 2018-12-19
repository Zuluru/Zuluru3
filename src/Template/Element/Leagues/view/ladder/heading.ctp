<?php
use Cake\Core\Configure;
?>

<tr>
	<th><?= __('Seed') ?></th>
	<th><?= __('Team Name') ?></th>
	<th><?= __('Rating') ?></th>
<?php
if ($this->Authorize->can('view_roster', \App\Controller\TeamsController::class)):
?>
	<th><?= __('Players') ?></th>
<?php
	if (Configure::read('profile.skill_level')):
?>
	<th><?= __('Avg. Skill') ?></th>
<?php
	endif;
endif;
?>
	<th class="actions"><?= __('Actions') ?></th>
</tr>
