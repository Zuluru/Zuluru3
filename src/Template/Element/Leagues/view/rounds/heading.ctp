<?php
/**
 * @type \App\Model\Entity\Division $division
 * @type \App\Model\Entity\League $league
 * @type \App\Model\Entity\Team $team
 * @type int $seed
 * @type string[] $classes
 */

use App\Controller\TeamsController;
use Cake\Core\Configure;
?>

<tr>
	<th><?= __('Team Name') ?></th>
<?php
if ($this->Authorize->can('view_roster', TeamsController::class)):
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
