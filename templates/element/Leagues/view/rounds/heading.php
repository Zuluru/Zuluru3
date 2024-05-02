<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Division $division
 * @var \App\Model\Entity\League $league
 * @var \App\Model\Entity\Team $team
 * @var int $seed
 * @var string[] $classes
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
