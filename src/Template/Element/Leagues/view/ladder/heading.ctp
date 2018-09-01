<?php
use Cake\Core\Configure;
?>

<tr>
	<th><?= __('Seed') ?></th>
	<th><?= __('Team Name') ?></th>
	<th><?= __('Rating') ?></th>
<?php
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
