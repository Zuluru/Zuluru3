<?php
/**
 * @type $division \App\Model\Entity\Division
 * @type $league \App\Model\Entity\League
 * @type $team \App\Model\Entity\Team
 * @type $seed int
 * @type $classes string[]
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
