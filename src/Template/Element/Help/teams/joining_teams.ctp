<?php
use Cake\Core\Configure;
?>

<p><?= __('In order to play, you need to be on the roster of a team. There may be a few options for you here:') ?></p>
<ul>
<?php
if (Configure::read('feature.registration')):
?>
	<li><?= __('Check the {0} to see if there are any "{1}" events available. Registering for one of these will reserve you a spot on a "hat" team. Note that hat teams are typically not set up until the league is almost ready to start, so don\'t be surprised if you don\'t show up on a roster right away.',
		$this->Html->link(__('Registration Wizard'), ['controller' => 'Events', 'action' => 'wizard']),
		__('Individuals for Teams')
	) ?></li>
<?php
endif;
?>
	<li><?= __('Check the {0} for the {1} icon indicating "open roster" teams who are accepting requests. Note that the prevalence and etiquette of this option may vary from one organization to another.',
		$this->Html->link(__('team list'), ['controller' => 'Teams', 'action' => 'index']),
		$this->Html->iconImg('roster_add_24.png')
	) ?></li>
	<li><?= __('Create your own team. More information about creating and managing teams is in the {0}.',
		$this->Html->link(__('captains guide'), ['controller' => 'Help', 'action' => 'guide', 'captain'])
	);
	?></li>
	<li><?= __('Your organization may also have forums where you can post messages looking for a team, or read messages from captains looking to fill out their roster.') ?></li>
</ul>
