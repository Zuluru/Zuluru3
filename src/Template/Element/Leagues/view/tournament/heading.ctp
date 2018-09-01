<?php
use Cake\Core\Configure;
?>

<tr>
<?php
if (Configure::read('Perm.is_admin') || Configure::read('Perm.is_manager') || $is_coordinator):
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

if (Configure::read('Perm.is_logged_in')):
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
