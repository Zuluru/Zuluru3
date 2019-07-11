<?php
use Cake\Core\Configure;
?>

<p><?= __('Team rosters can be either open or closed. Team rosters typically default to closed, but the desired option can be selected by a coach or captain in the {0} page.',
	(array_key_exists('team', $this->request->getQueryParams()) ? $this->Html->link(__('Edit Team'), ['controller' => 'Teams', 'action' => 'edit', 'team' => $this->request->getQuery('team')]) : '"' . __('Edit Team') . '"')
) ?></p>
<p><?= __('Players can always be invited to join a team by a coach or captain. Invitations must be accepted by the player before they are officially added to the roster.') ?>
<?php
if (Configure::read('options.roster_email')):
?>

<?= __('When a player is invited to join a team, an email is sent to the player with links and instructions on how to proceed.') ?>
<?php
endif;
?>
</p>
<p><?= __('If a team\'s roster is open, players may additionally request to join the team. Requests must be accepted by a coach or captain before the player is officially added to the roster.') ?>
<?php
if (Configure::read('options.roster_email')):
?>

<?= __('When a player requests to join a team, an email is sent to all coaches and captains with links and instructions on how to proceed.') ?>
<?php
endif;
?>
</p>
