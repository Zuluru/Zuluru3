<?php
use Cake\Core\Configure;
?>

<tr>
<?php
if ($can_edit):
?>
	<th><?= __('Initial Seed') ?></th>
<?php
endif;
?>
	<th><?= __('Team Name') ?></th>
<?php
if ($division->is_playoff):
?>
	<th><?= __('From') ?></th>
<?php
endif;

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
