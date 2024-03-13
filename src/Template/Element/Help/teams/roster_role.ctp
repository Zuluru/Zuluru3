<?php
/**
 * @var \App\View\AppView $this
 */

use Cake\Core\Configure;
?>

<p><?= __('There are times when you will want to promote players (e.g. from player to assistant captain, or from substitute to player), demote players (e.g. from assistant captain to player, or from player to substitute), or remove players entirely from your roster. To update anyone\'s role on the team, go to the "team view" page, and click the player\'s current role (e.g. "{0}"). You will be presented with a list of options for the player\'s new role on the team.',
	__('Regular player')
) ?></p>
<p><?= __('A change of role does not require any confirmation from the player{0}.',
	(Configure::read('feature.generate_roster_email') ? __(', though an email will be sent to the player informing them of the change') : '')
);
?></p>
<p><?= __('Anyone may use this feature to demote themselves, though the last coach or captain on a team cannot do so. Only coaches, captains and assistant captains may promote players, and nobody can promote someone to a role higher than their own.') ?></p>
